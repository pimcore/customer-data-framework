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
                $title = 'active';
            }
            else
            {
                $icon = 'plugin_cmf_icon_rule_disabled';
                $title = 'inactive';
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
            // create json config
            $json = array(
                'id' => $rule->getId(),
                'name' => $rule->getName(),
                'description' => $rule->getDescription(),
                'active' => $rule->getActive(),
                'trigger' => [],
                'condition' => [],
                'actions' => []
            );


            foreach($rule->getTrigger() as $trigger)
            {
                $json['trigger'][] = $trigger->toArray();
            }


            foreach($rule->getAction() as $action)
            {

                if(class_exists($action->getImplementationClass())) {
                    $actionData = call_user_func([$action->getImplementationClass(), 'getDataForEditmode'], $action);
                } else {
                    throw new \Exception(sprintf("class '%s' does not exist", $action->getImplementationClass()));
                }

                $json['actions'][] = $actionData;
            }

            foreach($rule->getCondition() as $condition)
            {
                $json['condition'][] = $condition->toArray();
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





            // create a tree from the flat structure
            $arrCondition = [];
            foreach($data->conditions as $setting)
            {
                $setting = json_decode(json_encode($setting), true);
                $condition = new \CustomerManagementFramework\ActionTrigger\ConditionDefinition($setting);
                $arrCondition[] = $condition;
            }


            $rule->setCondition( $arrCondition );


            // save action
            $arrActions = array();
            foreach($data->actions as $setting)
            {

                if(class_exists($setting->implementationClass)) {
                    $action = call_user_func([$setting->implementationClass, 'createActionDefinitionFromEditmode'], $setting);
                } else {
                    throw new \Exception(sprintf("class '%s' does not exist", $setting->implementationClass));
                }

                $arrActions[] = $action;
            }

            $rule->setAction($arrActions);


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

    /**
     * add new rule
     */
    public function addAction()
    {
        // send json response
        $return = array(
            'success' => false,
            'message' => ''
        );

        // save rule
        try
        {
            $rule = new \CustomerManagementFramework\ActionTrigger\Rule();
            $rule->setName( $this->getParam('name') );
            if($rule->save()) {

                $return['success'] = true;
                $return['id'] = $rule->getId();
            }

        }
        catch(\Exception $e)
        {
            $return['message'] = $e->getMessage();
            $return['success'] = false;
        }

        // send response
        $this->_helper->json($return);
    }

    /**
     * delete exiting rule
     */
    public function deleteAction()
    {
        // send json response
        $return = array(
            'success' => false,
            'message' => ''
        );

        // delete rule
        try
        {
            $rule = \CustomerManagementFramework\ActionTrigger\Rule::getById( (int)$this->getParam('id') );
            $rule->delete();
            $return['success'] = true;
        }
        catch(Exception $e)
        {
            $return['message'] = $e->getMessage();
        }

        // send response
        $this->_helper->json($return);
    }
}