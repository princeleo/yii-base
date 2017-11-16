<?php

namespace app\common\cache;

use app\common\helpers\BaseHelper;
use app\common\ResultModel;
use app\models\agent\AgentBase;
use app\models\app\BaseApp;
use app\models\department\Department;
use app\models\message\BaseMessageInbox;
use app\models\role\Role;
use app\models\role\RoleNode;
use app\models\user\AuthUser;
use app\models\user\User;
use app\models\user\UserForm;
use app\modules\api\controllers\UserController;
use Yii;

class UserCache
{
    const CACHE_USER_KEY = '_Boss_User_'; //此将影响到单点登录，不能随便改动
    public $redis;

    public function __construct(){
        $this->redis = self::getRedis();
    }

    public static function getRedis()
    {
        return Yii::$app->redis;
    }


    /**
     * @param $uid
     * @return bool
     * @throws \app\common\exceptions\AdminException
     */
    public static function setUserCache($uid)
    {
        $resultModel = new ResultModel();
        $user = $resultModel->resultData(AuthUser::findOne($uid));

        if(empty($user)){
            return false;
        }

        return self::setUserDataCache($user);
    }

    public static function getUserCache($uid)
    {
        if(is_null($uid)){
            return false;
        }
        $return = self::getRedis()->get(self::getCacheKey($uid));
        return json_decode($return,true) == false ? $return : json_decode($return,true);
    }


    public static function setAgentCache($agent_id)
    {
        $agent = (new AgentBase())->findAgentDetail($agent_id);
        if(empty($agent) || empty($agent['userDetail'])){
            return false;
        }

        return self::setUserDataCache($agent['userDetail']);
    }

    /**
     * @param $uid
     * @return bool
     */
    public static function delUserCache($uid)
    {
        if(empty($uid)) return false;
        return self::getRedis()->set(self::getCacheKey($uid),null);
    }


    /**
     * 统一返回用户缓存key
     * @param $uid
     * @return string
     */
    public static function getCacheKey($uid)
    {
        if(empty($_COOKIE['PHPSESSID'])){
            $php_sessid = 'PHPSESSID';
        }else{
            $php_sessid = $_COOKIE['PHPSESSID'];
        }
        //return $php_sessid.self::CACHE_USER_KEY.$uid;
        return self::CACHE_USER_KEY.$uid;
    }





    protected static function setUserDataCache($user)
    {
        //获取角色及权限
        $_roles =  $_permission = $_department = array();
        if(!empty($user['roles'])){
            $roles = explode(',',$user['roles']);
            foreach($roles as $role){
                $role_row = BaseHelper::recordToArray((new Role())->findOne($role));

                if(!empty($role_row) && $role_row['status'] == Role::STATUS_DEFAULT){
                    if(!empty($role_row['did'])){
                        $_department[] = (new Department())->getDepartmentTree($role_row['did']);
                    }
                    $_roles[] = $role_row;
                    $_permission[] = BaseHelper::recordToArray((new RoleNode())->find()->where(['rid'=>$role])->all());
                }
            }
        }

        $agent = !empty($user['user_type']) && $user['user_type'] == User::USER_TYPE_AGENT ? AgentBase::find()->where(['agent_id' => $user['extend_id']])->one() : [];
        $user = array_merge(['_roles'=>$_roles,'_permissions'=>$_permission,'_departments'=>$_department],[
            'uid' => $user['uid'],
            //'appid' => $user['appid'],
            //'openid' => $user['openid'],
            'is_root' => $user['is_root'],
            'pid' => $user['pid'],
            'auth_key' => $user['auth_key'],
            'nickname' => $user['nickname'],
            'username' => $user['username'],
            'realname' => $user['realname'],
            'email' => $user['email'],
            'mobile' => $user['mobile'],
            'logo' => $user['logo'],
            'valid_time' => $user['valid_time'],
            'internal' => $user['internal'],
            'status' => $user['status'],
            'modified' => $user['modified'],
            'created' => $user['created'],
            'extend_id' => $user['extend_id'],
            'unread_msg' => !empty($user['user_type']) && $user['user_type'] == User::USER_TYPE_AGENT ? BaseMessageInbox::findUnreadMessage($user['extend_id']) : 0,
            'is_channels' => isset($user['is_channels']) && $user['is_channels'] == 1 ? true : false,
            'domain' => isset($user['domain']) && $user['domain'] ? explode(',', $user['domain']) : [],
        ]);
        if(!empty($agent['agent_scope'])){
            $user['agent_scope'] = $agent['agent_scope'];
        }

        //缓存用户信息
        if(!self::getRedis()->set(self::getCacheKey($user['uid']),json_encode($user))){
            return false;
        }
        return true;
    }
}
