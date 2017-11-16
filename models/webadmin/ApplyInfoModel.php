<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/13
 * Time: 20:19
 */

namespace app\models\webadmin;

use Yii;

class ApplyInfoModel extends PublicModel
{
    // 内容状态
    const DISPLAY = 1;  // 显示
    const HIDE = 2;  // 隐藏
    const DELETED = 3;  // 删除
    public static $ContentStatus = [
        self::DISPLAY => "显示",
        self::HIDE => "隐藏",
        self::DELETED => "删除",
    ];

    // 类型
    const ATTRACT_INVESTMENT = 1;  // 加盟申请
    const TRIAL_APPLICATION = 2;  // 申请体验
    public static $TypeList = [
        self::ATTRACT_INVESTMENT => "加盟申请",
        self::TRIAL_APPLICATION => "申请体验",
    ];

    // 合作模式
    const COOPERATIVE_MERCHANT = 1;  // 商户合作
    const AGENT_TO_JOIN = 2;  // 代理商加盟
    public static $ModelList = [
        self::COOPERATIVE_MERCHANT => "商户合作",
        self::AGENT_TO_JOIN => "代理商加盟",
    ];

    // 客户角色
    const ELECTRONIC_COMMERCE = 1;  // 电子商务
    const CATERING_TRADE = 2;  // 餐饮行业
    const SERVICE_PROVIDER = 2;  // 服务商
    public static $RoleList = [
        self::ELECTRONIC_COMMERCE => "电子商务",
        self::CATERING_TRADE => "餐饮行业",
        self::SERVICE_PROVIDER => "服务商",
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'apply_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'contacts', 'name', "phone", 'province_id', 'city_id', 'dist_id', 'address', 'email', 'platform_id'], 'required'],
            [['province_name', 'city_name', 'dist_name', 'contacts', 'name', 'email', 'industry', 'ip'], 'string'],
            [['type', 'phone', 'province_id', 'city_id', 'dist_id', 'model', 'role', 'deleted', 'platform_id', 'qq'], 'integer'],
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

        if (!empty($params['with'])) {
            $query->with($params['with']);
        }

        if (empty($params['deleted'])) {
            $query->andFilterWhere(["deleted" => [self::DISPLAY, self::HIDE]]);
        } else {
            $query->andFilterWhere(["deleted" => $params['deleted']]);
        }

        // 申请类型
        if (!empty($params['type'])) {
            $query->andFilterWhere(["type" => $params['type']]);
        }

        // 公司名称
        if (!empty($params['name'])) {
            $query->andFilterWhere(["like", "name", $params['name']]);
        }

        // 联系人
        if (!empty($params['contacts'])) {
            $query->andFilterWhere(["like", "contacts", $params['contacts']]);
        }

        // 手机号
        if (!empty($params['phone'])) {
            $query->andFilterWhere(["=", "phone", $params['phone']]);
        }

        // 平台
        if (!empty($params['platform_id'])) {
            $query->andFilterWhere(["=", "platform_id", $params['platform_id']]);
        }

        // 省份 id
        if (!empty($params['province_id'])) {
            $query->andFilterWhere(["=", "province_id", $params['province_id']]);
        }

        // 市 ID
        if (!empty($params['city_id'])) {
            $query->andFilterWhere(["=", "city_id", $params['city_id']]);
        }

        // 区、县 id
        if (!empty($params['dist_id'])) {
            $query->andFilterWhere(["=", "dist_id", $params['dist_id']]);
        }

        // 详细地址
        if (!empty($params['address'])) {
            $query->andFilterWhere(["like", "address", $params['address']]);
        }

        // email
        if (!empty($params['email'])) {
            $query->andFilterWhere(["=", "email", $params['email']]);
        }

        // 合作模式
        if (!empty($params['model'])) {
            $query->andFilterWhere(["=", "model", $params['model']]);
        }

        // 客户角色
        if (!empty($params['role'])) {
            $query->andFilterWhere(["=", "role", $params['role']]);
        }

        // qq
        if (!empty($params['qq'])) {
            $query->andFilterWhere(["=", "qq", $params['qq']]);
        }

        if (!empty($params['start_time']) && !empty($params['end_time'])) {
            $query->andFilterWhere([">=", "created", $params['start_time']]);
            $query->andFilterWhere(["<=", "created", $params['end_time']]);
        }

        if (!empty($params['groupBy'])) {
            $query->groupBy($params['groupBy']);
        }

        if (!empty($params['orderBy'])) {
            $query->orderBy($params['orderBy']);
        }

        if (!empty($params['array'])) {
            $query->asArray();
        }

        if (!empty($params['re_sql'])) {
            return $query->createCommand()->getRawSql();
        }

        if (!empty($params['limit'])) {
            $data = $this->page($query, $params['limit']);
        } else {
            $data = $query->all();
        }
        return $data;
    }


    /**
     * 搜索列表
     * @param $params
     * @return array
     */
    public function ToSave($params)
    {
        if (empty($params['ApplyInfoModel']['created'])) {
            $params['ApplyInfoModel']['created'] = time();
        }
        if ($this->load($params)) {
            if ($this->save()) {
                if (!empty($params['ApplyInfoModel']['id'])) {
                    return $params['ApplyInfoModel']['id'];
                }
                return self::getDb()->getLastInsertID();
            }
        }
        return null;
    }
}