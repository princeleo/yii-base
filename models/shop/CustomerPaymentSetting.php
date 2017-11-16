<?php

namespace app\models\shop;

use app\common\errors\BaseError;
use app\common\exceptions\ParamsException;
use app\models\bank_channel\Rate;
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
class CustomerPaymentSetting extends \app\models\BaseModel
{

    const SHOULD_PAY_MONEY = 1; //0.01元

    const RATE_TYPE_SCAN = 2; //扫码
    const RATE_TYPE_TABLE = 1; //点餐
    /**
     * 结算渠道
     */
    const PAYMENT_BANK_CITIC = 1; //中信银行
    const PAYMENT_BANK_SPD = 2; //浦发银行
    public static $paymentBanks = [
        self::PAYMENT_BANK_CITIC => '中信',
        self::PAYMENT_BANK_SPD => '浦发',
    ];


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'customer_payment_settings';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id','payment_type','rate_type','min_money_per_order','max_money_per_order','max_money_per_day','customer_id','shop_id','shop_sub_id','store_id','rate','payment_rate_id'], 'integer'],
            [['account','sign_key','password'], 'string'],
            [['speedpos_id'], 'safe'],
        ];
    }


    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBankRates()
    {
        return $this->hasOne(Rate::className(), ['id' => 'payment_rate_id']);
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
        if (($model = CustomerPaymentSetting::findOne($id)) !== null) {
            return $model;
        } else {
            throw new ParamsException(BaseError::PARAMETER_ERR,['id'=>$id]);
        }
    }
}
