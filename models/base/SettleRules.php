<?php

namespace app\models\base;

use app\common\helpers\BaseHelper;
use Yii;

/**
 * This is the model class for table "base_settle_rules".
 *
 * @property integer $id
 * @property string $app_id
 * @property integer $pid
 * @property integer $model
 * @property string $name
 * @property string $remark
 * @property string $fields
 * @property integer $type
 * @property integer $group_id
 * @property integer $status
 * @property integer $modified
 * @property integer $created
 */
class SettleRules extends \app\models\BaseModel
{
    const STATUS_DEFAULT = 1;
    const STATUS_DISABLE = -1;

    public static function getStatus()
    {
        return [
            self::STATUS_DEFAULT => '启用',
            self::STATUS_DISABLE => '禁用'
        ];
    }


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'base_settle_rules';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['app_id', 'name', 'fields'], 'required'],
            [['type', 'group_id', 'status', 'modified', 'created'], 'integer'],
            [['app_id', 'name'], 'string', 'max' => 100],
            [['remark'], 'string', 'max' => 200],
            [['fields'], 'string', 'max' => 500]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '自增ID',
            'app_id' => '平台ID',
            'name' => '规则名称',
            'remark' => '规则描述',
            'fields' => '规则字段JSON集合',
            'type' => '分佣规则类型：1，流水，2消费',
            'group_id' => '所属分佣项',
            'status' => '状态：1正常，-1禁用',
            'modified' => '更新时间',
            'created' => '创建时间',
        ];
    }

    public function findList($where)
    {
        $list = BaseHelper::recordListToArray($this->find()->where($where)->all());

        $result = array();
        foreach($list as $li){
            $li['fields'] = json_decode($li['fields'],true);
            $result[$li['id']] = $li;
        }
        return $result;
    }

    public function getSettleGroup()
    {
        return $this->hasOne(SettleGroup::className(), ['group_id' => 'group_id']);
    }
}
