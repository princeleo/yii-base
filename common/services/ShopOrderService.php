<?php
/**
 * Created by PhpStorm.
 * User: lk2015
 * Date: 2016/12/30
 * Time: 14:37
 */

namespace app\common\services;

use app\common\ResultModel;
use app\models\shop\ShopSubPaymentSettings;
use yii\base\Component;
use Yii;

class ShopOrderService extends Component
{
    /**
     * @param $params
     * @return array
     */
    public function getShopSubPaymentList($params)
    {
        $searchModel = new ShopSubPaymentSettings();
        $dataProvider = $searchModel->search($params);
        $resultModel = new ResultModel();
        $dataProvider->setPagination(['pageSize' => $params['per-page'],'page' => $params['page']]);
        return $resultModel->result($dataProvider->getModels(),$dataProvider->getPagination());
    }
}