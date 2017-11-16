<?php

namespace app\models\base;

use app\common\helpers\BaseHelper;
use Yii;

/**
 * This is the model class for table "base_area".
 *
 * @property string $id
 * @property integer $pid
 * @property string $name
 * @property integer $area_type
 */
class BaseArea extends \app\models\BaseModel
{
    const AREA_TYPE_PROVINCE = 1;//省
    const AREA_TYPE_CITY = 2;//市
    const AREA_TYPE_AREA = 3;//区
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'base_area';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'pid', 'name', 'area_type'], 'required'],
            [['id', 'pid', 'area_type'], 'integer'],
            [['name'], 'string', 'max' => 30]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'pid' => 'Pid',
            'name' => 'Name',
            'area_type' => 'Area Type',
        ];
    }


    /**
     * 返回所有省
     * @return array|\yii\db\ActiveRecord[]
     */
    public static  function getProvince()
    {
        $list = BaseHelper::recordListToArray(self::find()->where(['area_type' => self::AREA_TYPE_PROVINCE])->all());
        $return = [];
        foreach($list as $li){
            $return[$li['id']] = $li;
        }

        return $return;
    }

    public static function getCity($pid = null)
    {
        $list =  BaseHelper::recordListToArray(self::find()->where(['area_type' => self::AREA_TYPE_CITY])->andFilterWhere(['pid' => $pid])->all());

        $return = [];
        foreach($list as $li){
            $return[$li['id']] = $li;
        }

        return $return;
    }

    public static function getArea($pid = null)
    {
        $list =  BaseHelper::recordListToArray(self::find()->where(['area_type' => self::AREA_TYPE_AREA])->andFilterWhere(['pid' => $pid])->all());
        $return = [];
        foreach($list as $li){
            $return[$li['id']] = $li;
        }

        return $return;
    }
}
