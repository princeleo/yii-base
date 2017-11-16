<?php

namespace app\models\agent_ids;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "t_agent_info".
 *
 * @property integer $AgentID
 * @property string $Name
 * @property string $abbreviation
 * @property string $Logo
 * @property string $Domain
 * @property string $Properties
 * @property string $GovernmentAgency
 * @property string $Licence
 * @property string $LicenceDate
 * @property string $LicenceEndDate
 * @property string $TaxNumber
 * @property string $TaxDate
 * @property string $TaxEndDate
 * @property string $LegalMan
 * @property string $RegMoney
 * @property string $OpenBankAddr
 * @property string $Comscale
 * @property string $ManageArea
 * @property string $BusinessFor
 * @property string $Country
 * @property string $Province
 * @property string $City
 * @property string $Area
 * @property string $Address
 * @property string $PostCode
 * @property string $TelArea
 * @property string $Tel
 * @property string $TelExt
 * @property string $FaxArea
 * @property string $Fax
 * @property string $FaxExt
 * @property string $Bank
 * @property string $BankNumber
 * @property string $BankName
 * @property string $WebSite
 * @property string $Email
 * @property string $Contact
 * @property string $ContactPhone
 * @property string $ContactTel
 * @property string $ContactEmail
 * @property string $Remark
 * @property string $CreateDate
 * @property string $CreateName
 * @property string $CreateIp
 * @property string $BrandName
 * @property string $TwoDomain
 * @property string $OrgNumber
 * @property string $OrgDate
 * @property string $OrgEndDate
 * @property string $ContractsNumber
 * @property string $Bond
 * @property integer $IsExamine
 * @property string $NamePinYin
 * @property string $Identify
 */
class AgentInfo extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 't_agent_info';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_agent');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['AgentID'], 'required'],
            [['AgentID', 'IsExamine'], 'integer'],
            [['LicenceDate', 'LicenceEndDate', 'TaxDate', 'TaxEndDate', 'CreateDate', 'OrgDate', 'OrgEndDate'], 'safe'],
            [['ManageArea', 'BusinessFor', 'Remark'], 'string'],
            [['Name', 'Properties', 'GovernmentAgency', 'Licence', 'TaxNumber', 'LegalMan', 'RegMoney', 'OpenBankAddr', 'Comscale', 'Address', 'Bank', 'BankNumber', 'BankName', 'WebSite', 'Email', 'Contact', 'ContactPhone', 'ContactTel', 'ContactEmail', 'BrandName', 'OrgNumber', 'ContractsNumber', 'Bond', 'NamePinYin'], 'string', 'max' => 255],
            [['abbreviation'], 'string', 'max' => 22],
            [['Logo', 'Country', 'Province', 'City', 'Area', 'CreateName', 'CreateIp', 'TwoDomain'], 'string', 'max' => 50],
            [['Domain', 'PostCode', 'Identify'], 'string', 'max' => 20],
            [['TelArea', 'Tel', 'TelExt', 'FaxArea', 'Fax', 'FaxExt'], 'string', 'max' => 32]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'AgentID' => '代理商ID',
            'Name' => '代理商名称',
            'abbreviation' => '代理商简称',
            'Logo' => '代理商Logo',
            'Domain' => '代理商二级域名',
            'Properties' => '企业性质',
            'GovernmentAgency' => '证发机关',
            'Licence' => '营业执照号',
            'LicenceDate' => '业营执照年检时间',
            'LicenceEndDate' => '业营执照截止时间',
            'TaxNumber' => '税务登记证号',
            'TaxDate' => '税务登记证年检时间',
            'TaxEndDate' => '税务登记有效期',
            'LegalMan' => '法人代表',
            'RegMoney' => '注册资金',
            'OpenBankAddr' => '户开地址',
            'Comscale' => '公司规模',
            'ManageArea' => '经营范围',
            'BusinessFor' => '主营产品',
            'Country' => '家国',
            'Province' => '省',
            'City' => '市',
            'Area' => '区',
            'Address' => '地址',
            'PostCode' => '邮政编码',
            'TelArea' => '电话区号',
            'Tel' => '电话',
            'TelExt' => '电话分机',
            'FaxArea' => '传真区号',
            'Fax' => '传真',
            'FaxExt' => '传真分机',
            'Bank' => '开户行',
            'BankNumber' => '账号',
            'BankName' => '账户名',
            'WebSite' => '网址',
            'Email' => '电子邮件',
            'Contact' => '业务联系人',
            'ContactPhone' => '联系人电话',
            'ContactTel' => '联系人手机',
            'ContactEmail' => '联系人电子邮件',
            'Remark' => '备注',
            'CreateDate' => '创建时间',
            'CreateName' => '创建者',
            'CreateIp' => '创建者IP',
            'BrandName' => '品牌名称',
            'TwoDomain' => '二级域名',
            'OrgNumber' => '组织架构号',
            'OrgDate' => '组织架构年检时间',
            'OrgEndDate' => '组织架构登记有效期',
            'ContractsNumber' => '代理合同编号',
            'Bond' => '保证金',
            'IsExamine' => '是否通过审核 (0 待审核 1通过 2不通过)',
            'NamePinYin' => '代理商名称拼音缩写',
            'Identify' => '自营标识，ZY：自营，DL：代理',
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

        $query = AgentInfo::find()->with(['agent','agentPicInfo','agentContractsPic','agentUser','agentContact']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'AgentID' => $this->AgentID
        ]);

        /*$query->andFilterWhere(['like', 'Salt', $this->Salt])
            ->andFilterWhere(['like', 'ProvideType', $this->ProvideType])
            ->andFilterWhere(['like', 'AccountType', $this->AccountType]);*/

        return $dataProvider;
    }


    public function getAgent()
    {
        return $this->hasOne(Agent::className(), ['AgentID' => 'AgentID']);
    }

    public function getAgentPicInfo()
    {
        return $this->hasMany(AgentPicInfo::className(),['AgentID' => 'AgentID']);
    }

    public function getAgentContractsPic()
    {
        return $this->hasMany(AgentContractsPic::className(),['AgentID' => 'AgentID']);
    }

    public function getAgentContact()
    {
        return $this->hasMany(AgentContact::className(),['AgentID' => 'AgentID']);
    }

    public function getAgentUser()
    {
        return $this->hasOne(AgentUser::className(),['AgentID' => 'AgentID']);
    }
}
