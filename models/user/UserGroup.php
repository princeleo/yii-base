<?php

namespace app\models\user;

use Yii;
use yii\helpers\Url;
use yii\web\Cookie;
use yii\redis\Connection;

class UserGroup extends \yii\base\Object
{
    public $id;
    public $is_root;
    public $pid;
    public $username;
    public $password;
    public $authKey;
    public $accessToken;
    public $appid;
    public $uid;
    public $openid;
    public $realname;
    public $nickname;
    public $email;
    public $mobile;
    public $auth_key;
    public $logo;
    public $valid_time;
    public $internal;
    public $status;
    public $modified;
    public $created;
    public $_roles;
    public $_permissions;
    public $_departments;
    public $_permissionsTree;
    public $extend_id;
    public $unread_msg;
    public $agent_scope;
    public $is_channels;
    public $domain;
    const SOS_SESSION_KEY = '_Boss_User_';


    /**
     * @inheritdoc
     */
    public static function findUserGroup($id)
    {
        $session_key = self::SOS_SESSION_KEY.$id;
        $session_data = Yii::$app->redis->get($session_key);
        if(!empty($session_data)){
            $session_data = json_decode($session_data,true);
            $session_data = self::getPermissionsTree($session_data);
        }


        return empty($session_data) ? null : new static($session_data);
    }


    /**
     * 返回权限节点树
     * @param $session
     * @return array|bool
     */
    public static function getPermissionsTree($session)
    {
        if(!empty($session['_permissions'])){
            $tree = array();
            foreach($session['_permissions'] as $permissions){
                foreach($permissions as $per){
                    $methods = empty($per['methods']) ? [] : explode(',',$per['methods']);
                    $tree[$per['app_id']][$per['pname']][$per['controller']] = isset($tree[$per['app_id']][$per['pname']][$per['controller']]) ? array_merge($tree[$per['app_id']][$per['pname']][$per['controller']],array_flip($methods)) : array_flip($methods);
                }
            }
            $session['_permissionsTree'] = $tree;
        }

        return $session;
    }
}
