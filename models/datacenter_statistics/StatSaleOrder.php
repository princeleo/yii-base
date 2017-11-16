<?php

namespace app\models\datacenter_statistics;

use Yii;

/**
 * This is the model class for table "stat_sale_order".
 *
 * @property integer $id
 * @property string $stat_date
 * @property integer $agent_id
 * @property string $agent_name
 * @property integer $shop_nums
 * @property integer $order_amount
 * @property integer $pay_amount
 * @property integer $order_nums
 * @property integer $setup_amount
 * @property integer $service_amount
 * @property integer $hardware_amount
 * @property integer $created
 * @property integer $modified
 */
class StatSaleOrder extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'stat_sale_order';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_datacenter_statistics');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['stat_date'], 'required'],
            [['stat_date'], 'safe'],
            [['agent_id', 'shop_nums', 'order_amount', 'pay_amount', 'order_nums', 'setup_amount', 'service_amount', 'hardware_amount', 'created', 'modified','refund_val','refund_amount','version_type'], 'integer'],
            [['agent_name'], 'string', 'max' => 100],
            [['domain'], 'string', 'max' => 200]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'stat_date' => 'Stat Date',
            'agent_id' => 'Agent ID',
            'agent_name' => 'Agent Name',
            'shop_nums' => 'Shop Nums',
            'order_amount' => 'Order Amount',
            'pay_amount' => 'Pay Amount',
            'order_nums' => 'Order Nums',
            'setup_amount' => 'Setup Amount',
            'service_amount' => 'Service Amount',
            'hardware_amount' => 'Hardware Amount',
            'created' => 'Created',
            'modified' => 'Modified',
        ];
    }
}
