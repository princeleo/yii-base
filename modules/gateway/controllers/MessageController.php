<?php

namespace app\modules\gateway\controllers;

use app\common\errors\BaseError;
use app\common\log\Log;
use app\models\message\BaseMessageInbox;


class MessageController extends BaseController{
    public $layout  = false;
    public $DefaultModel;

    public function init(){
        parent::init();
        $this->DefaultModel = new BaseMessageInbox();
    }

    /**
     * 根据商户获取已发送消息列表
     */
    public function actionMercMsgList()
    {
        //接收参与必须要验证
        //自由rule和model rule组合,正常情况直接使用model rule即可。
        $rules = array_merge(
            [
                [['app_id', "merc_id", "model_type"], 'required'],
                [['app_id'], 'string'],
                [["time_start", "time_end"], "integer"],
                [["page", "page_size", "merc_id", "model_type"], "integer"]
            ],
            $this->DefaultModel->rules()
        );
        $params = (array)($this->checkForm($this->paramsPost, $rules));
        $dataProvider = $this->DefaultModel->search([$this->DefaultModel->formName() => array_merge($params, [
            "is_pub" => BaseMessageInbox::IS_PUBLISHED
        ])]);
        if(!empty($params['page_size'])){
            $dataProvider->setPagination(['pageSize' => $params['page_size']]);
        }
        $result = $this->resultModel->resultData($dataProvider->getModels(), $dataProvider->getPagination());

        if (empty($result)) {
            $params = is_array($params) ? $params : [$params];
            Log::warning('@ResultModel Return Null', $params, __METHOD__);
        }
        $this->success($result);
    }


    /**
     * 消息详情
     * ## 目前针对 商户PC 定义了 merc_id 后期可以对此参数做修改 添加  as_type 即可
     */
    public function actionMsgInfo()
    {
        //接收参与必须要验证
        //自由rule和model rule组合,正常情况直接使用model rule即可。
        $rules = array_merge(
            [
                [['app_id', "id", "merc_id"], 'required'],
                [['app_id'], 'string'],
                [["id", "merc_id"], "integer"]
            ]
        );
        $params = $this->checkForm($this->paramsPost, $rules);
        $result = $this->DefaultModel->findMessageInfo([$this->DefaultModel->formName() => $params]);
        if (empty($result)) {
            Log::warning('@getMessageInfo Null!', (array)$params, __METHOD__);
        }
        $this->success($result);
    }


    /**
     * 消息详情
     * ## 目前针对 商户PC 定义了 merc_id 后期可以对此参数做修改 添加  as_type 即可
     */
    public function actionMsgSaveRead()
    {
        //接收参与必须要验证
        //自由rule和model rule组合,正常情况直接使用model rule即可。
        $rules = array_merge(
            [
                [['app_id', "id", "merc_id"], 'required'],
                [['app_id'], 'string'],
                [["id", "merc_id"], "integer"]
            ]
        );
        $params = $this->checkForm($this->paramsPost, $rules);
        $result = $this->DefaultModel->SaveRead([$this->DefaultModel->formName() => $params]);
        if (!$result) {
            Log::warning('@SaveRead Failure!', (array)$params, __METHOD__);
            $this->error(BaseError::SAVE_ERROR, "查不到改消息信息！");
        }
        $this->success();
    }
}