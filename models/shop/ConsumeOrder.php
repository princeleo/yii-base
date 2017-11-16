<?php

namespace app\models\shop;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "consume_order".
 *
 * @property string $id
 * @property integer $consume_date
 * @property string $app_id
 * @property integer $agent_id
 * @property integer $shop_id
 * @property integer $exposure
 * @property integer $click
 * @property integer $ctr
 * @property integer $cpm
 * @property integer $cost
 * @property string $src
 * @property integer $created
 * @property integer $modified
 * @property integer $cpc
 */
class ConsumeOrder extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'consume_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'consume_date',  'agent_id', 'shop_id', 'exposure', 'click', 'ctr', 'cpm', 'cost', 'created', 'cpc','modified'], 'integer'],
            [['app_id','src'], 'string'],
            [['consume_date', 'app_id', 'shop_id'], 'unique', 'targetAttribute' => ['consume_date', 'app_id', 'shop_id'], 'message' => 'The combination of Consume Date, AppId and Shop ID has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'consume_date' => 'Consume Date',
            'app_id' => 'App ID',
            'agent_id' => 'Agent ID',
            'shop_id' => 'Shop ID',
            'exposure' => 'Exposure',
            'click' => 'Click',
            'ctr' => 'Ctr',
            'cpm' => 'Cpm',
            'cost' => 'Cost',
            'created' => 'Created',
            'modified' => 'Modified',
            'src' => 'Src',
            'cpc' => 'Cpc',
        ];
    }

    /**
     * @param $params
     * @param array $with
     * @return ActiveDataProvider
     */
    public function search($params, $with=[])
    {
        $query = ConsumeOrder::find();
        if ($with) {
            $query->with($with);
        }
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load(['ConsumeOrder'=>$params]) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            ConsumeOrder::tableName().'.id' => $this->id,
            ConsumeOrder::tableName().'.agent_id' => $this->agent_id,
            ConsumeOrder::tableName().'.app_id' => $this->app_id,
            ConsumeOrder::tableName().'.src' => $this->src,
            ConsumeOrder::tableName().'.shop_id' => $this->shop_id,
            ConsumeOrder::tableName().'.consume_date' => $this->consume_date,
        ]);
        if (!empty($params['consume_date_s'])) {
            $query->andFilterWhere(['>=', ConsumeOrder::tableName().'.consume_date', $params['consume_date_s']]);
        }
        if (!empty($params['consume_date_e'])) {
            $query->andFilterWhere(['<', ConsumeOrder::tableName().'.consume_date', $params['consume_date_e']]);
        }
        if(!empty($params['shop_name'])) {
            $query->leftJoin(ShopBase::tableName(),'`'.ConsumeOrder::tableName().'`.`shop_id` = `'.ShopBase::tableName().'`.`shop_id`')
                ->andFilterWhere(['like', ShopBase::tableName().'.name', $params['shop_name']]);
        }

        if (!empty($params['sort'])) {
            $query->orderBy($params['sort']);
        }

        return $dataProvider;
    }

    /**
     * @param $params
     * @param $with
     * @return array|null|\yii\db\ActiveRecord
     */
    public function detail($params, $with)
    {
        $query = ConsumeOrder::find();
        if ($with) {
            $query->with($with);
        }

        if (!($this->load(['ConsumeOrder'=>$params]) && $this->validate())) {
            return [];
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'agent_id' => $this->agent_id,
            'shop_id' => $this->shop_id,
            'consume_date' => $this->consume_date,
        ]);

        if (!empty($params['consume_date_s'])) {
            $query->andFilterWhere(['>=', 'consume_date', $params['consume_date_s']]);
        }
        if (!empty($params['creation_time_e'])) {
            $query->andFilterWhere(['<', 'consume_date', $params['consume_date_e']]);
        }
        if(!empty($params['shop_name'])) {
            $query->leftJoin(ShopBase::tableName(), ShopBase::tableName() . ".account_name like '%{$params['shop_name']}%'");
        }

        return $query->asArray()->one();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getShopBase()
    {
        return $this->hasOne(ShopBase::className(), ['shop_id' => 'shop_id']);
    }

    public function getSummary($params)
    {
        $query = ConsumeOrder::find();
        if (!empty($params['selectStr'])) {
            $query->select($params['selectStr']);
        } else {
            $query->select('sum(cost) as iOrderAmount');
        }

        $query->andFilterWhere([
            ConsumeOrder::tableName().'.id' => isset($params['id']) ? $params['id'] : null,
            ConsumeOrder::tableName().'.agent_id' => isset($params['agent_id']) ? $params['agent_id'] : null,
            ConsumeOrder::tableName().'.shop_id' => isset($params['shop_id']) ? $params['shop_id'] : null,
        ]);
        if (!empty($params['consume_date_s'])) {
            $query->andFilterWhere(['>=', 'consume_date', $params['consume_date_s']]);
        }
        if (!empty($params['creation_time_e'])) {
            $query->andFilterWhere(['<', 'consume_date', $params['consume_date_e']]);
        }


        return $query->asArray()->one();
    }
}
