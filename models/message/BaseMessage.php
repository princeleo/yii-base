<?php

namespace app\models\message;

use app\models\notice\TextLinks;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "base_message".
 *
 * @property integer $id
 * @property string $title
 * @property integer $model_type
 * @property string $content
 * @property string $scene
 * @property integer $templet_id
 * @property integer $deleted
 * @property integer $status
 * @property string $app_id
 * @property string $write_name
 * @property integer $is_show
 * @property integer $is_import
 * @property integer $user_id
 * @property integer $created
 * @property integer $modified
 */
class BaseMessage extends \app\models\BaseModel
{
    const STATUS_DRAFT = 1;
    const STATUS_NO_AUDIT = 2;
    const STATUS_NOT_AUDIT = 3;
    const STATUS_AUDIT = 4;
//    const STATUS_NO_PUBLISH = 4;
//    const STATUS_PUBLISH = 5;
    const STATUS_RECALL = 6;
    const DELETE_DEFAULT = 1;
    const DELETE_TRUE = -1;
    const IS_IMPORT = 1;
    const MESSAGE_TYPE_SYS = 2;
    const MESSAGE_TYPE_ADMIN = 1;

    public static function getStatus()
    {
        return [
            self::STATUS_DRAFT => '草稿',
            self::STATUS_NO_AUDIT => '待审核',
            self::STATUS_NOT_AUDIT => '审核不通过',
            self::STATUS_AUDIT => '审核通过',
//            self::STATUS_PUBLISH => '已发布',
//            self::STATUS_NO_PUBLISH => '待发布',
            self::STATUS_RECALL => '已撤回'
        ];
    }

    public static function getMessageType()
    {
        return [
            self::MESSAGE_TYPE_ADMIN => '管理员通知',
            self::MESSAGE_TYPE_SYS => '系统通知'
        ];
    }

    public static function getDeteleStatus()
    {
        return [
            self::DELETE_DEFAULT => '正常',
            self::DELETE_TRUE => '删除'
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'base_message';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'model_type', 'content', 'user_id'], 'required','on'=>'create'],
            [['id', 'model_type', 'template_id', 'deleted', 'status', 'is_show', 'is_import', 'user_id', 'created', 'modified', 'pub_time'], 'integer'],
            [['content'], 'string'],
            [['title'], 'string', 'max' => 50, 'tooLong' => '{attribute}长度必需在50以内'],
            [['scene', 'write_name'], 'string', 'max' => 50],
            [['app_id'], 'string', 'max' => 20]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '主键ID（自增）',
            'title' => '文档标题',
            'model_type' => '消息类型',
            'content' => '通知内容',
            'scene' => '通知场景',
            'template_id' => '模版id',
            'deleted' => '删除状态',
            'status' => '状态',
            'app_id' => '平台id',
            'write_name' => '落款人',
            'is_show' => '是否显示落款人',
            'is_import' => '是否重要',
            'user_id' => '操作者id',
            'pub_time' => '发布时间',
            'created' => '创建日期',
            'modified' => '更新时间',
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
        $query = BaseMessage::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'modified' => SORT_DESC,
                    'id' => SORT_DESC
                ]
            ],
        ]);
        $params['pub_time'] = isset($params['pub_time']) ? strtotime($params['pub_time']) : '';
        if (!($this->load(['BaseMessage'=>$params]) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'status' => $this->status,
            'model_type' => $this->model_type,
            'template_id' => $this->template_id,
            'deleted' => self::DELETE_DEFAULT,
            'is_show' => $this->is_show,
            'is_import' => $this->is_import,
            'user_id' => $this->user_id,
            'created' => $this->created,
            'modified' => $this->modified,
        ]);

        if (!empty($params['title'])) {
            $query->andFilterWhere(['like', BaseMessage::tableName().'.title', $params['title']]);
        }
        if (!empty($params['pub_time_start'])) {
            $query->andFilterWhere(['>=', BaseMessage::tableName().'.pub_time', strtotime($params['pub_time_start']) ? strtotime($params['pub_time_start']) : $params['pub_time_start']]);
        }
        if (!empty($params['pub_time_end'])) {
            $query->andFilterWhere(['<=', BaseMessage::tableName().'.pub_time', strtotime($params['pub_time_end']) ? strtotime($params['pub_time_end']) : $params['pub_time_end']]);
        }

        return $dataProvider;
    }

    public function findOneMessage($id)
    {
        $query = self::find()->where(['id' => $id])->with('links')->with('inbox.agent')->one();
        return $this->recordToArray($query);
    }

    public function getLinks()
    {
        return $this->hasMany(TextLinks::className(), ['notice_id' => 'id'])->onCondition(['model_type' => TextLinks::MODEL_TYPE_MESSAGE]);
    }

    public function getInbox()
    {
        return $this->hasMany(BaseMessageInbox::className(), ['message_id' => 'id']);
    }
}
