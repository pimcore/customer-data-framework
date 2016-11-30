<?php
/**
 * Created by PhpStorm.
 * User: mmoser
 * Date: 30.11.2016
 * Time: 09:56
 */

class CustomerManagementFramework_RulesController extends \Pimcore\Controller\Action\Admin
{
    /**
     * get saved action trigger rules
     */
    public function listAction()
    {
        $rules = new \CustomerManagementFramework\ActionTrigger\Rule\Listing();
        $rules->setOrderKey('name');
        $rules->setOrder('ASC');

        $json = array();
        foreach($rules->load() as $rule)
        {

            if($rule->getActive())
            {
                $icon = 'plugin_cmf_icon_rule_enabled';
                $title = 'Aktiviert';
            }
            else
            {
                $icon = 'plugin_cmf_icon_rule_disabled';
                $title = 'Deaktiviert';
            }

            $json[] = array(
                'iconCls' => $icon,
                'id' => $rule->getId(),
                'text' => $rule->getName(),
                'leaf' => true,
                'qtipCfg' => array(
                    'title' => $title,
                    'text' => $rule->getDescription()
                )
            );
        }

        $this->_helper->json($json);
    }

    /**
     * get rule config as json
     */
    public function getAction()
    {
        $rule = \CustomerManagementFramework\ActionTrigger\Rule::getById( (int)$this->getParam('id') );
        if($rule)
        {
            // get data
            $condition = $rule->getCondition();


            // create json config
            $json = array(
                'id' => $rule->getId(),
                'name' => $rule->getName(),
                'description' => $rule->getDescription(),
                'active' => $rule->getActive(),
                'trigger' => [],
                'condition' => $condition ? $condition->toArray() : '',
                'actions' => []
            );


            foreach($rule->getTrigger() as $trigger)
            {
                $json['trigger'][] = $trigger->toArray();
            }


            foreach($rule->getAction() as $action)
            {
                $json['actions'][] = $action->toArray();
            }

            $this->_helper->json( $json );
        }
    }

    /**
     * save rule config
     */
    public function saveAction()
    {
        // send json response
        $return = array(
            'success' => false,
            'message' => ''
        );

        // save rule config
        try
        {
            $rule = \CustomerManagementFramework\ActionTrigger\Rule::getById( (int)$this->getParam('id') );
            $data = json_decode($this->getParam('data'));

            // apply basic settings
            $rule->setName( $data->settings->name );
            $rule->setDescription( $data->settings->description );
            $rule->setActive( (bool)$data->settings->active );


            // save trigger
            $arrTrigger = array();
            foreach($data->trigger as $setting)
            {
                $setting = json_decode(json_encode($setting), true);
                $trigger = new \CustomerManagementFramework\ActionTrigger\TriggerDefinition($setting);
                $arrTrigger[] = $trigger;
            }
            $rule->setTrigger($arrTrigger);



            // create root condition
            $rootContainer = new stdClass();
            $rootContainer->operator = null;
            $rootContainer->type = 'Bracket';
            $rootContainer->conditions = array();


            // create a tree from the flat structure
            $currentContainer = $rootContainer;
            foreach($data->conditions as $settings)
            {
                // handle brackets
                if($settings->bracketLeft == true)
                {
                    $newContainer = new stdClass();
                    $newContainer->parent = $currentContainer;
                    $newContainer->type = 'Bracket';
                    $newContainer->conditions = array();
                    $currentContainer->conditions[] = $newContainer;

                    $currentContainer = $newContainer;
                }

                $currentContainer->conditions[] = $settings;

                if( $settings->bracketRight == true )
                {
                    $old = $currentContainer;
                    $currentContainer = $currentContainer->parent;
                    unset($old->parent);
                }
            }


            // create rule condition
            $condition = \IFTTT\Factory::getInstance()->createCondition( $rootContainer->type );
            if($condition instanceof \IFTTT\Framework\IJsonConfig)
            {
                $condition->fromJSON( json_encode($rootContainer) );
                $rule->setCondition( $condition );
            }


            // save action
            $arrActions = array();
            foreach($data->actions as $setting)
            {
                $action = \IFTTT\Factory::getInstance()->createAction( $setting->type );
                if($action instanceof \IFTTT\Framework\IJsonConfig)
                {
                    $action->fromJSON( json_encode($setting) );
                }
                $arrActions[] = $action;
            }
            //$rule->setAction($arrActions);


            // save rule
            $rule->save();

            // finish
            $return['success'] = true;
            $return['id'] = $rule->getId();
        }
        catch(Exception $e)
        {
            $return['message'] = $e->getMessage();
        }

        // send response
        $this->_helper->json($return);
    }
}