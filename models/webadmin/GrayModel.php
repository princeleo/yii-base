<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/13
 * Time: 20:19
 */

namespace app\models\webadmin;

use Yii;

class GrayModel extends PublicModel
{
    // 内容状态
    const GRAY_TRUE = 1;  // 灰度
    const GRAY_FALSE = 2;  // 隐藏
    public static $StatusList = [
        self::GRAY_TRUE => "灰度",
        self::GRAY_FALSE => "非灰度"
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'gray_level_config';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['app_id', 'shop_id', "status"], 'required'],
            [['app_name', "shop_name"], 'safe'],
        ];
    }

    /**
     * 属性在页面默认显示的Label
     */
    public function attributeLabels()
    {
        return [
            'app_id'    => '平台id',
            'app_name'      => '平台名称',
            'shop_id'   => '商户id',
            'shop_name' => '商户名称',
            'status'    => '1、灰度，2、非灰度',
            'created'   => '创建时间',
            'modified'  => '修改日期',
        ];
    }

    /**
     * 搜索列表
     * @param $params
     * @return array
     */
    public function getList($params)
    {
        $query = self::find();

        if (!empty($params['select'])) {
            $query->select($params['select']);
        }

        if (empty($params['status'])) {
            $query->andFilterWhere(["status" => [self::GRAY_TRUE]]);
        } else {
            $query->andFilterWhere(["status" => $params['status']]);
        }

        if (!empty($params['app_name'])) {
            $query->andFilterWhere(["like", "app_name", $params['app_name']]);
        }

        if (!empty($params['shop_name'])) {
            $query->andFilterWhere(["like", "shop_name", $params['shop_name']]);
        }

        if (!empty($params['groupBy'])) {
            $query->groupBy($params['groupBy']);
        }

        if (!empty($params['orderBy'])) {
            $query->orderBy($params['orderBy']);
        }

        if (!empty($params['retArray'])) {
            $query->asArray();
        }

        if (!empty($params['indexBy'])) {
            $query->indexBy($params['indexBy']);
        }

        if (!empty($params['retSql'])) {
            return $query;
        }

        if (!empty($params['limit'])) {
            $data = $this->page($query, $params['limit']);
        } else {
            $data = $query->all();
        }

        return $data;
    }
}