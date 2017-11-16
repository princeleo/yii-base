<?php

namespace app\models\shop;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "shop_sub_payment_settings".
 *
 * @property integer $id
 * @property string $account
 * @property string $sign_key
 * @property string $crypt_key
 * @property integer $payment_type
 * @property integer $payment_bank
 * @property integer $shop_id
 * @property integer $shop_sub_id
 * @property string $rate
 * @property string $new_rate
 * @property integer $effect_time
 * @property integer $min_money_per_order
 * @property integer $max_money_per_order
 * @property integer $max_money_per_day
 * @property integer $created
 * @property integer $modified
 * @property integer $deleted
 * @property integer $merchant_id
 * @property integer $agent_id
 */
class ShopSubPaymentSettings extends \app\models\BaseModel
{
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
        return 'shop_sub_payment_settings';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['payment_type', 'payment_bank', 'agent_id', 'shop_id', 'shop_sub_id', 'effect_time', 'min_money_per_order', 'max_money_per_order', 'max_money_per_day', 'created', 'modified', 'deleted', 'merchant_id'], 'integer'],
            [['rate', 'new_rate'], 'number'],
            [['account'], 'string', 'max' => 100],
            [['shop_sub_name'], 'string', 'max' => 100],
            [['sign_key', 'crypt_key'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'account' => 'Account',
            'sign_key' => 'Sign Key',
            'crypt_key' => 'Crypt Key',
            'payment_type' => 'Payment Type',
            'payment_bank' => 'Payment Bank',
            'shop_id' => 'Shop ID',
            'shop_sub_id' => 'Shop Sub ID',
            'shop_sub_name' => 'Shop Sub Name',
            'rate' => 'Rate',
            'new_rate' => 'New Rate',
            'effect_time' => 'Effect Time',
            'min_money_per_order' => 'Min Money Per Order',
            'max_money_per_order' => 'Max Money Per Order',
            'max_money_per_day' => 'Max Money Per Day',
            'created' => 'Created',
            'modified' => 'Modified',
            'deleted' => 'Deleted',
            'merchant_id' => 'Merchant ID',
            'agent_id' => 'Agent ID',
        ];
    }

    /**
     * @param $params
     * @param $with
     * @return array|null|\yii\db\ActiveRecord
     */
    public function detail($params, $with)
    {
        $query = ShopSubPaymentSettings::find();
        if ($with) {
            $query->with($with);
        }
        if (!($this->load(['ShopSubPaymentSettings'=>$params]) && $this->validate())) {
            return [];
        }
        $query->andFilterWhere([
            'id' => $this->id,
            'agent_id' => $this->agent_id,
            'shop_id' => $this->shop_id,
        ]);

        return $query->one();
    }

    /**
     * @param $params
     * @param array $with
     * @return ActiveDataProvider
     */
    public function search($params, $with=[])
    {
        $query = ShopSubPaymentSettings::find();
        if ($with) {
            $query->with($with);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load(['ShopSubPaymentSettings'=>$params]) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            ShopSubPaymentSettings::tableName() . '.agent_id' => $this->agent_id,
            ShopSubPaymentSettings::tableName() . '.shop_id' => $this->shop_id,
            ShopSubPaymentSettings::tableName() . '.shop_sub_id' => $this->shop_sub_id,
            ShopSubPaymentSettings::tableName() . '.merchant_id' => $this->merchant_id,
            ShopSubPaymentSettings::tableName() . '.payment_bank' => $this->payment_bank,
        ]);
        if (!empty($params['modified_time_s'])) {
            $query->andFilterWhere(['>=', ShopSubPaymentSettings::tableName() . '.modified', $params['modified_time_s']]);
        }
        if (!empty($params['modified_time_e'])) {
            $query->andFilterWhere(['<', ShopSubPaymentSettings::tableName() . '.modified', $params['modified_time_e']]);
        }
        if(!empty($params['shop_name'])) {
            $query->leftJoin(ShopBase::tableName(),'`'.self::tableName().'`.`shop_id` = `'.ShopBase::tableName().'`.`shop_id`')
                ->andFilterWhere(['like', ShopBase::tableName().'.name', $params['shop_name']]);
        }
        if(!empty($params['shop_sub_name'])) {
            $query->andFilterWhere(['like', ShopSubPaymentSettings::tableName().'.shop_sub_name', $params['shop_sub_name']]);
        }

        return $dataProvider;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopBase()
    {
        return $this->hasOne(ShopBase::className(), ['shop_id' => 'shop_id']);
    }

    /**
     * find操作执行完后格式化数据
     */
    public function formatFindData()
    {
        $now = time();
        $this->setAttribute('cur_rate',self::getEffectRate($now, $this->getAttribute('rate'), $this->getAttribute('new_rate'), $this->getAttribute('effect_time'))); //当前费率
    }

    /**
     * @param $time
     * @param $rate
     * @param $rate_new
     * @param $rate_activate
     * @return mixed
     */
    public static function getEffectRate($time, $rate, $rate_new, $rate_activate)
    {
        return ($time >= $rate_activate && $rate_activate>0) ? $rate_new : $rate;
    }

    public function __construct()
    {
        parent::__construct();
        $this->on(self::EVENT_AFTER_FIND, [$this, 'formatFindData']);
    }

    public function hasAttribute($name)
    {
        if (in_array($name, ['cur_rate'])) {
            return true;
        }
        return parent::hasAttribute($name);
    }
}
