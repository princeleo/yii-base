<?php

namespace app\models\baseboss;

use app\common\helpers\BaseHelper;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "agent_product_version".
 *
 * @property integer $id
 * @property integer $agent_id
 * @property integer $version_id
 * @property integer $modified
 * @property integer $created
 */
class AgentProductVersion extends PublicModel
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'agent_product_version';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['agent_id', 'id', 'modified', 'created','version_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'agent_id' => 'Agent ID',
            'version_id' => 'Version ID',
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
    public function search($params, $with = [])
    {
        $query = AgentProductVersion::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'modified' => SORT_DESC,
                    'created' => SORT_DESC
                ]
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            AgentProductVersion::tableName().'.agent_id' => $this->agent_id,
            AgentProductVersion::tableName().'.version_id' => $this->version_id,
        ]);

        if(!empty($with)) $query->with($with);
        return $dataProvider;
    }


    /**
     * 返回所有服务商产品版本
     */
    public static function allProductVersion($agent_id)
    {
        $ids = [];
        $list = AgentProductVersion::find()->select(["version_id"])->where(['agent_id' => $agent_id])->asArray()->all();
        foreach ($list as $v) {
            $ids[] = $v['version_id'];
        }
        return $ids;
    }

    public function getShopProductVersion()
    {
        return $this->hasOne(ShopProductVersionModel::className(), ['id' => 'version_id'])->where(['status'=> ShopProductVersionModel::STATUS_DEFAULT]);
    }
}
