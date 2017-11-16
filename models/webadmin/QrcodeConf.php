<?php

namespace app\models\webadmin;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "elephant_qrcode_conf".
 *
 * @property integer $id
 * @property integer $type
 * @property string $name
 * @property integer $width
 * @property integer $height
 * @property integer $sort
 * @property integer $created
 * @property integer $modified
 */
class QrcodeConf extends PublicModel
{
    const STATUS_DEFAULT = 1; //默认
    const STATUS_DISABLE = -1; //禁用
    const TYPE_DESK_CODE = 1; //桌台码
    const TYPE_PAY_CODE = 2; // 收款码

    public static function getType()
    {
        return [
            self::TYPE_DESK_CODE => '桌台码',
            self::TYPE_PAY_CODE => '收款码',
        ];
    }

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
        return 'elephant_qrcode_conf';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['type', 'name', 'offset_x', 'offset_y'], 'required','on' => 'create'],
            [['id','type', 'offset_y', 'offset_x', 'sort', 'created', 'modified','status'], 'integer'],
            [['name'], 'string', 'max' => 100],
            [['show_img'],'string','max' => 200]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'name' => 'Name',
            'offset_y' => 'Width',
            'offset_x' => 'Height',
            'sort' => 'Sort',
            'created' => 'Created',
            'modified' => 'Modified',
            'status' => 'Status'
        ];
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
        $query = QrcodeConf::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'sort' => SORT_ASC,
                    'modified' => SORT_DESC
                ]
            ],
        ]);

        if (!($this->load(['QrcodeConf' => $params]) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'type' => $this->type,
            'offset_y' => $this->offset_y,
            'offset_x' => $this->offset_x,
            'sort' => $this->sort,
            'created' => $this->created,
            'modified' => $this->modified,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name]);

        return $dataProvider;
    }
}
