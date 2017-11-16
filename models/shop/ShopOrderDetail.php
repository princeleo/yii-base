<?php

namespace app\models\shop;

use Yii;

/**
 * This is the model class for table "shop_order_detail".
 *
 * @property string $id
 * @property string $boss_order_no
 * @property string $app_id
 * @property string $order_no
 * @property string $product_info
 * @property string $discount_info
 * @property string $consignee_info
 * @property string $delivery_info
 * @property string $order_log_info
 * @property integer $created
 * @property integer $modified
 */
class ShopOrderDetail extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_order_detail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id','product_info', 'discount_info', 'consignee_info', 'delivery_info', 'order_log_info'], 'string'],
            [['created', 'modified'], 'integer'],
            [['boss_order_no'], 'string', 'max' => 64],
            [['app_id', 'order_no'], 'string', 'max' => 50],
            [['app_id', 'order_no'], 'unique', 'targetAttribute' => ['app_id', 'order_no'], 'message' => 'The combination of App ID and Order No has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'boss_order_no' => 'Boss Order No',
            'app_id' => 'App ID',
            'order_no' => 'Order No',
            'product_info' => 'Product Info',
            'discount_info' => 'Discount Info',
            'consignee_info' => 'Consignee Info',
            'delivery_info' => 'Delivery Info',
            'order_log_info' => 'Order Log Info',
            'created' => 'Created',
            'modified' => 'Modified',
        ];
    }
}
