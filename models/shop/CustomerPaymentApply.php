<?php

namespace app\models\shop;

use app\common\errors\BaseError;
use app\common\exceptions\ParamsException;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "consume_order".
 *
 * @property string $id
 * @property integer $consume_date
 * @property string $app_id
 * @property integer $agent_id
 * @property integer $shop_id
 * @property integer $exposure
 * @property integer $click
 * @property integer $ctr
 * @property integer $cpm
 * @property integer $cost
 * @property string $src
 * @property integer $created
 * @property integer $modified
 * @property integer $cpc
 */
class CustomerPaymentApply extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'customer_payment_apply';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id','customer_id','dist_id','city_id','province_id','indu_id','account_type','partner_type'], 'integer'],
            [['name','short_name','business_licence_pic','business_licence_no','headman_pic','headman_mobile',
                'headman_idnum','headman','legalperson','homepage','mobile','email','address','bank_cardno','bank','bank_branch','bank_code','bank_branch_code',
                'open_account_owner','open_account_mobile','open_account_num','bank_card_pic','open_account_pic'], 'string']
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPaymentSetting()
    {
        return $this->hasMany(CustomerPaymentSetting::className(), ['customer_id' => 'customer_id']);
    }

    /**
     * @param $params
     * @param array $with
     * @return ActiveDataProvider
     */
    public function search($params, $with=[])
    {
        $query = ConsumeOrder::find();
        if ($with) {
            $query->with($with);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load(['ConsumeOrder'=>$params]) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            ConsumeOrder::tableName().'.id' => $this->id,
            ConsumeOrder::tableName().'.agent_id' => $this->agent_id,
            ConsumeOrder::tableName().'.app_id' => $this->app_id,
            ConsumeOrder::tableName().'.src' => $this->src,
            ConsumeOrder::tableName().'.shop_id' => $this->shop_id,
            ConsumeOrder::tableName().'.consume_date' => $this->consume_date,
        ]);
        if (!empty($params['consume_date_s'])) {
            $query->andFilterWhere(['>=', ConsumeOrder::tableName().'.consume_date', $params['consume_date_s']]);
        }
        if (!empty($params['consume_date_e'])) {
            $query->andFilterWhere(['<', ConsumeOrder::tableName().'.consume_date', $params['consume_date_e']]);
        }
        if(!empty($params['shop_name'])) {
            $query->leftJoin(ShopBase::tableName(),'`'.ConsumeOrder::tableName().'`.`shop_id` = `'.ShopBase::tableName().'`.`shop_id`')
                ->andFilterWhere(['like', ShopBase::tableName().'.name', $params['shop_name']]);
        }

        if (!empty($params['sort'])) {
            $query->orderBy($params['sort']);
        }

        return $dataProvider;
    }

    public function findModel($id){
        if (($model = CustomerPaymentApply::findOne($id)) !== null) {
            return $model;
        } else {
            throw new ParamsException(BaseError::PARAMETER_ERR,['id'=>$id]);
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopBase()
    {
        return $this->hasOne(ShopBase::className(), ['shop_id' => 'shop_id']);
    }

    public function getSummary($params)
    {
        $query = ConsumeOrder::find();
        if (!empty($params['selectStr'])) {
            $query->select($params['selectStr']);
        } else {
            $query->select('sum(cost) as iOrderAmount');
        }

        $query->andFilterWhere([
            ConsumeOrder::tableName().'.id' => isset($params['id']) ? $params['id'] : null,
            ConsumeOrder::tableName().'.agent_id' => isset($params['agent_id']) ? $params['agent_id'] : null,
            ConsumeOrder::tableName().'.shop_id' => isset($params['shop_id']) ? $params['shop_id'] : null,
        ]);
        if (!empty($params['consume_date_s'])) {
            $query->andFilterWhere(['>=', 'consume_date', $params['consume_date_s']]);
        }
        if (!empty($params['creation_time_e'])) {
            $query->andFilterWhere(['<', 'consume_date', $params['consume_date_e']]);
        }

        return $query->asArray()->one();
    }
}
