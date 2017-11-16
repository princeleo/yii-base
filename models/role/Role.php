<?php

namespace app\models\role;
use app\common\helpers\BaseHelper;
use Yii;
use app\models\BaseModel;
use app\common\errors\BaseError;

class Role extends BaseModel
{
    const STATUS_DEFAULT = 1;
    const STATUS_DISABLE = -1;

    public static function getStatus()
    {
        return [
            self::STATUS_DEFAULT => '正常',
            self::STATUS_DISABLE => '禁用'
        ];
    }

    /**
     * @inheritdoc 建立模型表
     */
    public static function tableName()
    {
        return '{{%auth_role}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'did','name', 'modified', 'created'], 'required','on' => 'add'],
            [['did', 'pid', 'status', 'modified','created'], 'integer'],
            [['remark'], 'string','max' => 100],
            [['name','app_id'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [];
    }


    /**
     * @param $params
     * @return bool
     * @throws \app\common\exceptions\SqlException
     */
    public function addRole($params)
    {
        if(!isset($params['rid'])){
            $params['created'] = time();
        }
        $params['modified'] = time();
        $this->loadDefaultValues();
        if (!$this->load(['Role' => $params]) || !$result = $this->insert()) {
            $this->setException(BaseError::SVR_ERR,$params,$this->getLastSql());
        }
        return $result ? $result : true;
    }


    /**
     * @param $rid
     * @return array|null|ActiveRecord
     */
    public function findByRid($rid)
    {
        $rid = intval($rid);
        return parent::find()->where(['rid'=>$rid])->one();
    }


    /**
     * @param $params
     * @return \yii\data\ActiveDataProvider
     */
    public function findRole($params)
    {
        $query = $this::find();
        if (isset($params['sortStr'])) {
            $ret = $this->orderStr($params['sortStr'], self::tableName());
            if ($ret) {
                $query->orderBy($ret);
            }
        }
        $provider = $this->activeDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => isset($params['count']) ? $params['count'] : 10,
                'page' => isset($params['page']) ? $params['page'] : 1,
            ],
        ]);

        //创建日期
        if (isset($params['createStart']) && isset($params['createEnd'])) {
            $query->andFilterWhere(['between', self::tableName() . '.created', $params['createStart'], $params['createEnd']]);
        }
        $query->andFilterWhere([
            self::tableName() . '.did' => isset($params['did']) ? $params['did'] : null,
            self::tableName() . '.pid' => isset($params['pid']) ? $params['pid'] : null,
            self::tableName() . '.name' => isset($params['name']) ? $params['name'] : null,
            self::tableName() . '.status' => isset($params['status']) ? $params['status'] : null
        ]);
        return BaseHelper::recordToArray($provider->getModels());
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
        $query = Role::find();

        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pagesize' => 20
            ],
            'sort' => [
                'defaultOrder' => [
                    'modified' => SORT_DESC,
                    'rid' => SORT_DESC
                ]
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'rid' => $this->rid,
            'did' => $this->did,
            'pid' => $this->pid,
            'status' => $this->status,
            'modified' => $this->modified,
            'created' => $this->created,
            'app_id' => $this->app_id,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'remark', $this->remark]);

        return $dataProvider;
    }


    public function findDepartmentRole($where = [])
    {
        $query = $this->find()->where(array_merge(['status'=>self::STATUS_DEFAULT],$where))->all();
        $list = $this->recordListToArray($query);

        $listData = array();
        if(is_array($list)){
            foreach($list as $li){
                $listData[$li['did']][] = $li;
            }
        }

        return $listData;
    }
}