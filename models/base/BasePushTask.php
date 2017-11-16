<?php

namespace app\models\base;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "base_push_task".
 *
 * @property integer $id
 * @property string $task_key
 * @property string $business
 * @property integer $push_type
 * @property string $temp_id
 * @property string $content
 * @property string $extend
 * @property integer $count
 * @property string $error
 * @property integer $state
 * @property integer $star_time
 * @property integer $modified
 * @property integer $created
 */
class BasePushTask extends \app\models\BaseModel
{
    const PUSH_TYPE_SMS = 1;
    const PUSH_TYPE_WX = 2;
    const PUSH_TYPE_APP = 3;

    const PUSH_STATE_DEFAULT = 0; //默认
    const PUSH_STATE_DONE    = 1;
    const PUSH_STATE_SUCCESS = 2;
    const PUSH_STATE_FAIL = -1;

    const PUSH_IS_READ = 1;//已读
    const PUSH_NOT_READ = 0;//未读

    const MSG_LEVEL_1 = 1;//一般
    const MSG_LEVEL_2 = 2;//比较重要
    const MSG_LEVEL_3 = 3; //重要
    const MSG_LEVEL_4 = 4; //非常重要

    //增加类别 -- 需对接地推APP消息类别
    const MSG_TYPE_1 = 1;//提单
    const MSG_TYPE_2 = 2;//审核不通过
    const MSG_TYPE_3 = 3;//开户成功
    const MSG_TYPE_4 = 4;//商户转移

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'base_push_task';
    }

    public static function getPushType()
    {
        return [
            self::PUSH_TYPE_APP => 'APP推送',
            self::PUSH_TYPE_SMS => '短信',
            self::PUSH_TYPE_WX => '微信'
        ];
    }

    public static function getPushState()
    {
        return [
            self::PUSH_STATE_DEFAULT => '默认',
            self::PUSH_STATE_DONE => '已发送',
            self::PUSH_STATE_SUCCESS => '已成功',
            self::PUSH_STATE_FAIL => '已失败'
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['task_key', 'content', 'modified', 'created'], 'required','on' => 'create'],
            [['push_type', 'count', 'state', 'star_time', 'modified', 'created','level','type'], 'integer'],
            [['content'], 'string'],
            [['task_key', 'temp_id', 'error'], 'string', 'max' => 255],
            [['extend'], 'string', 'max' => 1000],
            [['task_key'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'task_key' => 'Task Key',
            'push_type' => 'Push Type',
            'temp_id' => 'Temp ID',
            'content' => 'Content',
            'extend' => 'Extend',
            'count' => 'Count',
            'error' => 'Error',
            'state' => 'State',
            'star_time' => 'Star Time',
            'modified' => 'Modified',
            'created' => 'Created',
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
        $query = BasePushTask::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'created' => SORT_DESC
                ]
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'push_type' => $this->push_type,
            'count' => $this->count,
            'state' => $this->state,
            'star_time' => $this->star_time,
            'is_read' => $this->is_read,
            'modified' => $this->modified,
            'created' => $this->created,
            'extend' => $this->extend
        ]);

        $query->andFilterWhere(['like', 'task_key', $this->task_key])
            ->andFilterWhere(['like', 'temp_id', $this->temp_id])
            ->andFilterWhere(['like', 'content', $this->content])
            ->andFilterWhere(['like', 'error', $this->error]);

        return $dataProvider;
    }
}
