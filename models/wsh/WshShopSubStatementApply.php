<?php

namespace app\models\wsh;

use Yii;

/**
 * This is the model class for table "shop_sub_statement_apply".
 *
 * @property integer $id
 * @property string $name
 * @property string $short_name
 * @property integer $partner_type
 * @property integer $account_type
 * @property integer $industry_1
 * @property integer $industry_2
 * @property integer $industry_3
 * @property string $account_name
 * @property string $bank
 * @property string $open_bank
 * @property string $account_no
 * @property string $identity
 * @property string $contact
 * @property string $tel
 * @property string $custom_tel
 * @property string $email
 * @property integer $province_id
 * @property integer $city_id
 * @property string $address
 * @property integer $shop_id
 * @property integer $shop_sub_id
 * @property integer $audit_state
 * @property string $audit_desc
 * @property string $business_licence
 * @property string $tax_reg_cert
 * @property string $org_code_cert
 * @property string $identity_front
 * @property string $identity_back
 * @property string $account_opening_license
 * @property string $bank_no
 * @property integer $is_two_line
 * @property integer $fee_account_type
 * @property string $fee_account_name
 * @property string $fee_account_bank
 * @property string $fee_account_no
 * @property integer $fee
 * @property integer $created
 * @property integer $modified
 * @property integer $business_type
 * @property integer $merchant_id
 * @property integer $merchant_sub_id
 */
class WshShopSubStatementApply extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_sub_statement_apply';
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
            [['partner_type', 'account_type', 'industry_1', 'industry_2', 'industry_3', 'province_id', 'city_id', 'shop_id', 'shop_sub_id', 'audit_state', 'is_two_line', 'fee_account_type', 'fee', 'created', 'modified', 'business_type', 'merchant_id', 'merchant_sub_id'], 'integer'],
            [['name', 'address', 'audit_desc', 'business_licence', 'tax_reg_cert', 'org_code_cert', 'identity_front', 'identity_back', 'account_opening_license'], 'string', 'max' => 255],
            [['short_name', 'account_no', 'contact', 'fee_account_no'], 'string', 'max' => 50],
            [['account_name', 'bank', 'open_bank', 'email', 'fee_account_name', 'fee_account_bank'], 'string', 'max' => 100],
            [['identity', 'tel', 'custom_tel', 'bank_no'], 'string', 'max' => 20]
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
            'short_name' => 'Short Name',
            'partner_type' => 'Partner Type',
            'account_type' => 'Account Type',
            'industry_1' => 'Industry 1',
            'industry_2' => 'Industry 2',
            'industry_3' => 'Industry 3',
            'account_name' => 'Account Name',
            'bank' => 'Bank',
            'open_bank' => 'Open Bank',
            'account_no' => 'Account No',
            'identity' => 'Identity',
            'contact' => 'Contact',
            'tel' => 'Tel',
            'custom_tel' => 'Custom Tel',
            'email' => 'Email',
            'province_id' => 'Province ID',
            'city_id' => 'City ID',
            'address' => 'Address',
            'shop_id' => 'Shop ID',
            'shop_sub_id' => 'Shop Sub ID',
            'audit_state' => 'Audit State',
            'audit_desc' => 'Audit Desc',
            'business_licence' => 'Business Licence',
            'tax_reg_cert' => 'Tax Reg Cert',
            'org_code_cert' => 'Org Code Cert',
            'identity_front' => 'Identity Front',
            'identity_back' => 'Identity Back',
            'account_opening_license' => 'Account Opening License',
            'bank_no' => 'Bank No',
            'is_two_line' => 'Is Two Line',
            'fee_account_type' => 'Fee Account Type',
            'fee_account_name' => 'Fee Account Name',
            'fee_account_bank' => 'Fee Account Bank',
            'fee_account_no' => 'Fee Account No',
            'fee' => 'Fee',
            'created' => 'Created',
            'modified' => 'Modified',
            'business_type' => 'Business Type',
            'merchant_id' => 'Merchant ID',
            'merchant_sub_id' => 'Merchant Sub ID',
        ];
    }
}
