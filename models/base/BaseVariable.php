<?php

namespace app\models\base;

use app\models\BaseModel;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "base_variable".
 *
 * @property integer $id
 * @property string $key
 * @property string $name
 * @property string $value
 * @property string $remark
 * @property integer $type
 * @property integer $created
 * @property integer $modified
 */
class BaseVariable extends BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'base_variable';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['key', 'name', 'value'], 'required','on' => 'create,update'],
            [['value'], 'string'],
            [['type', 'created', 'modified'], 'integer'],
            [['key', 'name'], 'string', 'max' => 255],
            [['remark'], 'string', 'max' => 1022],
            [['key'], 'unique','on' => 'create,update']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'key' => 'Key',
            'name' => 'Name',
            'value' => 'Value',
            'remark' => 'Remark',
            'type' => 'Type',
            'created' => 'Created',
            'modified' => 'Modified',
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
        $query = BaseVariable::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    //'modified' => SORT_DESC,
                    'created' => SORT_DESC
                ]
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'type' => $this->type,
            'created' => $this->created,
            'modified' => $this->modified,
        ]);

        $query->andFilterWhere(['like', 'key', $this->key])
            ->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'value', $this->value])
            ->andFilterWhere(['like', 'remark', $this->remark]);

        return $dataProvider;
    }
}
