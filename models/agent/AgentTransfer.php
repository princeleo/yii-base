<?php

namespace app\models\agent;

use Yii;
use yii\data\ActiveDataProvider;


class AgentTransfer extends \app\models\BaseModel
{
    const STATUS_DEFAULT = 1;
    const STATUS_EFFECTIVE = 2;

    public static function getStatus()
    {
        return [
            self::STATUS_DEFAULT => '待生效',
            self::STATUS_EFFECTIVE => '已生效'
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'agent_transfer';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['old_agent_id', 'new_agent_id', 'shops', 'effective_time', 'action_name', 'transfer_num'], 'required'],
            [['status', 'modified', 'created'], 'integer'],
            [['shop_names'], 'safe'],
        ];
    }

    public function search($params,$with=[])
    {
        $query = AgentTransfer::find();

        if ($with) {
            $query->with($with);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!empty($params["agent_id"])) {
            $query->andFilterWhere(['or','old_agent_id='.$params["agent_id"],'new_agent_id='.$params["agent_id"]]);
        }
        if(!empty($params["agent_name"])){
            $query->leftJoin(AgentBase::tableName(), AgentBase::tableName().".agent_id = ".AgentTransfer::tableName().".old_agent_id");
            $query->orFilterWhere(['like',AgentBase::tableName().'.agent_name' , $params['agent_name']]);
        }

        if(!empty($params["shop_name"])){
            $query->orFilterWhere(['like',AgentTransfer::tableName().'.shop_names' , $params['shop_name']]);
        }

        if(!empty($params["shop_id"])){
            $query->orFilterWhere(['like',AgentTransfer::tableName().'.shops' , $params['shop_id']]);
        }

        if (!empty($params["c_start_time"]) && !empty($params["c_end_time"])) {
            $query->andWhere(['>', AgentTransfer::tableName().'.created', $params["c_start_time"]])->andWhere(['<', AgentTransfer::tableName().'.created', $params["c_end_time"]]);
        }

        if (!empty($params["e_start_time"]) && !empty($params["e_end_time"])) {
            $query->andWhere(['>=', AgentTransfer::tableName().'.effective_time', $params["e_start_time"]])->andWhere(['<=', AgentTransfer::tableName().'.effective_time', $params["e_end_time"]]);
        }

        if (!empty($params['status'])) {
            $query->andFilterWhere([AgentTransfer::tableName().'.status' => $params['status']]);
        }

        $query->orderBy(AgentTransfer::tableName().'.created desc');
        return $dataProvider;
    }

    public function getOldAgent()
    {
        return $this->hasOne(AgentBase::className(), ['agent_id' => 'old_agent_id']);
    }


    public function getNewAgent()
    {
        return $this->hasOne(AgentBase::className(), ['agent_id' => 'new_agent_id']);
    }
}
