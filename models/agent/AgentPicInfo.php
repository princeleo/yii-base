<?php

namespace app\models\agent;

use Yii;

/**
 * This is the model class for table "agent_pic_info".
 *
 * @property integer $id
 * @property integer $agent_id
 * @property integer $type
 * @property string $img_path
 * @property integer $created
 */
class AgentPicInfo extends \app\models\BaseModel
{
    const TYPE_LICENSE = 1; //营业执照
    const TYPE_IDENTITY = 2; //身份证
    const TYPE_TAX = 3; //税务登记
    const TYPE_ORG = 4; //组织架构
    const TYPE_OTHER = 5; //其他
    const TYPE_PLEDGE_MONEY = 6; //保证金
    const TYPE_APTITUDE = 7;//资质

    public static function getPicType()
    {
        return [
            self::TYPE_LICENSE => '营业执照',
            self::TYPE_IDENTITY => '法人身份证',
            self::TYPE_TAX => '税务登记',
            self::TYPE_ORG => '组织架构',
            self::TYPE_OTHER => '其他',
            self::TYPE_PLEDGE_MONEY => '保证金',
            self::TYPE_APTITUDE => '资质名称'
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'agent_pic_info';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['agent_id', 'type'], 'required'],
            [['agent_id', 'type', 'created'], 'integer'],
            [['img_path'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'agent_id' => '服务商ID',
            'type' => '图片类型',
            'img_path' => '图片路径',
            'created' => '创建时间',
        ];
    }


    public function getAgentPics($agent_id,$type)
    {
        $query = $this->find()->where(['AgentPicInfo' => ['agent_id' => $agent_id,'type' => $type]])->all();
        return $this->recordListToArray($query);
    }
}
