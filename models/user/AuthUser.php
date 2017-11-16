<?php

namespace app\models\user;

use Yii;

/**
 * This is the model class for table "auth_user".
 *
 * @property integer $uid
 * @property integer $pid
 * @property integer $user_type
 * @property string $username
 * @property string $password
 * @property string $auth_key
 * @property string $nickname
 * @property string $realname
 * @property string $email
 * @property string $roles
 * @property integer $internal
 * @property string $extend_id
 * @property integer $status
 * @property integer $login_count
 * @property integer $login_time
 * @property string $login_ip
 * @property integer $modified
 * @property integer $created
 */
class AuthUser extends \app\models\BaseModel
{
    const STATUS_DEFAULT = 1;
    const STATUS_DISABLE = -1;
    const STATUS_LEAVE = -2;

    public static function getStatus()
    {
        return [
            self::STATUS_DEFAULT => '正常',
            self::STATUS_DISABLE => '停用',
            self::STATUS_LEAVE => '离职'
        ];
    }


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'auth_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username'],'unique'],
            [['pid', 'is_root', 'user_type', 'internal', 'status', 'login_count', 'login_time', 'modified', 'created','valid_time','mobile','uid','is_channels'], 'integer'],
            [['username', 'password', 'nickname', 'realname', 'modified', 'created'], 'required','on' => 'create'],
            [['username'], 'string', 'max' => 20,'min' => 4],
            [['nickname', 'realname', 'email'], 'string', 'max' => 32],
            [['password'],'string','max'=>50,'min' => 6],
            [['email'], 'email'],
            [['auth_key', 'roles', 'login_ip'], 'string', 'max' => 50],
            [['extend_id'], 'string', 'max' => 100],
            [['remark','logo'],'string','max'=>200],
            [['domain'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'uid' => 'uid',
            'is_root' => '超级管理员',
            'pid' => '子账号',
            'user_type' => '用户类型',
            'username' => '用户名',
            'password' => '密码',
            'auth_key' => '随机KEY',
            'nickname' => '昵称',
            'realname' => '真实名字',
            'email' => '邮箱',
            'roles' => '所属角色',
            'internal' => '内部员工',
            'extend_id' => '扩展字段',
            'status' => '账号状态',
            'login_count' => '登录次数',
            'login_time' => '登录时间',
            'login_ip' => '登录ip',
            'modified' => '最后更新时间',
            'created' => '创建时间',
            'is_channels' => '是否是渠道'
        ];
    }


    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)){
            $attr = $this->getAttributes();
            if(array_key_exists('password',$attr) && $this->isNewRecord){
                $this->password = \app\models\user\UserForm::getPassWord($this->username,$this->password);
                $this->auth_key = \app\common\helpers\BaseHelper::getRandChar(15);
            }elseif(!empty($this->password) && strlen($this->password) < 18){
                $this->password = \app\models\user\UserForm::getPassWord($this->username,$this->password);
            }
        }

        return true;
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = AuthUser::find();

        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'modified' => SORT_DESC,
                    'created' => SORT_DESC
                ]
            ],
        ]);

        if (!($this->load(['AuthUser'=>$params]))) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'uid' => $this->uid,
            'internal' => $this->internal,
            'status' => $this->status,
            'pid' => $this->pid,
            'modified' => $this->modified,
            'created' => $this->created,
            'user_type' => $this->user_type
        ]);

        $query->andFilterWhere(['like', 'extend_id', $this->extend_id])
            ->andFilterWhere(['like', 'username', $this->username])
            ->andFilterWhere(['like', 'nickname', $this->nickname])
            ->andFilterWhere(['like', 'password', $this->password])
            ->andFilterWhere(['like', 'realname', $this->realname])
            ->andFilterWhere(['like', 'email', $this->email])
            ->andFilterWhere(['like', 'roles', $this->roles]);

        return $dataProvider;
    }
}
