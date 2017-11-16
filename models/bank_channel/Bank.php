<?php
namespace app\models\bank_channel;

use app\common\helpers\BaseHelper;
use app\models\BaseModel;
use yii\behaviors\TimestampBehavior;

/**
 * Class Bank
 *
 * @package \\${NAMESPACE}
 */
class Bank extends BaseModel
{
    public static function tableName()
    {
        return '{{%bank_channels}}';
    }

    public function rules()
    {
        return [
            ['bank_name', 'required'],
            ['bank_name', 'string', 'max' => 50],
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created',
                'updatedAtAttribute' => 'modified',
            ],
        ];
    }

    public function getRates()
    {
        return $this->hasMany(Rate::className(), ['bank_id' => 'id']);
    }


    /**
     * 统一返回平台
     * @return array|\yii\db\ActiveRecord[]
     */
    public function findList()
    {
        $bankChannels = Bank::find()->select(['id', 'bank_name'])->with(['rates' => function($query) {
            $query->select(['id', 'name', 'rate', 'bank_id'])->where(['status' => 1])->indexBy('id');
        }])->indexBy('id')->all();
        return BaseHelper::recordToArray($bankChannels);

    }


    /**
     * 名称唯一
     * @param $params
     * @return bool
     */
    public function findNameUnique($params){

        $name = isset($params['bank_name'])?$params['bank_name']:"";

        if(!empty($name)){
            $query = Bank::find()->where("bank_name=:name", [':name' => $name]);
        }

        if(isset($params['id'])){
            $query->andFilterWhere(['<>',Bank::tableName().'.id', $params['id']]);
        }

        $data = $query->asArray()->one();
        if(empty($data)){
            return false;
        }
        return true;
    }
}
