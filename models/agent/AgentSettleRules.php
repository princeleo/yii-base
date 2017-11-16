<?php

namespace app\models\agent;

use app\common\cache\RedisCache;
use app\models\base\SettleGroup;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "agent_settle_rules".
 *
 * @property integer $id
 * @property string $app_id
 * @property integer $agent_id
 * @property integer $group_id
 * @property string $name
 * @property string $remark
 * @property string $fields
 * @property integer $type
 * @property integer $start_time
 * @property integer $end_time
 * @property integer $modified
 * @property integer $created
 */
class AgentSettleRules extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'agent_settle_rules';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            //[['app_id', 'group_id', 'name', 'fields', 'start_time', 'end_time','agent_id','on'=>'create'], 'required'],
            [['agent_id', 'group_id', 'type', 'start_time', 'end_time', 'modified', 'created','rule_id'], 'integer'],
            [['app_id'], 'string', 'max' => 100],
            [['remark','name','rule_name'], 'string', 'max' => 200],
            [['fields'], 'string', 'max' => 500]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '自增ID',
            'app_id' => '平台ID',
            'agent_id' => '服务商ID',
            'group_id' => '分佣项ID',
            'rule_id' => '分佣规则ID',
            'name' => '分佣名称',
            'rule_name' => '规则名称',
            'remark' => '规则描述',
            'fields' => '规则字段JSON集合',
            'type' => '分佣规则类型：1，流水，2消费',
            'start_time' => '有效开始时间',
            'end_time' => '有效结束时间',
            'modified' => '更新时间',
            'created' => '创建时间',
        ];
    }


    /**
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = AgentSettleRules::find()->with('settleGroup');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'app_id' => $this->app_id,
            'agent_id' => $this->agent_id,
            'group_id' => $this->created,
            'name' => $this->name,
            'type' => $this->type,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time
        ]);

        return $dataProvider;
    }

    public function getSettleGroup()
    {
        return $this->hasOne(SettleGroup::className(), ['group_id' => 'group_id']);
    }

    public function findAgentRules($agent_id)
    {
        $query = $this->find()->where(['agent_id'=>$agent_id])->with('settleGroup')->all();
        $list = $this->recordListToArray($query);

        if(!empty($list)){
            foreach($list as &$li){
                $li['fields'] = json_decode($li['fields'],true);
                if(!empty($li['fields']['field_min'])){
                    $li['fields']['field_min'] = trim($li['fields']['field_min'],'%');
                    $li['fields']['field_max'] = trim($li['fields']['field_max'],'%');
                    $li['fields']['field_val'] = trim($li['fields']['field_val'],'%');
                }
            }
        }

        return $list;
    }

    /**
     * 保存之后删除缓存
     */
    public  function afterSave()
    {
        $key = 'AgentRules_'.$this->agent_id;
        return RedisCache::del($key);
    }
}
