<?php

namespace app\models\agent;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "agent_promotion_account".
 *
 * @property integer $id
 * @property string $mobile
 * @property string $true_name
 * @property string $pass_word
 * @property integer $agent_id
 * @property integer $status
 * @property integer $login_count
 * @property integer $login_time
 * @property string $login_ip
 * @property integer $created
 * @property integer $modified
 */
class AgentPromotionAccount extends \app\models\BaseModel
{
    const STATUS_DEFAULT = 1;
    const STATUS_DISABLE = -1;

    public static function getStatus()
    {
        return [
            self::STATUS_DEFAULT => '正常',
            self::STATUS_DISABLE => '禁用',
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'agent_promotion_account';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
           [['mobile', 'agent_id'], 'required','on' => 'create'],
            [['agent_id', 'status', 'login_count', 'login_time', 'created', 'modified','id'], 'integer'],
            [['mobile', 'true_name', 'pass_word', 'login_ip'], 'string', 'max' => 50],
            [['mobile'], 'match', 'pattern'=>'/^1[345678]{1}\d{9}$/'],
            [['mobile'], 'unique']
        ];
    }

    /**
     * @return array
     */
    public function fields()
    {
        $fields = parent::fields();
        unset($fields['pass_word']);
        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'mobile' => 'Mobile',
            'true_name' => 'User Name',
            'pass_word' => 'Pass Word',
            'agent_id' => 'Agent ID',
            'status' => 'Status',
            'login_count' => 'Login Count',
            'login_time' => 'Login Time',
            'login_ip' => 'Login Ip',
            'created' => 'Created',
            'modified' => 'Modified',
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
            if(array_key_exists('pass_word',$attr) && $this->isNewRecord){
                $this->pass_word = \app\models\user\UserForm::getPassWord($this->mobile,$this->pass_word);
            }elseif(!empty($this->pass_word) && strlen($this->pass_word) <= 20){
                $this->pass_word = \app\models\user\UserForm::getPassWord($this->mobile,$this->pass_word);
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
        $query = AgentPromotionAccount::find();
        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'modified' => SORT_DESC,
                    'created' => SORT_DESC
                ]
            ],
        ]);
        if (!($this->load([self::formName()=>$params]))) {
            return $dataProvider;
        }
        $query->andFilterWhere([
            'id' => $this->id,
            'agent_id' => $this->agent_id,
            'status' => $this->status,
            'login_count' => $this->login_count,
            'login_time' => $this->login_time,
            'created' => $this->created,
            'modified' => $this->modified,
        ]);
        $query->andFilterWhere(['like', 'mobile', $this->mobile])
            ->andFilterWhere(['like', 'true_name', $this->true_name])
            ->andFilterWhere(['like', 'pass_word', $this->pass_word])
            ->andFilterWhere(['like', 'login_ip', $this->login_ip]);
        return $dataProvider;
    }

    public function getAgentBase()
    {
        return $this->hasOne(AgentBase::className(), ['agent_id' => 'agent_id']);
    }

    public function getLists($agent_id){
        return AgentPromotionAccount::find()->where(['status' =>AgentPromotionAccount::STATUS_DEFAULT])->andFilterWhere(['agent_id'=>$agent_id])->orderBy('modified desc,created desc')->asArray()->all();
    }
}
