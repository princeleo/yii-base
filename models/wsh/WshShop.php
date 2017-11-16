<?php

namespace app\models\wsh;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "shop".
 *
 * @property integer $id
 * @property string $name
 * @property integer $category_f
 * @property integer $category_s
 * @property string $qq
 * @property integer $company_sid
 * @property integer $pickup_status
 * @property string $brand_label
 * @property integer $self_platform
 * @property integer $platform_info_id
 * @property string $contract_no
 * @property string $contract_start
 * @property string $contract_end
 * @property integer $group_id
 * @property string $tel
 * @property string $website
 * @property string $addr
 * @property string $desc
 * @property string $bg_img
 * @property string $logo
 * @property integer $review_status
 * @property integer $auto_refund
 * @property integer $version
 * @property integer $created
 * @property integer $modified
 * @property integer $deleted
 * @property integer $after_sale_time_status
 * @property integer $after_sale_handle_time
 * @property string $return_address
 * @property string $return_consignee
 * @property string $return_phone
 * @property string $contact
 * @property integer $is_restaurant
 * @property integer $merchant_id
 * @property integer $boss_auto_refund
 * @property integer $shop_limit
 */
class WshShop extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop';
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
            [['category_f', 'category_s', 'company_sid', 'pickup_status', 'self_platform', 'platform_info_id', 'group_id', 'review_status', 'auto_refund', 'version', 'created', 'modified', 'deleted', 'after_sale_time_status', 'after_sale_handle_time', 'is_restaurant', 'merchant_id', 'shop_limit'], 'integer'],
            [['contract_start', 'contract_end'], 'safe'],
            [['desc'], 'string'],
            [['name', 'contract_no', 'return_consignee'], 'string', 'max' => 50],
            [['qq', 'brand_label', 'contact'], 'string', 'max' => 30],
            [['tel', 'return_phone'], 'string', 'max' => 16],
            [['website'], 'string', 'max' => 200],
            [['addr', 'return_address'], 'string', 'max' => 300],
            [['bg_img', 'logo'], 'string', 'max' => 250]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'category_f' => 'Category F',
            'category_s' => 'Category S',
            'qq' => 'Qq',
            'company_sid' => 'Company Sid',
            'pickup_status' => 'Pickup Status',
            'brand_label' => 'Brand Label',
            'self_platform' => 'Self Platform',
            'platform_info_id' => 'Platform Info ID',
            'contract_no' => 'Contract No',
            'contract_start' => 'Contract Start',
            'contract_end' => 'Contract End',
            'group_id' => 'Group ID',
            'tel' => 'Tel',
            'website' => 'Website',
            'addr' => 'Addr',
            'desc' => 'Desc',
            'bg_img' => 'Bg Img',
            'logo' => 'Logo',
            'review_status' => 'Review Status',
            'auto_refund' => 'Auto Refund',
            'version' => 'Version',
            'created' => 'Created',
            'modified' => 'Modified',
            'deleted' => 'Deleted',
            'after_sale_time_status' => 'After Sale Time Status',
            'after_sale_handle_time' => 'After Sale Handle Time',
            'return_address' => 'Return Address',
            'return_consignee' => 'Return Consignee',
            'return_phone' => 'Return Phone',
            'contact' => 'Contact',
            'is_restaurant' => 'Is Restaurant',
            'merchant_id' => 'Merchant ID',
            //'boss_auto_refund' => 'Boss Auto Refund',
            'shop_limit' => 'Shop Limit',
        ];
    }

    /**
     * @param $params
     * @param array $with
     * @return ActiveDataProvider
     */
    public function search($params, $with=[])
    {
        $query = WshShop::find();
        if ($with) {
            $query->with($with);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load(['WshShop'=>$params]) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'modified' => $this->modified,
            'created' => $this->created
        ]);
        if (!empty($params['created_s_time'])) {
            $query->andFilterWhere(['>=', 'created', $params['created_s_time']]);
        }
        if (!empty($params['created_e_time'])) {
            $query->andFilterWhere(['<', 'created', $params['created_e_time']]);
        }
        if (!empty($params['modified_s_time'])) {
            $query->andFilterWhere(['>=', 'modified', $params['modified_s_time']]);
        }
        if (!empty($params['modified_e_time'])) {
            $query->andFilterWhere(['<', 'modified', $params['modified_e_time']]);
        }

        return $dataProvider;
    }



    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(WshCompany::className(), ['id' => 'group_id']);
    }
}
