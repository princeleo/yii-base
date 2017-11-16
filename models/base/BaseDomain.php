<?php

namespace app\models\base;

use app\common\helpers\BaseHelper;
use app\models\agent\AgentBase;
use app\models\shop\ShopBase;
use app\models\user\AuthUser;
use Yii;
use yii\data\ActiveDataProvider;
use app\models\app\BaseApp;

class BaseDomain extends \app\models\BaseModel
{



    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%base_domain}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['app_id', 'name','status'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '自增ID',
            'app_id' => '平台标识',
            'name' => '区域名称',
            'status' => '状态',
            'modified' => '更新时间',
            'created' => '创建时间',
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
        $query = BaseDomain::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'modified' => SORT_DESC,
                    'id' => SORT_DESC
                ]
            ],
        ]);

        if (!($this->load(['BaseDomain'=>$params]) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'status' => 1,
        ]);

        $query->andFilterWhere(['=', 'app_id', $this->app_id])
            ->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }

    /**
     * 返回所有区域
     * @return array
     */
    public static  function getDomain()
    {
        $list = BaseHelper::recordListToArray(self::find()->where(['status' => 1])->all());
        $return = [];
        foreach($list as $li){
            $return[$li['id']] = $li;
        }

        return $return;
    }

    /**
     * 返回区域平台
     * @return array
     */
    public static function getDomainApp()
    {
        $list = BaseHelper::recordListToArray(self::find()->where(['status' => 1])->all());
        $apps = (new BaseApp())->findList();
        $return = [];
        foreach($list as $li){
            if (!array_key_exists($li['app_id'],$return))
            {
                foreach($apps as $app)
                {
                    if($app['id'] == $li['app_id'])
                    {
                        $return[$li['app_id']] = $app['app_name'];
                    }
                }

            }
        }
        return $return;
    }


    public static function getDomainByApp($app_id = null)
    {
        $list = BaseHelper::recordListToArray(self::find()->where(['status' => 1,'app_id'=>$app_id])->all());
        $return = [];
        foreach($list as $li){
            $return[$li['id']] = $li;
        }

        return $return;
    }

    // 服务商和商户获取区域信息公共接口
    public static function AgentAndShopGetDomain($params)
    {
        $ShopIds = $AgentIds = $DomainIds = [];
        if (!empty($params['agent_id']) && empty($params['shop_id']) && empty($params['app_id'])) {
            $AgentIds = $params['agent_id'];
            $DomainIds = self::AgentToDomain($AgentIds);
        } else if (!empty($params['shop_id']) && empty($params['app_id'])) {
            $shopList = ShopBase::find()
                ->select(["shop_id", "agent_id", "app_id"])
                ->where(["shop_id" => $params['shop_id']])
                ->asArray()->all();
            $AgentIds = [];
            if (!empty($shopList) && is_array($shopList)) {
                foreach ($shopList as $k => $v) {
                    if (!in_array($v['agent_id'], $AgentIds)) {
                        $AgentIds[] = $v['agent_id'];
                    }
                    $ShopIds[] = $v['shop_id'];
                }
            }
            $DomainIds = self::AgentToDomain($AgentIds);
        } else if (!empty($params['app_id'])) {
            $query = BaseApp::find()
                ->select([
                    BaseDomain::tableName().".id domain",
                    BaseDomain::tableName().".name DomainName",
                ])
                ->where([BaseApp::tableName().".app_id" => $params['app_id']])
                ->leftJoin(BaseDomain::tableName(), BaseDomain::tableName().".app_id = ".BaseApp::tableName().".id");
            $sql = $query->createCommand()->getRawSql();
            $appIds = BaseApp::findBySql($sql)->asArray()->all();
            if (!empty($appIds)) {
                return $appIds;
            }
        }
        if (empty($DomainIds)) {
            return null;
        }
        unset($params);
        unset($AgentIds);
        return self::AppInfo($DomainIds, $ShopIds);
    }

    // 服务商id 换取
    public static function AgentToDomain($AgentIds)
    {
        $data =[];
        $List = AgentBase::find()
            ->select([
                "agent_id", "domain"
            ])->where(["agent_id" => $AgentIds])->asArray()->all();
        if (!empty($List) && is_array($List)) {
            foreach ($List as $k => $v) {
                $List[$k]['domain'] = explode(",", $v['domain']);
            }
        }
        if (isset($List) && is_array($List)) {
            foreach ($List as $k => $v) {
                if (isset($v['domain']) && is_array($v['domain'])) {
                    foreach ($v['domain'] as $ke => $va) {
                        $data[$k]['agent_id'] = $v['agent_id'];
                        $data[$k]['domain_id'] = $va;
                    }
                }
            }
        }
        return $data;
    }

    // Domain id 换取 Agent、Domain、Shop 信息
    public static function AppInfo($DomainId, $ShopIds = null)
    {
        $List = [];
        if (!empty($DomainId) && is_array($DomainId)) {
            foreach ($DomainId as $k => $v) {
                $query = self::find()->select([
                    ShopBase::tableName().".agent_id",
                    AgentBase::tableName().".agent_name",
                    ShopBase::tableName().".app_id",
                    BaseApp::tableName().".app_name",
                    ShopBase::tableName().".shop_id",
                    ShopBase::tableName().".name shop_name",
                    self::tableName().".name DomainName",
                    self::tableName().".id domain",
                ])->where([
                    self::tableName().".id" => $v['domain_id']
                ])->andFilterWhere([
                    ShopBase::tableName().".agent_id" => $v['agent_id']
                ])->leftJoin(BaseApp::tableName(), BaseApp::tableName().".id = ".self::tableName().".app_id")
                ->leftJoin(ShopBase::tableName(), ShopBase::tableName().".app_id = ".BaseApp::tableName().".app_id")
                ->leftJoin(AgentBase::tableName(), AgentBase::tableName().".agent_id = ".ShopBase::tableName().".agent_id");

                if (!empty($ShopIds)) {
                    $query->andFilterWhere([ShopBase::tableName().".shop_id" => $ShopIds]);
                }

                $sql = $query->createCommand()->getRawSql();
                $data = self::findBySql($sql)->asArray()->all();
                $List = array_merge($List, $data);
            }
        }
        return $List;
    }


    public static function getDomainByCurrentUser($app_id = null)
    {
        $domainIdsArr = array();
        if(!is_array(Yii::$app->user->identity->domain)){
            $domainIdsArr = explode(',', Yii::$app->user->identity->domain);
        }

        $domains = [];
        $allDomains = self::find()
            ->leftJoin('base_app', 'base_app.id = base_domain.app_id')
            ->where(['base_domain.status' => 1])
            ->andWhere(['base_app.app_id' => $app_id])
            ->indexBy('id')
            ->asArray()
            ->all();
        if (Yii::$app->user->identity->is_root) {
            $domains = $allDomains;
        } elseif (Yii::$app->user->identity->is_channels && $domainIdsArr) {
            foreach ($allDomains as $key => $item) {
                if (in_array($key, $domainIdsArr)) {
                    $domains[$key] = $item;
                }
            }
        }
        return $domains;
    }
}
