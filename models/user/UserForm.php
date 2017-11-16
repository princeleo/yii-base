<?php
namespace app\models\user;
use Yii;
use yii\base\Model;
use app\models\BaseModel;
use app\common\helpers\BaseHelper;
use app\common\errors\BaseError;
use app\common\exceptions\FormException;

class UserForm extends BaseModel{
    public  $user;
    public  $pwd;
    public  $verifyCode;
    public $goto;
    private $_user = false;

    public function rules(){

        return [
            [['user', 'pwd','verifyCode'], 'required','message'=>'{attribute}不能为空！'],
            ['user', 'string', 'max' => 50,'tooLong'=>'{attribute}长度必需在100以内'],
            ['pwd', 'string', 'max' => 32,'tooLong'=>'{attribute}长度必需在32以内'],
            ['pwd','validatePassword','message'=>'账号或密码不正确！'],
            ['verifyCode', 'captcha','message' => '验证码不正确','captchaAction'=>'/admin/login/captcha'],
        ];
    }

    /**
     * @inheritdoc 建立模型表
     */
    public static function tableName()
    {
        return '{{%auth_user}}';
    }

    /**
     * @
     */
    public function attributeLabels()
    {
        return [
            'user' => '账号',
            'pwd' => '密码',
            'verifyCode'=>'验证',
        ];
    }

    /**
     * @
     */
    public function validatePassword($attribute,$params){

        if(!$this->hasErrors()){
            $user = $this->getUser();
            if(!$user){
                $this->addError($attribute, '账号或密码不正确');
            }
        }

    }

    /**
     * @根据用户名密码查询用户
     */
    public function getUser(){
        if($this->_user===false){
            $this->_user=User::find()->where(['username'=>$this->user,'password'=>self::getPassWord($this->user,$this->pwd),'user_type'=>\app\models\user\User::USER_TYPE_BOSS])->one();
        }

        if(empty($this->_user)){
            throw new FormException(BaseError::USER_AUTH_ERR,[$this->user]);
        }
        if($this->_user->status != AuthUser::STATUS_DEFAULT || ($this->_user->valid_time != 0 && $this->_user->is_root != 1 && $this->_user->valid_time < time())){
            throw new FormException(BaseError::USER_STATUS_FAIL);
        }

        return $this->_user;
    }

    public static function getPassWord($username,$pwd)
    {
        return md5(md5($pwd).$username);
    }


    /**
     * @用户登录
     */
    public function login(){
        return Yii::$app->user->login($this->getUser(),3600*24*1);
        /*if($this->validate()){
            exit;

        }else{
            pr($this->getErrors());exit;
            return false;
        }*/
    }


}
?>