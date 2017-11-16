<?php

namespace app\models\wsh;

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
 * @property integer $merchant_sub_id
 */
class WshShopSubPaymentSettings extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_sub_payment_settings';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_wsh');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['payment_type', 'payment_bank', 'shop_id', 'shop_sub_id', 'effect_time', 'min_money_per_order', 'max_money_per_order', 'max_money_per_day', 'created', 'modified', 'deleted', 'merchant_id','merchant_sub_id'], 'integer'],
            [['rate', 'new_rate'], 'number'],
            [['account'], 'string', 'max' => 100],
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
            'merchant_sub_id' => 'Merchant Sub ID',
        ];
    }

    /**
     * @param $params
     * @param array $with
     * @return ActiveDataProvider
     */
    public function search($params, $with=[])
    {
        $query = WshShopSubPaymentSettings::find();
        if ($with) {
            $query->with($with);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load(['WshShopSubPaymentSettings'=>$params]) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'shop_id' => $this->shop_id,
            'shop_sub_id' => $this->shop_sub_id,
            'merchant_id' => $this->merchant_id,
            'merchant_sub_id' => $this->merchant_sub_id,
        ]);
        if (!empty($params['start_time'])) {
            $query->andFilterWhere(['>=', 'modified', $params['start_time']]);
        }
        if (!empty($params['end_time'])) {
            $query->andFilterWhere(['<', 'modified', $params['end_time']]);
        }

        return $dataProvider;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWshShop()
    {
        return $this->hasOne(WshShop::className(), ['id' => 'shop_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getWshShopSubStatementApply()
    {
        return $this->hasOne(WshShopSubStatementApply::className(), ['shop_id' => 'shop_id','shop_sub_id' => 'shop_sub_id','merchant_id' => 'merchant_id','merchant_sub_id' => 'merchant_sub_id']);
    }
}
