<?php

namespace app\models\base;

use app\models\BaseModel;
use yii\data\ActiveDataProvider;

class AccessLog extends BaseModel
{


    /**链接类型
     * 声明数据库中的表名
     */
    public static function tableName()
    {
        return '{{%base_access_log}}';
    }

    /**
     * 定义表中各个字段的规则，用于验证
     */
    public function rules()
    {
        return [
            [['login_name','login_ip','login_url' ], 'string'],
        ];
    }

    /**
     * 根据条件获取相应的列表和页码信息
     *
     * @return array $data 以及页码信息
     */
    public function getLists($params, $page = 1, $limit = 10)
    {
        $query = self::find();
        if (isset($params['login_name']) && $params['login_name']) {
            $query->andFilterWhere(['like', 'login_name', $params['login_name']]);
        }
        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $limit,
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
        ]);
        return $provider;
    }



}
