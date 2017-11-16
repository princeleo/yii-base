<?php

/**
 * Created by PhpStorm.
 * User: ShiYaoJia
 * Date: 2017/08/24
 * Time: 16:50
 */

namespace app\models\baseboss;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "sales_order_pay_info".
 *
 * @property integer    $id
 * @property string     $shop_sales_order_id        sales_order 自增id
 * @property string     $order_no                   订单号
 * @property integer    $pay_status                 支付状态（1、创建支付，2、支付完成，3、支付失败）
 * @property integer    $pay_info                   支付信息
 * @property integer    $transaction_id             威富通交易号
 * @property integer    $time_end                   支付完成时间
 * @property integer    $created
 * @property integer    $modified
 */
class ShopSalesOrderPayInfoModel extends PublicModel
{
    // 支付状态
    const CREATE    = 1;
    const SUCCEED   = 2;
    const FAIL      = 3;
    public $PayStatus = [
        self::CREATE    => "创建支付",
        self::SUCCEED   => "支付完成",
        self::FAIL      => "支付失败",
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_sales_order_pay_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['shop_sales_order_id', 'order_no'], 'required'],  // shop_sales_order_id、订单号
            [[
                'pay_info', 'transaction_id', 'transaction_id', 'time_end', "pay_status"
            ], 'safe'],
            [['pay_status'], 'integer']
        ];
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
        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load(['ContentModel'=>$params]) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'shop_sales_order_id' => $this->shop_sales_order_id,
            'order_no' => $this->order_no,
            'pay_status' => $this->pay_status,
            'pay_info' => $this->pay_info,
            'transaction_id' => $this->transaction_id,
            'time_end' => $this->time_end,
            'created' => $this->created,
            'modified' => $this->modified,
        ]);

        if (!empty($params['orderBy'])) {
            $query->orderBy($params['orderBy']);
        }

        if (!empty($params['with'])) {
            $query->with($params['with']);
        }

        return $dataProvider;
    }

    /**
     * 订单支付成功修改
     * @param $params
     * @return bool
     */
    public static function SucceedOrder($params) {
        $pay_Info["shop_sales_order_id"] = $params['id'];
        $pay_Info["order_no"] = $params['order_no'];
        $pay_Info["pay_status"] = self::SUCCEED;
        $pay_Info["time_end"] = strtotime($params['time_end']);
        $pay_Info["transaction_id"] = $params['transaction_id'];
        if (!empty($params['pay_info'])) {
            $pay_Info["pay_info"] = $params['pay_info'];
        }
        $Model = new ShopSalesOrderPayInfoModel();

        if (!$Model->load([$Model->formName() => $pay_Info]) || !$Model->save()) {
            return false;
        }
        return true;
    }
}