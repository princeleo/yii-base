<?php

namespace app\models\agent;

use app\common\helpers\ConstantHelper;
use app\models\base\BaseDomain;
use app\models\shop\Customer;
use app\models\shop\ShopBase;
use app\models\user\AuthUser;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "agent_base".
 *
 * @property integer $agent_id
 * @property string $agent_auth
 * @property string $agent_name
 * @property string $short_name
 * @property string $acronym_name
 * @property string $company_logo
 * @property string $company_nature
 * @property string $licence
 * @property string $business_type
 * @property integer $licence_start_time
 * @property integer $licence_end_time
 * @property string $tax_number
 * @property integer $tax_start_time
 * @property integer $tax_end_time
 * @property string $legal_person
 * @property integer $reg_money
 * @property string $company_scale
 * @property string $business_scope
 * @property string $org_nubmer
 * @property integer $org_time
 * @property integer $org_end_time
 * @property string $country
 * @property string $province
 * @property string $city
 * @property string $area
 * @property string $address
 * @property string $post_code
 * @property string $telephone
 * @property string $fax_number
 * @property string $website
 * @property string $email
 * @property integer $pledge_money
 * @property string $identify_imgs
 * @property string $contract_imgs
 * @property string $provide_type
 * @property string $account_type
 * @property string $operator
 * @property integer $audit_state
 * @property integer $operator_id
 * @property string $operator_ip
 * @property string $remark
 * @property integer $start_time
 * @property integer $end_time
 * @property integer $status
 * @property integer $modified
 * @property integer $created
 */
class AgentBase extends \app\models\BaseModel
{
    const STATUS_DEFAULT = 1;
    const STATUS_DISABLE = -1;
    const STATUS_EXIT = -2;
    const AUDIT_STATE_DEFAULT = 0;//草稿
    const AUDIT_STATE_STAY_PASS = 1;//待审核
    const AUDIT_STATE_PASS = 2;//待开户
    const AUDIT_STATE_SUCCESS = 3;//开户成功
    const AUDIT_STATE_NOT = -1; //审核不通过

    const BUSINESS_TYPE_DL = 'DL';//代理
    const BUSINESS_TYPE_ZY = 'ZY'; //自营或直营
    const BUSINESS_TYPE_CE = 'CE'; //测试

    const AUTHORIZE_TRADE_GOOD = 1; //商品
    const AUTHORIZE_TRADE_LIFE = 2; //本地生活
    const AUTHORIZE_TRADE_MEDICAL = 3;  //医疗
    const AUTHORIZE_TRADE_FINANCE = 4;  //金融
    const AUTHORIZE_TRADE_LANDED= 5;    //地产
    const AUTHORIZE_TRADE_CAR = 6;  //汽车
    const AUTHORIZE_TRADE_PRODUCE = 7;  //生产制造


    const DOMAIN_TYPE_ZY_TEST = '34'; //所属区域-测试-自营
    const DOMAIN_TYPE_ZY_ONLINE = '10'; //所属区域-正式-自营


    public static function getStatus()
    {
        return [
            self::STATUS_DEFAULT => '正常',
            self::STATUS_DISABLE => '禁用'
        ];
    }

    public static function getAuditState()
    {
        return [
            self::AUDIT_STATE_DEFAULT => '草稿',
            self::AUDIT_STATE_STAY_PASS => '待审核',
            self:: AUDIT_STATE_PASS => '待开户',
            self::AUDIT_STATE_SUCCESS => '开户成功',
            self::AUDIT_STATE_NOT => '审核不通过'
        ];
    }

    public static function getAuditStateView()
    {
        return [
            self::AUDIT_STATE_DEFAULT => '保存为草稿',
            self::AUDIT_STATE_STAY_PASS => '提交审核',
            self:: AUDIT_STATE_PASS => '审核通过',
            self::AUDIT_STATE_SUCCESS => '开户成功',
            self::AUDIT_STATE_NOT => '审核不通过'
        ];
    }

    public static function getBusinessType()
    {
        return [
            self::BUSINESS_TYPE_DL => '代理',
            self::BUSINESS_TYPE_ZY => '直营',
            self::BUSINESS_TYPE_CE => '测试'
        ];
    }

    public static function getAuthorizeTrade()
    {
        return [
            self::AUTHORIZE_TRADE_GOOD => '商品',
            self::AUTHORIZE_TRADE_LIFE => '本地生活',
            self::AUTHORIZE_TRADE_MEDICAL => '医疗',
            self::AUTHORIZE_TRADE_FINANCE => '金融',
            self::AUTHORIZE_TRADE_LANDED => '地产',
            self::AUTHORIZE_TRADE_CAR => '汽车',
            self::AUTHORIZE_TRADE_PRODUCE => '生产制造'
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'agent_base';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['agent_id', 'agent_name', 'short_name', 'identify_imgs', 'contract_imgs', 'operator', 'operator_id'], 'required','on'=>'create'],
            [['agent_id', 'licence_start_time', 'licence_end_time', 'tax_start_time', 'tax_end_time', 'reg_money', 'org_time', 'org_end_time', 'pledge_money', 'audit_state', 'operator_id', 'start_time', 'end_time', 'status', 'modified', 'created'], 'integer'],
            [['org_nubmer'], 'string'],
            [['short_name', 'licence', 'tax_number', 'website'], 'string', 'max' => 100],
            [['agent_auth', 'acronym_name', 'company_nature', 'business_type', 'legal_person', 'company_scale', 'business_scope', 'address', 'email', 'provide_type', 'account_type', 'operator_ip'], 'string', 'max' => 50],
            [['agent_name', 'remark','aptitude_name','other_aptitude_name','legal_identity','auth_remark','agent_scope'], 'string', 'max' => 200],
            [['company_logo','pledge_desc'], 'string', 'max' => 255],
            [['country', 'operator'], 'string', 'max' => 50],
            [['province', 'city', 'area', 'post_code', 'telephone', 'fax_number','domain'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'agent_id' => '服务商ID',
            'agent_auth' => '服务商随机加密串',
            'agent_name' => '服务商全称',
            'short_name' => '服务商简称',
            'acronym_name' => '服务名称字母缩写',
            'company_logo' => '企业LOGO',
            'company_nature' => '企业性质',
            'licence' => '企业执照号',
            'business_type' => '运营模式：ZY自营，DL代理',
            'licence_start_time' => '执照开始时间',
            'licence_end_time' => '执照结束时间',
            'tax_number' => '税务登记号',
            'tax_start_time' => '税务登记时间',
            'tax_end_time' => '税务有效截止时间',
            'legal_person' => '法人',
            'reg_money' => '注册资本，单位W',
            'company_scale' => '公司规模',
            'business_scope' => '经营范围',
            'org_nubmer' => '组织架构号',
            'org_time' => '组织年检时间',
            'org_end_time' => '组织架构登记时间',
            'aptitude_name' => '资质名称',
            'other_aptitude_name' => '其他资质',
            'legal_identity' => '法人身份证号',
            'country' => '国家',
            'province' => '省',
            'city' => '市',
            'area' => '区',
            'address' => '详细地址',
            'post_code' => '邮编',
            'telephone' => '电话',
            'fax_number' => '传真中',
            'website' => '企业网站',
            'email' => '邮箱',
            'pledge_money' => '保证金',
            'provide_type' => '供应方式',
            'account_type' => '结算方式',
            'operator' => '合同录入操作人',
            'audit_state' => '审核状态：0未审核，1审核通过，-1审核未通过',
            'operator_id' => '合同入录人ID',
            'operator_ip' => '操作IP',
            'remark' => '备注',
            'start_time' => '有效开始时间',
            'end_time' => '有效结束时间',
            'status' => '状态：1有效，-1无效',
            'modified' => '更新时间',
            'created' => '添加时间',
            'domain' => '所属区域',
            'pledge_desc' => '保证金说明',
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params, $type = 1)
    {
        if($type == 1){
            $query = AgentBase::find()->with('userDetail');
        }elseif($type == 2){
            $query = AgentBase::find()->select(['agent_id', 'short_name']);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    //'modified' => SORT_DESC,
                    'created' => SORT_DESC
                ]
            ],
            'pagination' => [
                'pageSize' => isset($params['AgentBase']['pageSize']) ? $params['AgentBase']['pageSize'] : 20,
            ],
        ]);
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            AgentBase::tableName().'.agent_id' => $this->agent_id,
            AgentBase::tableName().'.created' => $this->created,
            AgentBase::tableName().'.audit_state' => $this->audit_state,
        ]);

        if ($this->end_time && $this->start_time) {
            $query->andWhere(['<', AgentBase::tableName().'.created', $this->end_time])
                ->andWhere(['>', AgentBase::tableName().'.created', $this->start_time])
                ->andWhere([AgentBase::tableName().'.audit_state' => \app\models\agent\AgentBase::AUDIT_STATE_SUCCESS]);
        }
        if(!empty($params['AgentBase']['source']) && $params['AgentBase']['source'] == 1){
            $query->andWhere(['<', AgentBase::tableName().'.agent_id', 30000]);
        }elseif(!empty($params['AgentBase']['source']) && $params['AgentBase']['source'] == 2){
            $query->andWhere(['>=', AgentBase::tableName().'.agent_id', 30000]);
        }

        if(isset($params['AgentBase']['pass']) && $params['AgentBase']['pass'] == -1){ //过期
            $query->andWhere([
                'or',
                ['<=', AgentBase::tableName().'.end_time', time()],
                ['>=', AgentBase::tableName().'.start_time', time()]
            ]);
        }elseif(isset($params['AgentBase']['pass']) && $params['AgentBase']['pass'] == 1){//未过期
            $query->andWhere(['>',AgentBase::tableName().'.end_time',time()])
                ->andWhere(['<', AgentBase::tableName().'.start_time', time()]);
        }

        if ($this->status) {
            $ids = AuthUser::find()->select(['extend_id'])->where(['status' => $this->status])->column();
            $query->andFilterWhere([AgentBase::tableName().'.agent_id' => array_unique($ids)]);
        }

        if(!empty($params['AgentBase']['authorize_trade'])){
            $query->leftJoin(AgentVictor::tableName(),AgentVictor::tableName().'.agent_id='.AgentBase::tableName().'.agent_id');
            $query->andFilterWhere(['like', AgentVictor::tableName().'.grant_industry', AgentBase::getAuthorizeTrade()[$params['AgentBase']['authorize_trade']]]);
        }
        $query->andFilterWhere(['like', AgentBase::tableName().'.agent_name', $this->agent_name])
            ->andFilterWhere(['like', AgentBase::tableName().'.short_name', $this->short_name]);

        return $dataProvider;
    }


    public function findAgentDetail($agent_id)
    {
        $query = $this->find()->where(['agent_id' => $agent_id])->with('userDetail')->one();
        return $this->recordToArray($query);
    }

    public function getUserDetail()
    {
        return $this->hasOne(AuthUser::className(), ['extend_id' => 'agent_id']);
    }

    public function getAgentAudit()
    {
        return $this->hasMany(AgentAudit::className(), ['agent_id' => 'agent_id']);
    }

    public function getAgentDuty()
    {
        return $this->hasMany(AgentDuty::className(), ['agent_id' => 'agent_id']);
    }

    public function getAgentPicInfo()
    {
        return $this->hasMany(AgentPicInfo::className(), ['agent_id' => 'agent_id']);
    }

    public function getAgentBank()
    {
        return $this->hasOne(AgentBank::className(), ['agent_id' => 'agent_id']);
    }

    public function getAgentSettleRule()
    {
        return $this->hasMany(AgentSettleRules::className(), ['agent_id' => 'agent_id']);
    }

    public function getAgentContract()
    {
        return $this->hasMany(AgentContract::className(), ['agent_id' => 'agent_id']);
    }

    public function getAgentVictor()
    {
        return $this->hasOne(AgentVictor::className(), ['agent_id' => 'agent_id']);
    }



    //区域-服务商-商户关联搜索
    public function searchByDomain($params)
    {
        $query = AgentBase::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'created' => SORT_DESC
                ]
            ],
            'pagination' => [
                'pageSize' => 0,
            ],
        ]);
        if (!($this->load(['AgentBase'=>$params]) && $this->validate())) {
            return $dataProvider;
        }
        $domainId = !empty($params['domain_id']) ? $params['domain_id'] : false;
        if (!$domainId && \Yii::$app->user->identity->is_root) {
            $agentIds = [];
        } else {
            $agentIds = $this->getIdsByDomain($domainId);
        }
        $query->andFilterWhere(['agent_id' => $agentIds]);

        $query->andFilterWhere([
            'agent_id' => $this->agent_id,
        ])->andFilterWhere(['like', 'agent_name', $this->agent_name])
            ->andFilterWhere(['like', 'short_name', $this->short_name]);

        return $dataProvider;
    }

    /**
     * 根据区域id获取服务商id，如果$domainId为false,则获取全部服务商id
     *
     * @param bool|integer $domainId
     *
     * @return mixed
     */
    public static function getIdsByDomain($domainId = false,$appId=100001)
    {
        // 获取特定平台，当前用户的区域id
        $domains = BaseDomain::getDomainByCurrentUser($appId);
        $domainIds = array_keys($domains);

        if ($domainId === false) {
            $domainId = $domainIds;
        } elseif (! in_array($domainId, $domainIds)) {
            return 0;
        }

        $all = AgentBase::find()
            ->select(['agent_id', 'agent_scope', 'domain'])
            ->where(['status' => 1])
            ->andWhere(['<>', 'agent_scope', ''])
            ->andWhere(['<>', 'domain', ''])
            ->andWhere(['audit_state' => 3])
            ->all();
        $ids = [];
        foreach ($all as $item) {
            if (! $item->agent_scope || ! $item->domain) continue;
            if (in_array($appId, explode(',', $item->agent_scope))) {
                $domains = explode(',', $item->domain);
                foreach ($domains as $value) {
                    if (in_array($value, (array)$domainId)) {
                        $ids[] = $item->agent_id;
                    }
                }
            }
        }
        return $ids ?: 0;
    }


    //过滤已退出和停用的服务商
    public static function getAgentList(){
        $query = AgentBase::find()->where(['<>','agent_base.status',AgentBase::STATUS_EXIT])
            ->andFilterWhere(['like','agent_base.agent_scope',ConstantHelper::PLATFORM_ELEPHANT])
            ->leftJoin(AuthUser::tableName(),AuthUser::tableName().'.extend_id='.AgentBase::tableName().'.agent_id')
            ->andFilterWhere(['auth_user.status'=>AuthUser::STATUS_DEFAULT]);
        return $query->asArray()->all();
    }

}
