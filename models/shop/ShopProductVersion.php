<?php

namespace app\models\shop;

use app\common\helpers\BaseHelper;
use app\common\helpers\ConstantHelper;
use Yii;

/**
 * This is the model class for table "shop_prodcut_version".
 *
 * @property integer $id
 * @property string $name
 * @property string $app_id
 * @property integer $version_type
 * @property string $remark
 * @property string $setup_fee
 * @property string $service_fee
 * @property string $hardware_ids
 * @property integer $status
 * @property integer $operate_id
 * @property string $operate_user
 * @property integer $created
 * @property integer $modified
 */
class ShopProductVersion extends \app\models\BaseModel
{
    const STATUS_DEFAULT = 1;//正常
    const STATUS_DISABLE = -1; //禁用
    const VERSION_TYPE_Q = 1;//快餐
    const VERSION_TYPE_W = 2;//围餐

    //返回服务费档位级别
    public static function getLevel()
    {
        return [1,2,3,4,5,6,7,8,9];
    }

    public static function getVersionType()
    {
        return [
            self::VERSION_TYPE_Q => '基础版',//快餐
            self::VERSION_TYPE_W => '增强版' //围餐
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
        return 'shop_product_version';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'app_id', 'service_fee'], 'required','on' => 'edit'],
            [['version_type', 'status', 'operate_id', 'created', 'modified'], 'integer'],
            [['remark', 'service_fee'], 'string'],
            [['setup_fee'], 'number'],
            [['name', 'hardware_ids'], 'string', 'max' => 200],
            [['app_id'], 'string', 'max' => 100],
            [['operate_user'], 'string', 'max' => 50]
        ];
    }

    /**
     * 设置场景
     * @return array|void
     */
    public function scenarios()
    {
        return [
            'default' => ['name', 'app_id', 'remark', 'service_fee','version_type','status','operate_id','operate_user','setup_fee','hardware_ids'],
            'edit' => ['name', 'app_id', 'remark', 'service_fee','version_type','status','operate_id','operate_user','setup_fee','hardware_ids']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'app_id' => 'App ID',
            'version_type' => 'Version Type',
            'remark' => 'Remark',
            'setup_fee' => 'Setup Fee',
            'service_fee' => 'Service Fee',
            'hardware_ids' => 'Hardware Ids',
            'status' => 'Status',
            'operate_id' => 'Operate ID',
            'operate_user' => 'Operate User',
            'created' => 'Created',
            'modified' => 'Modified',
        ];
    }

    /**
     * 验证后处理
     */
    public  function afterValidate(){
        $this->setup_fee = BaseHelper::amountYuanToFen($this->setup_fee);
        return true;
    }


    /**
     * @param $params
     * @return \yii\data\ActiveDataProvider
     */
    public function search($params, $with=[])
    {
        $query = ShopProductVersion::find();
        if(!empty($with)) $query->with($with);

        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pagesize' => !empty($params['pageSize']) ? $params['pageSize'] : 20
            ],
            'sort' => [
                'defaultOrder' => [
                    'modified' => SORT_DESC,
                    'id' => SORT_DESC
                ]
            ],
        ]);

        if (!($this->load([$this->formName() => $params]) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'app_id' => $this->app_id,
            'version_type' => $this->version_type,
            //'setup_fee' => $this->setup_fee,
            //'service_fee' => $this->service_fee,
            'status' => $this->status,
            'operate_id' => $this->operate_id,
            'operate_user' => $this->operate_user,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like','hardware_ids',$this->hardware_ids])
            ->andFilterWhere(['like', 'remark', $this->remark]);

        return $dataProvider;
    }


    /**
     * 返回所有产品版本
     */
    public static function allProductVersion()
    {
        $list = ShopProductVersion::find()->where(['status' => self::STATUS_DEFAULT])->all();
        $result = [];

        if($list){
            $list = BaseHelper::recordListToArray($list);
            foreach($list as $li){
                $result[$li['app_id']][] = $li;
            }
        }

        return $result;
    }

	/**
     * detail
     * @param $id
     * @return array
     */
    public function findDetail($id)
    {
        $query = $this->find()->where(['id' => $id])->one();
        return $this->recordToArray($query);
    }
}
