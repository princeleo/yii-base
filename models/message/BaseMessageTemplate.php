<?php

namespace app\models\message;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "base_message_templet".
 *
 * @property integer $id
 * @property string $title
 * @property string $content
 * @property integer $status
 * @property integer $created
 * @property integer $modified
 */
class BaseMessageTemplate extends \app\models\BaseModel
{
    const STATUS_DEFAULT = 1;
    const STATUS_DISABLE = -1;

    public static function getStatus()
    {
        return [
            self::STATUS_DEFAULT => '启用',
            self::STATUS_DISABLE => '禁用'
        ];
    }
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'base_message_template';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_key'],'unique'],
            [['title', 'content'], 'required','on' => 'create'],
            [['content','remark','data_config'], 'string'],
            [['data_config'],'string','max' => 1000],
            [['status','created', 'modified','id'], 'integer'],
            [['title','id_key'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '模板ID',
            'id_key' => '唯一业务标识',
            'title' => '消息ID',
            'content' => '模板内容',
            'remark' => '描述',
            'data_config' => '参数配置',
            'status' => '状态：1正常，-1禁用',
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
        $query = BaseMessageTemplate::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'modified' => SORT_DESC,
                    'id' => SORT_DESC
                ]
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'id_key' => $this->id_key,
            'status' => $this->status,
            'created' => $this->created,
            'modified' => $this->modified,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'content', $this->content]);

        return $dataProvider;
    }
}
