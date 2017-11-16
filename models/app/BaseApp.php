<?php

namespace app\models\app;

use app\common\helpers\BaseHelper;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "base_app".
 *
 * @property integer $id
 * @property string $app_id
 * @property string $app_name
 * @property string $app_key
 * @property integer $status
 * @property integer $modified
 * @property integer $created
 */
class BaseApp extends \app\models\BaseModel
{
    const STATUS_DISABLE = -1;
    const STATUS_DEFAULT = 1;

    public static function getStatus()
    {
        return [
            self::STATUS_DEFAULT => '开启',
            self::STATUS_DISABLE => '禁用'
        ];
    }


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%base_app}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['status'], 'integer'],
            ['app_id', 'unique'],
            //[['modified', 'created'], 'required'],
            [['app_id', 'app_name'], 'string', 'max' => 50],
            [['app_key'], 'string', 'max' => 100]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '自增ID',
            'app_id' => '平台标识',
            'app_name' => '平台名称',
            'app_key' => '平台KEY',
            'status' => '状态',
            'modified' => '更新时间',
            'created' => '创建时间',
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
        $query = BaseApp::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'modified' => SORT_DESC,
                    'id' => SORT_DESC
                ]
            ],
        ]);

        if (!($this->load(['BaseApp'=>$params]) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'status' => empty($this->status) || $this->status > 0 ? self::STATUS_DEFAULT : self::STATUS_DISABLE,
        ]);

        $query->andFilterWhere(['like', 'app_id', $this->app_id])
            ->andFilterWhere(['like', 'app_name', $this->app_name])
            ->andFilterWhere(['like', 'app_key', $this->app_key]);

        return $dataProvider;
    }


    /**
     * 统一返回平台
     * @return array|\yii\db\ActiveRecord[]
     */
    public function findList()
    {
        $apps = BaseHelper::recordListToArray(BaseApp::find()->where(['status'=>1])->all());
        $result = array();
        foreach($apps as $app){
            $result[$app['app_id']] = $app;
        }

        return $result;
    }
}
