<?php

/**
 * Created by PhpStorm.
 * User: ShiYaoJia
 * Date: 2017/08/24
 * Time: 16:50
 */

namespace app\models\baseboss;

use Yii;
use yii\data\ActiveDataProvider;

/**
 * This is the model class for table "shop_product_version".
 *
 * @property integer    $id
 * @property string     $name           版本名称
 * @property string     $app_id         关联平台ID
 * @property integer    $version_type   版本类型：1快餐，2围餐
 * @property integer    $remark         备注
 * @property integer    $setup_fee      开户费
 * @property integer    $service_fee    服务费
 * @property integer    $hardware_ids   硬件id集合，以","隔开，关联shop_product.id
 * @property integer    $status         启用状态：1正常，-1禁用
 * @property integer    $operate_id     操作用户ID，与auth_user关联
 * @property integer    $operate_user   操作用户名
 * @property integer    $created
 * @property integer    $modified
 */
class ShopProductVersionModel extends PublicModel
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

    // 产品类别
    const HARDWARE              = 1;  // 硬件
    const SOFTWARE              = 1;  // 软件
    const SERVICE               = 2;  // 服务
    public static $category = [
        self::HARDWARE  => "硬件",
        self::SOFTWARE  => "软件",
        self::SERVICE   => "服务"
    ];

    // 启用状态
    const NORMAL  = 1;  // 正常
    const DISABLE = 2;  // 禁用

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
            [['name', 'app_id', 'version_type', 'remark', 'setup_fee', 'service_fee','hardware_ids','status','operate_id','operate_user'], 'safe'],
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
        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $params = $params[self::formName()];

        if (!empty($params['select'])) {
            $query->select($params['select']);
        }

        if (!empty($params['version_ids'])) {
            $query->andFilterWhere(["id" => $params['version_ids']]);
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'name' => $this->name,
            'app_id' => $this->app_id,
            'remark' => $this->remark,
            'version_type' => $this->version_type,
            'setup_fee' => $this->setup_fee,
            'service_fee' => $this->service_fee,
            'hardware_ids' => $this->hardware_ids,
            'operate_id' => $this->operate_id,
            'operate_user' => $this->operate_user,
            'status' => $this->status,
            'created' => $this->created,
            'modified' => $this->modified,
        ]);

        if (!empty($params['orderBy'])) {
            $query->orderBy($params['orderBy']);
        }

        if (!empty($params['with'])) {
            $query->with($params['with']);
        }

        return $dataProvider;
    }
}