<?php

namespace app\models\node;
use app\common\helpers\BaseHelper;
use Yii;
use app\models\BaseModel;
use app\common\errors\BaseError;

class Node extends BaseModel
{
    const TYPE_MODULE = 1;
    const TYPE_CONTROLLER = 2;
    const TYPE_ACTION = 3;
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
        return '{{%auth_node}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'pid','app_id','name','title','type'], 'required','on' => 'add'],
            [['pid', 'type', 'modified','created','status'], 'integer'],
            [['name'], 'string','max' => 50],
            [['title','app_id'], 'string', 'max' => 50]
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
     * @return \yii\data\ActiveDataProvider
     */
    public function findNode($params)
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
            self::tableName() . '.app_id' => isset($params['app_id']) ? $params['app_id'] : null,
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
        $query = Node::find();

        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'pid' => $this->pid,
            'app_id' => $this->app_id,
            'type' => $this->type,
            'status' => $this->status,
            'modified' => $this->modified,
            'created' => $this->created,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'title', $this->title]);

        return $dataProvider;
    }


    public function findNodes($params = [])
    {
        $query = $this->find()->where(array_merge(['status' => self::STATUS_DEFAULT],$params))->all();
        return $this->recordListToArray($query);
    }


    /**
     * @param $nodes
     * @return array
     */
    public function getNodeAllTree($nodes)
    {
        if(!is_array($nodes)) return [];

        $tree = $modules = $controllers = $actions = $methods =[];
        foreach($nodes as $node){
            unset($node['modified'],$node['created'],$node['status']);
            $tree[$node['app_id']] = [];
            if($node['type'] == self::TYPE_MODULE){
                $modules[] = $node;
            }elseif($node['type'] == self::TYPE_CONTROLLER){
                $controllers[] = $node;
            }elseif($node['type'] == self::TYPE_ACTION){
                $actions[] = $node;
            }
        }

        foreach($modules as $module){
            $tree[$module['app_id']]['modules'][$module['id']] = $module;
        }

        foreach($actions as $action){
            $methods[$action['pid']][$action['id']] = $action;
        }

        foreach($controllers as $controller){
            $method = isset($methods[$controller['id']]) ? $methods[$controller['id']] : [];
            if(empty($tree[$controller['app_id']]) || empty($tree[$controller['app_id']]['modules']) || empty($tree[$controller['app_id']]['modules'][$controller['pid']])){
                continue;
            }
            $tree[$controller['app_id']]['modules'][$controller['pid']]['controllers'][$controller['id']] = array_merge($controller,['actions'=>$method]);
        }

        return $tree;
    }
}