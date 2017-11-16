<?php

namespace app\models\agent;

use Yii;
use yii\data\ActiveDataProvider;


class DBToolsAgentVictor extends \app\models\BaseModel
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 't_victor_agent';
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
        $query = DBToolsAgentVictor::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'iAutoId' => SORT_DESC
                ]
            ],
        ]);

        if (!($this->load(['DBToolsAgentVictor' => $params]) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'iAutoId' => $this->iAutoId
        ]);

        if(!empty($params['orderby'])){
            $query->addOrderBy($params['orderby']);
        }

        return $dataProvider;
    }


}
