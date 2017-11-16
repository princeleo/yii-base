<?php

namespace app\models\message;
use app\models\BaseModel;
use yii\data\ActiveDataProvider;

class MessageTemplet extends BaseModel
{

    /**消息模版
     * 声明数据库中的表名
     */
    public static function tableName()
    {
        return '{{%base_message_templet}}';
    }

    /**
     * 定义表中各个字段的规则，用于验证
     */
    public function rules()
    {
        return [
            [['title', 'content'], 'string'],
            [['deleted','user_id','created','modified'], 'integer'],
        ];
    }

    /**
     * 根据条件获取相应的列表和页码信息
     *
     * @return array $data 以及页码信息
     */
    public function getLists($params, $page = 1, $limit = 10)
    {
        $query = self::find()->where(['deleted' => 1]);

        if (isset($params['title']) && $params['title']) {
            $query->andFilterWhere(['like', 'title', $params['title']]);
        }
        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $limit,
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
        ]);
        return $provider;
    }
    /**根据公告ID回去数据
     * @param $id
     * @return null|static
     */
    protected function findModel($id)
    {
        if (($model = self::findOne($id)) !== null) {
            return $model;
        }
    }


}
