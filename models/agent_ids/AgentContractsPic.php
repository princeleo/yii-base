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
class AgentContractsPic extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 't_agent_contracts_pic';
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
}
