<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/13
 * Time: 20:19
 */

namespace app\models\webadmin;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "banner".
 *
 * @property integer $id
 * @property string $name
 * @property integer $modular
 * @property integer $type_id
 * @property integer $file_id
 * @property string $brief_introduction
 * @property string $content
 * @property string $seo_title
 * @property string $seo_keywords
 * @property string $seo_description
 * @property string $jump_url
 * @property integer $sort
 * @property integer $platform_id
 * @property integer $deleted
 * @property integer $created
 * @property integer $modified
 * @property integer $is_home
 * @property integer $is_hot
 * @property integer $is_new
 * @property integer $is_com
 * @property integer $is_top
 * @property integer $set_top_time
 */
class ContentModel extends PublicModel
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

    // 案例标题
    const ENTERPRISE_INTRODUCTION = 1;  // 企业介绍
    const USING_PURPOSE = 2;    // 运用目的
    const CURRENT_PROBLEMS_ENCOUNTERED = 3; // 目前遇到的问题
    const CUSTOMIZED_SOLUTIONS = 4; // 定制解决方案
    const O2O_ACTIVITY_RESULTS = 5; // O2O活动成果
    const BANNER_IMG = 6;   // Banner图
    public static $caseTitleList = [
        self::ENTERPRISE_INTRODUCTION => "企业介绍",
        self::USING_PURPOSE => "运用目的",
        self::CURRENT_PROBLEMS_ENCOUNTERED => "目前遇到的问题",
        self::CUSTOMIZED_SOLUTIONS => "定制解决方案",
        self::O2O_ACTIVITY_RESULTS => "O2O活动成果",
        self::BANNER_IMG => "Banner图",
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'content';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['modular'], 'required'],
            [['seo_title', 'seo_keywords', 'seo_description', 'brief_introduction', 'name', 'type_id', 'file_id', "content", 'platform_id', ], 'safe'],
            [['jump_url'], 'url'],
            [['sort', 'deleted', 'is_home', 'is_hot', 'is_new', 'is_com', 'is_top', 'set_top_time'], 'integer']
        ];
    }

    /**
     * 属性在页面默认显示的Label
     */
    public function attributeLabels()
    {
        return [
            'modular' => '所属模块：',
            'name' => '内容名称：',
            'type_id' => '所属分类：',
            'created' => '创建时间：',
            'brief_introduction' => '简介：',
            'content' => '内容编辑：',
            'jump_url' => '跳转链接：',
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

        if (!empty($params['name_and_content'])) {
            $query->andFilterWhere(["like", "name", $params['name_and_content']]);
            $query->orFilterWhere(["like", "content", $params['name_and_content']]);
        }

        if (empty($params['deleted'])) {
            $query->andFilterWhere(["deleted" => [self::DISPLAY, self::HIDE]]);
        } else {
            $query->andFilterWhere(["deleted" => $params['deleted']]);
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

        if (!empty($params['is_home'])) {
            $query->andFilterWhere(["=", "is_home", 1]);
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

        if (!empty($params['is_api'])) {
            return $query;
        }

        if (!empty($params['limit'])) {
            $data = $this->page($query, $params['limit']);
        } else {
            $data = $query->all();
        }

        return $data;
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
        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load(['ContentModel'=>$params]) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'name' => $this->name,
            'modular' => $this->modular,
            'type_id' => $this->type_id,
            'file_id' => $this->file_id,
            'brief_introduction' => $this->brief_introduction,
            'content' => $this->content,
            'seo_title' => $this->seo_title,
            'seo_keywords' => $this->seo_keywords,
            'seo_description' => $this->seo_description,
            'jump_url' => $this->jump_url,
            'sort' => $this->sort,
            'platform_id' => $this->platform_id,
            'deleted' => self::DISPLAY,
            'created' => $this->created,
            'modified' => $this->modified,
            'is_home' => $this->is_home,
            'is_hot' => $this->is_hot,
            'is_new' => $this->is_new,
            'is_com' => $this->is_com,
            'is_top' => $this->is_top,
            'set_top_time' => $this->set_top_time
        ]);

        if (!empty($params['orderBy'])) {
            $query->orderBy($params['orderBy']);
        }

        if (!empty($params['with'])) {
            $query->with($params['with']);
        }

        return $dataProvider;
    }







    /**
     * 搜索列表
     * @param $params
     * @return array
     */
    public function ToSave($params)
    {
        if ($this->load($params)) {
            if (!empty($params['ContentModel']['created'])) {
                $this->created = $params['ContentModel']['created'];
            }
            if ($this->save()) {
                if (!empty($params['ContentModel']['id'])) {
                    return ["retCode" => 0, "retMsg" => $params['ContentModel']['id']];
                }
                return ["retCode" => 0, "retMsg" => self::getDb()->getLastInsertID()];
            } else {
                return ["retCode" => 1, "retMsg" => $this->getErrors()];
            }
        }
        return ["retCode" => 1, "retMsg" => "数据载入失败！"];
    }

    /**
     * 获取图片
     */
    public function getFile()
    {
        return $this->hasOne(FileModel::className(),['file_id'=>'file_id']);
    }

    /**
     * 获取分类
     */
    public function getType()
    {
        return $this->hasOne(ContentTypeModel::className(),['id'=>'type_id']);
    }
}