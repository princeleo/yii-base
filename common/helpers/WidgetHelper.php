<?php

namespace app\common\helpers;

use app\common\widgets\Html;
use app\models\agent\AgentBase;
use app\models\base\BaseDomain;
use app\models\shop\ShopBase;
use app\models\user\UserGroup;

class WidgetHelper
{
    /**
     * 服务商带搜索的下拉选择框级联表单
     * @param $conf
     * @return string
     */
    public static function agentSearchSelectForm($conf = [])
    {
        $default = [
            'view'=>'/widgets/agent-search-select-form',
        ];
        $conf = array_merge($default, $conf);
        return Html::widget(['html' => $conf]);
    }

    /**
     * 服务商带搜索的下拉选择框
     * @param $conf
     * @return string
     */
    public static function agentSearchSelect($conf = [])
    {
        $default = [
            'id' => 'agent_id',
            'name'=>'agent_id',
            'show_required'=>0,
            'label'=>'所属服务商',
            'load_default'=> 1,
            'view'=>'/widgets/agent-search-select',
            'agent_info' => []
        ];
        $conf = array_merge($default, $conf);
        $params = \Yii::$app->request->queryParams;
        $agent_id = isset($params[$conf['name']]) ? intval($params[$conf['name']]) : '';
        if ($agent_id && empty($conf['agent_info'])) {
            $conf['agent_info'] = (new AgentBase())->findAgentDetail($agent_id);
        }
        $conf['agent_id'] = $agent_id;
        return Html::widget(['html' => $conf]);
    }

    /**
     * 服务商带搜索的下拉选择框-上级代理商专属
     * @param $conf
     * @return string
     */
    public static function agentSearchSelectTable($conf = [])
    {

        $default = [
            'id' => 'up_agent_level',
            'name'=>'up_agent_level',
            'show_required'=>0,
            'label'=>'所属服务商',
            'load_default'=> 1,
            'view'=>'/widgets/agent-search-select-table',
            'agent_info' => []
        ];
        $conf = array_merge($default, $conf);
        $agent_id = isset($conf['values']) ? intval($conf['values']) : 0;
        if ($agent_id && empty($conf['agent_info'])) {
            $conf['agent_info'] = (new AgentBase())->findAgentDetail($agent_id);
        }
        else
        {
            $conf['agent_info'] = array('agent_name'=>'高朋');
        }
        $conf['agent_id'] = $agent_id;
        return Html::widget(['html' => $conf]);
    }


    /**
     * 区域-服务商-商户搜索框
     * @param $conf
     * @return string
     */
    public static function domainAgentShopSearchSelectForm($conf = [])
    {
        $default = [
            'view'=>'/widgets/domain-agent-shop-search-select-form',
        ];
        $conf = array_merge($default, $conf);
        $params = \Yii::$app->request->get();
        if (! empty($params['domain_id'])) {
            $params['domain_name'] = BaseDomain::find()->select('name')->where(['id' => $params['domain_id']])->scalar();
        }
        if (! empty($params['agent_id'])) {
            $params['agent_name'] = AgentBase::find()->select('agent_name')->where(['agent_id' => $params['agent_id']])->scalar();
        }
        if (! empty($params['shop_id'])) {
            $params['shop_name'] = ShopBase::find()->select('name')->where(['shop_id' => $params['shop_id']])->scalar();
        }
        $conf['params'] = $params;
        return Html::widget(['html' => $conf]);
    }


    /**
     * 左边菜单
     * @param array $conf
     * @return string
     */
    public static function menus($conf = [])
    {
        $conf = array_merge([
            'view' => '/widgets/menus',
            'menus' => static::permMenus(),
            'current' => \Yii::$app->controller->id . '/' . \Yii::$app->controller->action->id,
        ],$conf);
        return Html::widget(['html' => $conf]);
    }



    private static  function permMenus($menus = [])
    {
        $permMenus = [];

        if (empty($menus)) {
            $menus = \Yii::$app->controller->module->params['menus'];
        }

        foreach ($menus as $menu) {
            if (! empty($menu['sub'])) {
                $menu['sub'] = static::permMenus($menu['sub']);
                if (! empty($menu['sub'])) {
                    $permMenus[] = $menu;
                }
            } else if (static::checkPerm($menu['route'],isset($menu['auth']) ? $menu['auth'] : true)) {
                $permMenus[] = $menu;
            }
        }

        return $permMenus;
    }

    private static  function checkPerm($route,$auth = true)
    {
        list($controller, $action) = explode('/', $route);
        $id = \Yii::$app->id;
        $module = \Yii::$app->controller->module->id;
        $userGroup = UserGroup::findUserGroup(\Yii::$app->user->identity->getId());
        if (1 === $userGroup->is_root || $auth == false) { // 超级管理员
            return true;
        }
        $userGroup = $userGroup->_permissionsTree;
        return isset($userGroup[$id][$module][$controller][$action]);
    }
}

