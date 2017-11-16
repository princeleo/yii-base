<?php

namespace app\models\wsh;

use Yii;

/**
 * This is the model class for table "WshCompany".
 *
 * @property integer $id
 * @property string $sid
 * @property string $full_name
 * @property string $short_name
 * @property string $address
 * @property string $phone
 * @property integer $category
 * @property string $remark
 * @property string $created
 * @property string $modified
 */
class WshCompany extends \app\models\BaseModel
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'company';
    }

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('db_wsh');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['full_name', 'short_name', 'created', 'modified'], 'required'],
            [['category'], 'integer'],
            [['remark'], 'string'],
            [['created', 'modified'], 'safe'],
            [['sid'], 'string', 'max' => 10],
            [['full_name', 'address'], 'string', 'max' => 250],
            [['short_name', 'phone'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sid' => 'Sid',
            'full_name' => 'Full Name',
            'short_name' => 'Short Name',
            'address' => 'Address',
            'phone' => 'Phone',
            'category' => 'Category',
            'remark' => 'Remark',
            'created' => 'Created',
            'modified' => 'Modified',
        ];
    }
}
