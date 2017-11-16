<?php
/**
 * DataCenter AgentOrder库
 * Author:ShiYaoJia
 * Class DataCenterModel extends DataCenterModel
 * @package app\models\datacenter
 */

namespace app\models\webadmin;

use Yii;
use yii\data\ActiveDataProvider;
use app\models\datacenter\AgentStatisticsInfoModel;

class ElephantPictures extends \app\models\BaseModel
{

    /**
     * @inheritdoc 建立模型表
     */
    public static function tableName()
    {
        return 'elephant_base_pictures';
    }

    public static function getDb()
    {
        return Yii::$app->get('db_webadmin');
    }

    public function search($params,$with=[])
    {
        $query = ElephantPictures::find();
        if ($with) {
            $query->with($with);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $query->andFilterWhere(['like', ElephantPictures::tableName().'.picture_name', $params['name']]);

        $query->orderBy('created desc');

        return $dataProvider;
    }


}

