<?php

namespace app\modules\gateway\controllers;

use app\common\errors\BaseError;
use app\common\log\Log;
use app\models\baseboss\AgentProductVersion;
use app\models\baseboss\ShopProductModel;
use app\models\baseboss\ShopProductVersionModel;
use app\models\shop\Customer;


class ProductVersionController extends BaseController{
    public $layout  = false;
    public $DefaultModel;

    public function init(){
        parent::init();
        $this->DefaultModel = new ShopProductVersionModel();
    }

    /**
     * 根据商户获取已发送消息列表
     */
    public function actionList()
    {
        $rules = array_merge(
            [
                [["app_id", "merc_id"], "required"],
                [["page_size", "app_id"], "integer"],
            ],
            $this->DefaultModel->rules()
        );
        $params = (array)($this->checkForm($this->paramsPost, $rules));

        $params['select'] = ["id", "name", "setup_fee", "version_type"];

        $agent_id = Customer::find()->select("agent_id")->where(["id" => $params['merc_id']])->scalar();
        if (!$agent_id) {
            $this->error(BaseError::NOT_DATA_TO_FIND, "不存在的商户!");
        }

        $params['version_ids'] = AgentProductVersion::allProductVersion($agent_id);
        if (empty($params['version_ids'])) {
            return $this->success();
        }
        $dataProvider = $this->DefaultModel->search([$this->DefaultModel->formName() => array_merge($params, [
            "status" => ShopProductVersionModel::NORMAL
        ])]);
        if (!empty($params['page_size'])) {
            $dataProvider->setPagination(['pageSize' => $params['page_size']]);
        }
        $result = $this->resultModel->resultData($dataProvider->getModels(), $dataProvider->getPagination());

        if (empty($result)) {
            $params = is_array($params) ? $params : [$params];
            Log::warning('@ResultModel Return Null', $params, __METHOD__);
        }
        // 转整形  (第一版的情况 单位最小为分）
        foreach ($result['lists'] as $k => $v) {
            $result['lists'][$k]['setup_fee'] = !empty($result['lists'][$k]['setup_fee']) ? (int)$result['lists'][$k]['setup_fee'] : 0;
        }
        return $this->success($result);
    }

    /**
     * 根据商户获取已发送消息列表
     */
    public function actionDetails()
    {
        $rules = [
                [["id", "app_id"], "required"]
        ];
        $params = (array)($this->checkForm($this->paramsPost, $rules));

        $params['select'] = ["name", "setup_fee", "service_fee", "hardware_ids"];

        $result = ShopProductVersionModel::find()->select([
            "id", "name", "app_id", "version_type", "remark", "setup_fee", "service_fee", "hardware_ids"
        ])->where([
            "id" => $params["id"], "status" => ShopProductVersionModel::NORMAL
        ])->limit(1)->asArray()->one();

        if (empty($result)) {
            $params = is_array($params) ? $params : [$params];
            Log::warning('@ResultModel Return Null', $params, __METHOD__);
            return $this->error(BaseError::NOT_DATA_TO_FIND);
        } else {
            // 添加 硬件 id 集合
            $result['hardware_ids'] = explode(",", $result['hardware_ids']);
            $result['products'] = ShopProductModel::find()->select(["id", "name", "img", "remark", "sell_price", "cost_price", "category"])
                ->where(["id" => $result['hardware_ids']])->asArray()->all();
            unset($result['hardware_ids']);

            // 解析服务费数据
            $result['services'] = json_decode($result['service_fee'], true);
            unset($result['service_fee']);
        }

        // 转整形  (第一版的情况 单位最小为分）
        $result['setup_fee'] = !empty($result['setup_fee']) ? (int)$result['setup_fee'] : 0;
        foreach ($result['products']  as $k => $v) {
            $result['products'][$k]['sell_price'] = !empty($result['products'][$k]['sell_price']) ? (int)$result['products'][$k]['sell_price'] : 0;
            $result['products'][$k]['cost_price'] = !empty($result['products'][$k]['cost_price']) ? (int)$result['products'][$k]['cost_price'] : 0;
        }

        return $this->success($result);
    }
}