<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/13
 * Time: 20:19
 */

namespace app\models\webadmin;

use Yii;

class ContentTypeModel extends PublicModel
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

    // 基础模块 --------- 官网
    const ARTICLE_MODULE = 1;  // 文章模块
    const CASE_MODULE = 2;  // 案例模块
    const PRODUCT_INTRODUCTION_MODULE = 3;  // 产品介绍模块
    public static $ModuleList = [
        self::ARTICLE_MODULE => "文章模块",
        self::CASE_MODULE => "案例模块",
        self::PRODUCT_INTRODUCTION_MODULE => "产品介绍模块",
    ];
    // 对应的内容页面 --------- 官网
    public static $contentFile = [
        self::ARTICLE_MODULE => "_content_1",
        self::CASE_MODULE => "_content_2",
        self::PRODUCT_INTRODUCTION_MODULE => "_content_3",
    ];

    // 基础模块 --------- 大象官网
    const ELEPHANT_INFORMATION_CENTER = 1;  // 资讯中心
    const ELEPHANT_MERCHANT_CASE = 2;       // 商家案例
    const ELEPHANT_PRODUCT_GUIDE = 3;       // 使用指南
    const ELEPHANT_PRODUCT_ANNOUNCEMENT = 4;// 产品公告
    const ELEPHANT_COMMON_PROBLEM = 5;      // 产品公告
    public static $ElephantModuleList = [
        self::ELEPHANT_INFORMATION_CENTER => "资讯中心",
        self::ELEPHANT_MERCHANT_CASE => "商家案例",
        self::ELEPHANT_PRODUCT_GUIDE => "使用指南",
//        self::ELEPHANT_PRODUCT_ANNOUNCEMENT => "产品公告",
        self::ELEPHANT_COMMON_PROBLEM => "常见问题",
    ];
    // 对应的内容页面 --------- 大象官网
    public static $ElephantContentFile = [
        self::ELEPHANT_INFORMATION_CENTER => "_content_1",
        self::ELEPHANT_MERCHANT_CASE => "_content_2",
        self::ELEPHANT_PRODUCT_GUIDE => "_content_3",
        self::ELEPHANT_PRODUCT_ANNOUNCEMENT => "_content_4",
        self::ELEPHANT_COMMON_PROBLEM => "_content_5",
    ];

    const SCTEK_NEWS        = 1;    // 公司动态
    const SCTEK_SHOP_CASE   = 2;    // 商户案例
    const SCTEK_HUMANITY    = 3;    // 商户案例
    public static $SctekModuleList = [
        self::SCTEK_NEWS        => '新闻中心',
        self::SCTEK_SHOP_CASE   => '商户案例',
        self::SCTEK_HUMANITY    => '人文盛灿',
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'content_type';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'modular', 'platform_id', "created"], 'required'],
            [['sort', 'deleted'], 'integer'],
            [['desc'], 'string', 'max' => 255]
        ];
    }

    /**
     * 属性在页面默认显示的Label
     */
    public function attributeLabels()
    {
        return [
            'name' => '分类名称：',
            'modular' => '模块类型：',
            'created' => '创建时间：',
            'desc' => '分类备注',
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
            $query->andFilterWhere(['deleted' =>$params['deleted']]);
        }

        if (!empty($params['modular'])) {
            $query->andFilterWhere(["modular" => $params['modular']]);
        }

        if (!empty($params['type_id'])) {
            $query->andFilterWhere(["type_id" => $params['type_id']]);
        }

        if (!empty($params['name'])) {
            $query->andFilterWhere(["like", "name", $params['name']]);
        }

        if (!empty($params['platform_id'])) {
            $query->andFilterWhere(["=", "platform_id", $params['platform_id']]);
        }

        if (!empty($params['is_com'])) {
            $query->andFilterWhere(["=", "is_com", $params['is_com']]);
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
        if (empty($params['ContentTypeModel']['created'])) {
            $params['ContentTypeModel']['created'] = time();
        }
        if ($this->load($params)) {

            if ($this->save()) {
                if (!empty($params['ContentTypeModel']['id'])) {
                    return $params['ContentTypeModel']['id'];
                }
                return self::getDb()->getLastInsertID();
            }
        }
        return null;
    }

    /*
     * 模块 代号
     * */
    public static $ModularCode = [
        "SCTEK_NEWS" => self::SCTEK_NEWS,
        "SCTEK_SHOP_CASE" => self::SCTEK_SHOP_CASE,
        'SCTEK_HUMANITY' => self::SCTEK_HUMANITY
    ];
    public static function _getModular($code){
        if (isset(self::$ModularCode[$code])) {
            return self::$ModularCode[$code];
        }
        return false;
    }
}