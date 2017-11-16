<?php

namespace app\models\agent_ids;

use Yii;

/**
 * This is the model class for table "t_agent_pic_info".
 *
 * @property integer $Id
 * @property integer $AgentID
 * @property integer $Type
 * @property string $ImgPath
 * @property integer $CreateTime
 * @property integer $Cid
 */
class AgentPicInfo extends \app\models\BaseModel
{
    const BUSINESS_TYPE = 1; //营来执照
    const IDENTITY_TYPE = 2; //法人代表
    const TAX_TYPE = 3 ;//税务
    const ORG_TYPE = 4;//组织架构
    const OTHER_TYPE = 5;//其他


    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 't_agent_pic_info';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_agent');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['AgentID', 'Type'], 'required'],
            [['AgentID', 'Type', 'CreateTime', 'Cid'], 'integer'],
            [['ImgPath'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'Id' => 'ID',
            'AgentID' => 'Agent ID',
            'Type' => '1 营业执照 2 法人身份证 3 税务登记登 4组织架构代码证 5其它证件',
            'ImgPath' => 'Img Path',
            'CreateTime' => '创建时间',
            'Cid' => '创建人',
        ];
    }
}
