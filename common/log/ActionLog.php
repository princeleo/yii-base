<?php
/**
 * Author: Richard <chenz@snsshop.cn>
 * Date: 2016/11/28
 * Time: 16:22
 */

namespace app\common\log;

use app\models\ActionLogModel;
use yii\base\Object;

class ActionLog extends Object
{
    public $system;

    public $resourceTypes;

    protected $request;

    const ADD = 'add';
    const DELETE = 'delete';
    const EDIT = 'edit';
    const SEARCH = 'query';
    const LOGIN = 'login';
    const LOGOUT = 'logout';
    const SYNC = 'sync';
    const DOWNLOAD = 'down';

    const SUCCESS = 1;
    const FAILED = 2;

    public function record($resourceType, $resourceId, $actionType, $params, $data = [], $result = self::SUCCESS, $ext = '')
    {
        if ((! $this->checkResourceType($resourceType)) ||
            empty($resourceId) ||
            (! $this->checkActionType($actionType)) ||
            empty($params)) {
            return false;
        }

        $request = \Yii::$app->request;

        $m = new ActionLogModel();

        $m->system_id = $this->system;
        $m->resource_type = $resourceType;
        $m->resource_id = $resourceId;
        $m->action_type = $actionType;
        $m->action_time = time();
        $m->action_params = json_encode($params, JSON_UNESCAPED_UNICODE);
        $m->action_data = empty($data) ? '' : json_encode($data, JSON_UNESCAPED_UNICODE);
        $m->action_result = $result;
        $m->action_aid = empty(\Yii::$app->user->identity) ? (!empty($params['uid']) ? $params['uid'] : 0) : \Yii::$app->user->identity->getId();
        $m->ip = $request->userIP;
        $m->request_host = $request->hostInfo;
        $m->user_agent = empty($request->userAgent) ? '' : $request->userAgent;

        if ('' !== $ext) {
            $m->ext_data = json_encode($ext, JSON_UNESCAPED_UNICODE);
        }

        return $m->save();
    }

    public function getResourceTypes()
    {
        return $this->resourceTypes;
    }

    public function getActionTypes()
    {
        return self::$actions;
    }

    public function init()
    {
        parent::init();
        $this->system = \Yii::$app->id;
    }

    protected function checkResourceType($resourceType)
    {
        return in_array($resourceType, array_keys($this->resourceTypes));
    }

    protected function checkActionType($actionType)
    {
        return in_array($actionType, array_keys(self::$actions));
    }

    protected static $actions = [
        'add' => '新增',
        'delete' => '删除',
        'edit' => '修改',
        'search' => '查询',
        'login' => '登录',
        'logout' => '退出',
        'sync' => '同步',
        'down' => '下载',
    ];

    /**
     * 操作结果状态
     * @var array
     */
    protected static $actionResults = [
        self::SUCCESS => '成功',
        self::FAILED => '失败',
    ];

    public function getActionResults()
    {
        return self::$actionResults;
    }
}