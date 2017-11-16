<?php

namespace app\modules\tools\controllers;


use app\common\cache\UserCache;
use app\common\errors\BaseError;
use app\common\exceptions\ParamsException;


/**
 * 工具类，用于开发手动执行
 * Class ToolsController
 * @package app\modules\admin\controllers
 */
class ToolsController extends BaseController
{

    /**
     * 手动执行计划任务脚本
     */
    public function actionIndex()
    {
        $params = \Yii::$app->request->get();
        $url = parse_url($_SERVER['REQUEST_URI']); //解决服务配置会自带一个空健值为路径的空数组如：['/tools/tools/index' => '']
        if(empty($params['controller']) || empty($params['action'])){
            new ParamsException(BaseError::PARAMETER_ERR);
        }

        $controller = $params['controller'].'Controller';
        $action = 'action'.$params['action'];
        $script_path = \Yii::$app->basePath.'/modules/script/controllers/';
        $script_file = $script_path.$controller.'.php';
        if(!file_exists($script_file)){
            new ParamsException(BaseError::PARAMETER_ERR);
        }

        require_once($script_file);
        $controller = '\app\modules\script\controllers\\'.$controller;
        $controller =  new $controller('script','script');
        unset($params['controller'],$params['action'],$params[$url['path']]);
        $params = array_values($params);
        call_user_func_array(array($controller,$action),$params);
    }


    /**
     * 返回redis缓存值
     */
    public function actionGetRedis()
    {
        $params = \Yii::$app->request->get();
        if(!isset($params['key']) && !isset($params['uid'])){
            new ParamsException(BaseError::PARAMETER_ERR);
        }

        if($params['uid']){
            $cache = UserCache::getUserCache($params['uid']);
        }else{
            $cache = \Yii::$app->redis->get($params['key']);
            $cache = json_decode($cache,true) == false ? $cache : json_decode($cache,true);
        }

        pr($cache);
    }


    /**
     * 删除缓存
     */
    public function actionDelRedis()
    {
        $params = \Yii::$app->request->get();
        if(!isset($params['key']) && !isset($params['uid'])){
            new ParamsException(BaseError::PARAMETER_ERR);
        }

        if($params['uid']){
            $return = UserCache::delUserCache($params['uid']);
        }else{
            $return =  \Yii::$app->redis->set($params['key'],null);
        }

        if($return){
            echo '成功！';
        }else{
            echo '失败！';
        }
    }
}