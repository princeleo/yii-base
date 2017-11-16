<?php

namespace app\models\user;
use Yii;
use yii\db\ActiveRecord;
use \yii\web\IdentityInterface;

class User extends ActiveRecord implements IdentityInterface
{
    public $id;
    public $password;
    public $accessToken;

    const USER_TYPE_BOSS = 1;//boss后台用户
    const USER_TYPE_AGENT = 2;//服务商用户

    /**
     * @inheritdoc 建立模型表
     */
    public static function tableName()
    {
        return '{{%auth_user}}';
    }




    /**
     * @inheritdoc
     */

    public static function findIdentity($id)
    {
        //自动登陆时会调用
        $temp = parent::find()->where(['uid'=>$id,'user_type'=>self::USER_TYPE_BOSS])->one();
        return isset($temp)?new static($temp):null;
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['accessToken' => $token]);
    }

    /**
     * Finds user by username
     *
     * @param  string      $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        foreach (self::$users as $user) {
            if (strcasecmp($user['username'], $username) === 0) {
                return new static($user);
            }
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->uid;
    }

    /**
     * @username
     */
    public function getUsername()
    {
        return $this->username;
    }

    public function getNickName()
    {
        return $this->nickname;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return empty($this->auth_key) ? '' : $this->auth_key;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }

    /**
     * Validates password
     *
     * @param  string  $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return $this->password === $password;
    }
}
