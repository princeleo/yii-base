<?php

namespace app\models\message;

use app\common\log\Log;
use app\models\agent\AgentBase;
use \app\models\BaseModel;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "base_message_inbox".
 *
 * @property integer $id
 * @property integer $agent_id
 * @property integer $message_id
 * @property string $app_id
 * @property integer $is_read
 * @property integer $type
 * @property integer $is_pub
 * @property integer $pub_time
 * @property integer $created
 * @property integer $modified
 * @property integer $as_type
 * @property integer $as_id
 */
class BaseMessageInbox extends BaseModel
{
    const IS_READ_TRUE = 1;
    const IS_READ_FALSE = 2;
    const MESSAGE_TYPE_SYS = 2;
    const MESSAGE_TYPE_ADMIN = 1;
    const IS_PUBLISHED = 1;
    const IS_NOT_PUBLISHED = 0;
    const IS_RECALL_PUBLISHED = 2;

    const AS_AGENT = 1; // 关联服务商
    const AS_SHOP  = 2; // 关联商户

    public static function getPublishStatus()
    {
        return [
            self::IS_PUBLISHED => '已发布',
            self::IS_NOT_PUBLISHED => '未发布',
            self::IS_RECALL_PUBLISHED => '已撤回'
        ];
    }
    public static function getReadStatus()
    {
        return [
            self::IS_READ_FALSE => '未阅读',
            self::IS_READ_TRUE => '已阅读'
        ];
    }

    public static function getMessageType()
    {
        return [
            self::MESSAGE_TYPE_ADMIN => '管理员通知',
            self::MESSAGE_TYPE_SYS => '系统通知'
        ];
    }

    public static function getMessageAsType(){
        return [
            self::AS_AGENT => 'agent_id',
            self::AS_SHOP => 'shop_id'
        ];
    }



    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'base_message_inbox';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['agent_id', 'message_id'], 'required','on' => 'create'],
            [['agent_id', 'message_id', 'is_read', 'type', 'is_pub', 'pub_time', 'created', 'modified', "as_type"], 'integer'],
            [['app_id'], 'string', 'max' => 20],
            [['as_id'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键ID',
            'agent_id' => '服务商id',
            'message_id' => '消息ID',
            'app_id' => '平台标识',
            'is_read' => '阅读',
            'type' => '消息类型',
            'is_pub' => '是否发布',
            'pub_time' => '发布时间',
            'created' => '创建日期',
            'modified' => '更新时间',
            'as_type' => '关联字段类型',
            'as_id' => '关联id',
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
        $this->__getAsInfo($params);
        $query = BaseMessageInbox::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }
        $params = $params[self::formName()];

        $query->andFilterWhere([
            'id' => $this->id,
            'agent_id' => $this->agent_id,
            'message_id' => $this->message_id,
            'is_read' => $this->is_read,
            'type' => $this->type,
            'is_pub' => $this->is_pub,
            'pub_time' => $this->pub_time,
            'created' => $this->created,
            'modified' => $this->modified,
            'as_type' => $this->as_type,
            'as_id' => $this->as_id,
        ]);

        if(!empty($params['select']) && !empty($params['merc_id'])){
            $query->select($params['select'])->with("msg");
            $query->innerJoin(BaseMessage::tableName(),BaseMessage::tableName().'.id = '.self::tableName().'.message_id');
            $query->andFilterWhere([
                BaseMessage::tableName().'.model_type' => $params['model_type'],
                BaseMessage::tableName().'.status' => BaseMessage::STATUS_AUDIT,
            ]);
            $query->orderBy("pub_time desc, is_read asc");
        } else {
            $query->with('message.links');
        }

        $query->andFilterWhere([self::tableName().'.app_id' => $this->app_id]);
        if(!empty($params['title'])){
            $query->innerJoin(BaseMessage::tableName(),BaseMessage::tableName().'.id = '.self::tableName().'.message_id');
            $query->andFilterWhere(['like', BaseMessage::tableName().'.title', $params['title']]);
        }

        if(!empty($params['time_start']) && !empty($params['time_end'])){
            $query->andFilterWhere(['>=', self::tableName().'.pub_time', $params['time_start']]);
            $query->andFilterWhere(['<=', self::tableName().'.pub_time', $params['time_end']]);
        } else {
            $query->andFilterWhere(['<', self::tableName().'.pub_time', time()]);
        }

        return $dataProvider;
    }

    public static  function findUnreadMessage($agent_id)
    {
        return BaseMessageInbox::find()->where(['agent_id' => $agent_id,'is_read' => self::IS_READ_FALSE,'is_pub' => self::IS_PUBLISHED])->count();
    }

    public function findImportantMessage($agent_id)
    {
        $query = BaseMessageInbox::find()->where(['agent_id'=>$agent_id,'is_read' => self::IS_READ_FALSE,'is_pub' => self::IS_PUBLISHED]);
        $query->andFilterWhere(['<', BaseMessageInbox::tableName().'.pub_time', time()]);
        $query->innerJoin(BaseMessage::tableName(),BaseMessage::tableName().'.id = '.self::tableName().'.message_id AND '.BaseMessage::tableName().'.is_import ='.BaseMessage::IS_IMPORT);
        $query->with('message');
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'modified' => SORT_DESC,
                ]
            ],
            'pagination' => [
                'pageSize' => 5,
            ],
        ]);
        return $dataProvider;
    }

    public function findMessageDetail($id,$agent_id)
    {
        $query = BaseMessageInbox::find()->where(['id'=>$id,'agent_id'=>$agent_id,'is_pub'=>self::IS_PUBLISHED])->with('message.links')->one();
        return $this->recordToArray($query);
    }

    /**
     *  获取消息详情
     * @param $params
     * @return array
     */
    public function findMessageInfo($params)
    {
        $this->__getAsInfo($params);
        $params = $params[self::formName()];
        $query = self::find()->select([
            self::tableName().".id",
            BaseMessage::tableName().".title",
            BaseMessage::tableName().".content",
            BaseMessage::tableName().".model_type",
            BaseMessage::tableName().".template_id",
            BaseMessage::tableName().".write_name",
            BaseMessage::tableName().".is_show",
            BaseMessage::tableName().".is_import",
            self::tableName().".pub_time",
            self::tableName().".is_read",
        ])->where([
            self::tableName().'.id' => $params['id'],
            self::tableName().'.app_id' => $params['app_id'],
            self::tableName().'.as_type' => $params['as_type'],
            self::tableName().'.as_id' => $params['as_id'],
            BaseMessage::tableName().'.status' => BaseMessage::STATUS_AUDIT,
            self::tableName().".is_pub" => BaseMessageInbox::IS_PUBLISHED
        ])->leftJoin(BaseMessage::tableName(),BaseMessage::tableName().'.id = '.self::tableName().'.message_id');
        $sql = $query->createCommand()->getRawSql();
        return self::findBySql($sql)->asArray()->one();
    }

    public function getAgent()
    {
        return $this->hasOne(AgentBase::className(), ['agent_id' => 'agent_id']);
    }

    public function getMessage()
    {
        return $this->hasOne(BaseMessage::className(), ['id' => 'message_id']);
    }

    public function publish($message_id)
    {
        if(!empty($message_id) && BaseMessageInbox::updateAll(['is_pub' => BaseMessageInbox::IS_PUBLISHED, 'modified' => time()], ['message_id'=>$message_id])){
            return true;
        }
        return false;
    }

    public function recall($message_id)
    {
        if(!empty($message_id) && BaseMessageInbox::updateAll(['is_pub' => BaseMessageInbox::IS_NOT_PUBLISHED, 'modified' => time()], ['message_id'=>$message_id])){
            return true;
        }
        return false;
    }

    public function getMsg()
    {
        return $this->hasOne(BaseMessage::className(), ['id' => 'message_id'])->select([
            "id", "title", "content", "template_id", "write_name", "is_show", "is_import", "model_type"
        ]);
    }

    public function SaveRead($params){
        $this->__getAsInfo($params);
        $params = $params[self::formName()];
        if (!empty($params['as_id'])) {
            $Model = self::findOne($params['id']);
            if (!$Model) {
                return null;
            }
            if (($Model->is_read != self::IS_READ_TRUE)) {
                $Model->is_read = self::IS_READ_TRUE;
                if (!$Model->save()) {
                    Log::error('BaseMessageInboxModel|SaveRead|save','', $params);
                }
            }
            return true;
        }
        return null;
    }

    /**
     *  获取关联信息
     * @param $params
     */
    public function __getAsInfo(&$params){
        if (!empty($params[self::formName()]['merc_id'])) {
            $params[self::formName()]["as_type"] = self::AS_SHOP;
            $params[self::formName()]["as_id"] = (string)$params[self::formName()]['merc_id'];
            $params[self::formName()]["select"] = [
                self::tableName().".pub_time",
                self::tableName().".id",
                self::tableName().".is_read",
                self::tableName().".message_id",
            ];
        }
    }
}
