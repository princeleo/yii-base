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
 * This is the model class for table "shop_product".
 *
 * @property integer    $id
 * @property string     $name           产品名称
 * @property string     $img            显示图片
 * @property integer    $remark         产品说明
 * @property integer    $sell_price     售价
 * @property integer    $cost_price     成本价
 * @property integer    $category       产品类别：1硬件，2软件，3服务
 * @property integer    $status         产品状态：1正常，-1删除
 * @property integer    $created
 * @property integer    $modified
 */
class ShopProductModel extends PublicModel
{
    // 产品类别
    const HARDWARE              = 1;  // 硬件
    const SOFTWARE              = 1;  // 软件
    const SERVICE               = 2;  // 服务
    public static $category = [
        self::HARDWARE  => "硬件",
        self::SOFTWARE  => "软件",
        self::SERVICE   => "服务"
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'shop_product';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'img', 'remark', 'sell_price', 'cost_price', 'category','status'], 'safe'],
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

        if (!($this->load(['ContentModel'=>$params]) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id' => $this->id,
            'name' => $this->name,
            'img' => $this->img,
            'remark' => $this->remark,
            'sell_price' => $this->sell_price,
            'cost_price' => $this->cost_price,
            'category' => $this->category,
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