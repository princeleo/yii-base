<?php

namespace app\models\message;
use app\models\BaseModel;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;
use Yii;
class Message extends BaseModel
{

    /**消息内容
     * 声明数据库中的表名
     */
    public static function tableName()
    {
        return '{{%base_message}}';
    }

    /**
     * 定义表中各个字段的规则，用于验证
     */
    public function rules()
    {
        return [
            [['title'], 'required', 'message' => '标题不能为空!'],
            [['content'], 'required', 'message' => '内容不能为空!'],
            [['title', 'content','scene','write_name'], 'string'],
            [['model_type','deleted', 'status','is_import', 'user_id','created','modified','appid','template_id','is_show'], 'integer'],

        ];
    }

    /**
     * 根据条件获取相应的列表和页码信息
     *
     * @return array $data 以及页码信息
     */
    public function getLists($params, $page = 1, $limit = 10)
    {
        $query = self::find()->where(['deleted' => 1,'model_type'=>1]);

        if (isset($params['title']) && $params['title']) {
            $query->andFilterWhere(['like', 'title', $params['title']]);
        }
        if (isset($params['status']) && $params['status']) {
            $query->andFilterWhere(['status' => $params['status']]);
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

    /**
     * 服务商根据条件获取相应的列表和页码信息
     *
     * @return array $data 以及页码信息
     */
    public function getAgentLists($params, $page = 1, $limit = 10)
    {

        $sql='';
        $par=array();
        if (isset($params['title']) && $params['title']) {
            $sql=$sql .' and title=:title ';
            $par=[':title'=>$params['title']];
        }
        if (isset($params['is_read']) && $params['is_read']) {
            $sql=$sql .' and is_read=:is_read ';
            $par=[':is_read'=>$params['is_read']];
        }
        $count = Yii::$app->db->createCommand('SELECT  count(*)  FROM  '.self::tableName().'  INNER JOIN  '.MessageClientUser::tableName().'  ON  '.MessageClientUser::tableName().' .message_id= '.self::tableName().' .id WHERE ( deleted =1) AND ( type_id =1) '.$sql .' order by '.self::tableName().'.id desc',$par)->queryScalar();
        $provider = new SqlDataProvider(['sql' => 'SELECT  '.self::tableName().'.* ,  is_read  FROM  '.self::tableName().'  INNER JOIN  '.MessageClientUser::tableName().'  ON  '.MessageClientUser::tableName().' .message_id= '.self::tableName().' .id WHERE ( deleted =1) AND ( type_id =1)  '.$sql .' order by '.self::tableName().'.id desc',
            'params' => $par,
            'totalCount' => $count,
            'pagination' => [
                'pageSize' => 10,
            ],
        ]);

        return $provider;
    }

    /**
     * 获取通知中心数据
     *
     * @return array $data
     */
    public function GetTopLists($agent_id,$limit = 7)
    {

      //  $query = Message::find()->select('title,id')->where(['deleted' => 1])->limit($limit);

        $query = self::find()->select(self::tableName() . '.id,title')
            ->InnerJoin(MessageClientUser::tableName(), MessageClientUser::tableName() . '.message_id=' . self::tableName() . '.id')
            ->orderBy(self::tableName().'.id desc')
            ->limit($limit);
        if (!empty($model_type)) {
            $query->andFilterWhere([MessageClientUser::tableName().'.agent_id' => $agent_id]);
        }


        // 返回一个Post实例的数组
        $posts = $query;
        $result = [
            'lists' => $query->asArray()->all()
        ];
        return $result;
    }

    /**状态集合
     * @return array
     */
    public static  function getStatus(){
        return [
            '1'=>'草稿',
            '2'=>'待审核',
            '3'=>'审核不通过',
            '4'=>'待发布',
            '5'=>'已发布',
        ];
    }

    /**
     * 获取服务商自动通知数据
     *
     * @return array $data
     */
    public function GetAccordLists($agent_id)
    {

        //  $query = Message::find()->select('title,id')->where(['deleted' => 1])->limit($limit);

        $query = self::find()->select(self::tableName() . '.id,title,template_id,content')
            ->InnerJoin(MessageClientUser::tableName(), MessageClientUser::tableName() . '.message_id=' . self::tableName() . '.id')
            ->where(['model_type'=>2])
            ->andWhere(['is_read'=>2])
            ->orderBy(self::tableName().'.id desc');
           // ->limit($limit);
        if (!empty($model_type)) {
            $query->andFilterWhere([MessageClientUser::tableName().'.agent_id' => $agent_id]);
        }


        // 返回一个Post实例的数组
        $posts = $query;
        $result = [
            'total' => $query->count(),
            'lists' => $query->asArray()->all()
        ];
        return $result;
    }


    /**根据公告ID回去数据
     * @param $id
     * @return null|static
     */
    protected function findModel($id)
    {
        if (($model = Message::findOne($id)) !== null) {
            return $model;
        }
    }


}
