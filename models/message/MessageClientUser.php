<?php

namespace app\models\message;
use app\models\BaseModel;
use yii\data\ActiveDataProvider;

class MessageClientUser extends BaseModel
{

    /**发送消息
     * 声明数据库中的表名
     */
    public static function tableName()
    {
        return '{{%base_message_inbox}}';
    }

    /**
     * 定义表中各个字段的规则，用于验证
     */
    public function rules()
    {
        return [
            [['agent_id','message_id','appid', 'is_read', 'is_pub','pub_time','created','modified','type_id'], 'integer'],
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

        if (isset($params['name']) && $params['name']) {
            $query->andFilterWhere(['like', 'name', $params['name']]);
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

    /**获取消息用户信息
     * @param $params
     */
    public function getClientUser($params)
    {
        $query = self::find()->orderBy('id desc');
        if (isset($params['message_id']) && $params['message_id']) {
            $query->where(['message_id'=>$params['message_id']]);
        }
        if (isset($params['type_id']) && $params['type_id']) {
            $query->andWhere(['type_id'=>$params['type_id']]);
        }

        // 输出SQL语句
       // $commandQuery = clone $query;
       // echo $commandQuery->createCommand()->getRawSql();
      //  exit();

        // 返回一个Post实例的数组
        $posts = $query;
        $result = [
            'lists' =>  $this->recordToArray($query->one())
        ];

        return $result;

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
