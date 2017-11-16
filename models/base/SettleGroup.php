<?php

namespace app\models\base;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "base_settle_group".
 *
 * @property integer $group_id
 * @property string $app_id
 * @property string $remark
 * @property integer $modified
 * @property integer $created
 * @property integer $type
 * @property string $name
 */
class SettleGroup extends \app\models\BaseModel
{
    const SETTLE_TYPE_TRANS = 1;
    const SETTLE_TYPE_CONSUME = 2;
    const SETTLE_TYPE_OPEN = 3;//开户费分佣
    const SETTLE_TYPE_SERVICE = 4;//服务费分佣

    public static function getSettleType()
    {
        return [
            self::SETTLE_TYPE_TRANS => '商户流水分佣',
            self::SETTLE_TYPE_CONSUME => '微信广告消费分佣',
            self::SETTLE_TYPE_OPEN => '开户分佣',
            self::SETTLE_TYPE_SERVICE => '服务费分佣',
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'base_settle_group';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['app_id','name','type'], 'required','on'=>'create'],
            [['modified', 'created', 'type'], 'integer'],
            [['app_id', 'name'], 'string', 'max' => 100],
            [['remark'], 'string', 'max' => 200]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'group_id' => '分佣项ID',
            'app_id' => '分佣平台',
            'remark' => '分佣说明',
            'modified' => '更新时间',
            'created' => '创建时间',
            'type' => '分佣规则类型：1，流水，2消费',
            'name' => '分佣名称',
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
        $query = SettleGroup::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'modified' => SORT_DESC,
                    'group_id' => SORT_DESC
                ]
            ],
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'group_id' => empty($params['SettleGroup']['group_id']) ? null : $params['SettleGroup']['group_id'],
            'modified' => $this->modified,
            'created' => $this->created,
            'type' => $this->type,
            'app_id' => empty($params['SettleGroup']['app_ids']) ? null : $params['SettleGroup']['app_ids'],
        ]);

        $query->andFilterWhere(['like', 'app_id', $this->app_id])
            ->andFilterWhere(['like', 'remark', $this->remark])
            ->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}
