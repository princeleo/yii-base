<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/13
 * Time: 20:19
 */

namespace app\models\webadmin;

use Yii;

class FileModel extends PublicModel
{
    // 默认文件分类
    const BANNER = 1;  // Banner
    const COVER = 2;  // Banner
    public static $FILE_TYPE_LIST = [
        self::BANNER => "Banner图",
        self::COVER => "封面图",
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'file';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['file_type_id', 'file_route', 'file_class', 'user_id', 'platform_id'], 'required'],
            [['file_type_id', 'file_class', 'user_id', 'platform_id'], 'integer'],
            [['file_name', 'file_desc', 'file_type_name'], 'string', 'max' => 255]
        ];
    }

    /**
     * 搜索列表
     * @param $params
     * @return array
     */
    public function ToSave($params)
    {
        if ($this->load($params)) {
            if ($this->save()) {
                return self::getDb()->getLastInsertID();
            }
        }
        return null;
    }
}