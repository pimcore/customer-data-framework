<?php

/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
 */

namespace CustomerManagementFrameworkBundle\Controller\Admin;

use CustomerManagementFrameworkBundle\Model\ActionTrigger\Rule;
use CustomerManagementFrameworkBundle\Model\ActionTrigger\Rule\Listing;
use CustomerManagementFrameworkBundle\Model\ActionTrigger\TriggerDefinition;
use Pimcore\Controller\KernelControllerEventInterface;
use Pimcore\Controller\Traits\JsonHelperTrait;
use Pimcore\Controller\UserAwareController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/rules")
 */
class RulesController extends UserAwareController implements KernelControllerEventInterface
{
    use JsonHelperTrait;

    public function onKernelControllerEvent(ControllerEvent $event): void
    {
        $this->checkPermission('plugin_cmf_perm_customer_automation_rules');
    }

    /**
     * get saved action trigger rules
     *
     * @param Request $request
     * @Route("/list")
     */
    public function listAction(Request $request): JsonResponse
    {
        $rules = new Listing();
        $rules->setOrderKey('name');
        $rules->setOrder('ASC');

        $json = [];
        foreach ($rules->load() as $rule) {
            if ($rule->getActive()) {
                $icon = 'plugin_cmf_icon_rule_enabled';
                $title = 'active';
            } else {
                $icon = 'plugin_cmf_icon_rule_disabled';
                $title = 'inactive';
            }

            $json[] = [
                'iconCls' => $icon,
                'id' => $rule->getId(),
                'text' => htmlspecialchars($rule->getName()),
                'leaf' => true,
                'qtipCfg' => [
                    'title' => htmlspecialchars($title),
                    'text' => htmlspecialchars($rule->getDescription()),
                ],
            ];
        }

        return $this->jsonResponse($json);
    }

    /**
     * get rule config as json
     *
     * @Route("/get")
     *
     * @throws \Exception
     */
    public function getAction(Request $request): JsonResponse
    {
        $rule = Rule::getById((int)$request->get('id'));
        if ($rule) {
            // create json config
            $json = [
                'id' => $rule->getId(),
                'name' => $rule->getName(),
                'description' => $rule->getDescription(),
                'active' => $rule->getActive(),
                'trigger' => [],
                'condition' => [],
                'actions' => [],
            ];

            foreach ($rule->getTrigger() as $trigger) {
                $json['trigger'][] = $trigger->toArray();
            }

            foreach ($rule->getAction() as $action) {
                if (class_exists($action->getImplementationClass())) {
                    $actionData = call_user_func([$action->getImplementationClass(), 'getDataForEditmode'], $action);
                } else {
                    throw new \Exception(sprintf("class '%s' does not exist", $action->getImplementationClass()));
                }

                $json['actions'][] = $actionData;
            }

            foreach ($rule->getCondition() as $condition) {
                if (class_exists($condition->getImplementationClass())) {
                    $conditionData = call_user_func(
                        [$condition->getImplementationClass(), 'getDataForEditmode'],
                        $condition
                    );
                } else {
                    throw new \Exception(sprintf("class '%s' does not exist", $condition->getImplementationClass()));
                }

                $json['condition'][] = $conditionData;
            }

            return $this->jsonResponse($json);
        }

        return $this->jsonResponse(['error' => true, 'msg' => 'rule not found']);
    }

    /**
     * save rule config
     *
     * @Route("/save", methods={"PUT"})
     */
    public function saveAction(Request $request): JsonResponse
    {
        // send json response
        $return = [
            'success' => false,
            'message' => '',
        ];

        // save rule config
        try {
            $rule = Rule::getById((int)$request->get('id'));
            $data = json_decode($request->get('data'));

            // apply basic settings
            $rule->setName($data->settings->name);
            $rule->setDescription($data->settings->description);
            $rule->setActive((bool)$data->settings->active);

            // save trigger
            $arrTrigger = [];
            foreach ($data->trigger as $setting) {
                $setting = json_decode(json_encode($setting), true);
                $trigger = new TriggerDefinition($setting);
                $arrTrigger[] = $trigger;
            }
            $rule->setTrigger($arrTrigger);

            // create a tree from the flat structure
            $arrCondition = [];
            foreach ($data->conditions as $setting) {
                if (class_exists($setting->implementationClass)) {
                    $condition = call_user_func(
                        [$setting->implementationClass, 'createConditionDefinitionFromEditmode'],
                        $setting
                    );
                } else {
                    throw new \Exception(sprintf("class '%s' does not exist", $setting->implementationClass));
                }
                $arrCondition[] = $condition;
            }

            $rule->setCondition($arrCondition);

            // save action
            $arrActions = [];
            foreach ($data->actions as $setting) {
                if (class_exists($setting->implementationClass)) {
                    $action = call_user_func(
                        [$setting->implementationClass, 'createActionDefinitionFromEditmode'],
                        $setting
                    );
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
        } catch (\Exception $e) {
            $return['message'] = $e->getMessage();
        }

        // send response
        return $this->jsonResponse($return);
    }

    /**
     * add new rule
     *
     * @Route("/add", methods={"POST"})
     */
    public function addAction(Request $request): JsonResponse
    {
        // send json response
        $return = [
            'success' => false,
            'message' => '',
        ];

        // save rule
        try {
            $rule = new Rule();
            $rule->setName($request->get('name'));
            if ($rule->save()) {
                $return['success'] = true;
                $return['id'] = $rule->getId();
            }
        } catch (\Exception $e) {
            $return['message'] = $e->getMessage();
            $return['success'] = false;
        }

        // send response
        return $this->jsonResponse($return);
    }

    /**
     * delete exiting rule
     *
     * @Route("/delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request): JsonResponse
    {
        // send json response
        $return = [
            'success' => false,
            'message' => '',
        ];

        // delete rule
        try {
            $rule = Rule::getById((int)$request->get('id'));
            $rule->delete();
            $return['success'] = true;
        } catch (\Exception $e) {
            $return['message'] = $e->getMessage();
        }

        // send response
        return $this->jsonResponse($return);
    }
}
