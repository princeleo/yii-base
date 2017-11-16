<?php

namespace app\models\agent;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "agent_victor".
 *
 * @property integer $id
 * @property integer $grant_num
 * @property string $agent_name
 * @property string $city
 * @property integer $grant_industry
 * @property integer $agent_level
 * @property integer $up_agent_level
 * @property integer $begin_time
 * @property integer $end_time
 * @property integer $assure_price
 * @property string $contact_man
 * @property string $contact_phone
 * @property string $address
 * @property integer $opt_person
 */
class AgentVictor extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'agent_victor';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['agent_id','grant_num',  'agent_level', 'up_agent_level', 'begin_time', 'end_time', 'assure_price', 'opt_person','status'], 'integer'],
            [['agent_name', 'city','status'], 'required'],
            [['agent_name', 'contact_man', 'contact_address'], 'string', 'max' => 30],
            [['grant_industry','city'], 'string', 'max' => 100],
            [['contact_phone'], 'string', 'max' => 18],
            [['agent_name'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'agent_id' => 'Agent ID',
            'grant_num' => 'Grant Num',
            'agent_name' => 'Agent Name',
            'city' => 'City',
            'grant_industry' => 'Grant Industry',
            'agent_level' => 'Agent Level',
            'up_agent_level' => 'Up Agent Level',
            'begin_time' => 'Begin Time',
            'end_time' => 'End Time',
            'assure_price' => 'Assure Price',
            'contact_man' => 'Contact Man',
            'contact_phone' => 'Contact Phone',
            'contact_address' => 'Address',
            'opt_person' => 'Opt Person',
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
        $query = AgentVictor::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC
                ]
            ],
        ]);

        if (!($this->load(['AgentVictor' => $params]) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'grant_num' => $this->id,
        ]);

        if(!empty($params['orderby'])){
            $query->addOrderBy($params['orderby']);
        }

        return $dataProvider;
    }
}
