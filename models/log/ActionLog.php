<?php
namespace app\models\log;

use app\models\user\AuthUser;
use \yii\data\ActiveDataProvider;
use app\models\BaseModel;

class ActionLog extends BaseModel
{
    /**
     * @inheritdoc 建立模型表
     */
    public static function tableName()
    {
        return '{{%action_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [[ 'log_id','action_time'], 'integer'],
            [[ 'system_id','resource_type','action_result','resource_id','action_type','action_aid', 'ip'], 'string','max'=>40],
            [[ 'request_host','user_agent'], 'string','max'=>255],
            [[ 'action_params','action_data','ext_data'], 'string','max'=>102400],
        ];
    }

    /**
     * @param $params
     * @param array $with
     * @return ActiveDataProvider
     */
    public function search($params, $with = [])
    {
        $query = self::find();

        if ($with) {
            $query->with($with);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'action_time' => SORT_DESC,
                    'log_id' => SORT_DESC
                ]
            ],
        ]);

        $query->andFilterWhere([
            'system_id' => isset($params['system_id']) ? $params['system_id'] : null,
            'resource_type' => isset($params['resource_type']) ? $params['resource_type'] : null,
            'action_type' => isset($params['action_type']) ? $params['action_type'] : null,
            'action_aid' => isset($params['action_aid']) ? $params['action_aid'] : null,
            'resource_id' => isset($params['resource_id']) ? $params['resource_id'] : null,
            'action_result' => isset($params['action_result']) ? $params['action_result'] : null,
        ]);

        if (! empty($params['action_begin'])) {
            $query->andWhere(['>=', 'action_time', $this->transStringTime($params['action_begin'])]);
        }

        if (! empty($params['action_end'])) {
            $query->andWhere(['<=', 'action_time', $this->transStringTime($params['action_end'])]);
        }

        return $dataProvider;
    }

    /**
     * @param $params
     * @param $with
     * @return array|null|\yii\db\ActiveRecord
     */
    public function detail($params, $with = [])
    {
        $query = self::find();
        if ($with) {
            $query->with($with);
        }

        $query->andFilterWhere([
            'log_id' => $params['log_id'],
        ]);

        return $query->asArray()->one();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthUser()
    {
        return $this->hasOne(AuthUser::className(), ['uid' => 'action_aid']);
    }

    private function transStringTime($value)
    {
        if (! is_numeric($value)) {
            return strtotime($value);
        }
        return $value;
    }
}