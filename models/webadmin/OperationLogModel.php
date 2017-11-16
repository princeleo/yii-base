<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/13
 * Time: 20:19
 */

namespace app\models\webadmin;

use Yii;

class OperationLogModel extends PublicModel
{
    // 操作类型
    const OPERATION_TYPE_ADD = 1;  // 新增
    const OPERATION_TYPE_UPDATE = 2;  // 更新
    const OPERATION_TYPE_DELETED = 3;  // 删除
    const OPERATION_TYPE_REFRESH = 4;  // 初始化
    public static $OperationTypeList = [
        self::OPERATION_TYPE_ADD => "新增",
        self::OPERATION_TYPE_UPDATE => "更新",
        self::OPERATION_TYPE_DELETED => "删除",
        self::OPERATION_TYPE_REFRESH => "初始化",
    ];

    // 操作结果
    const OPERATION_RESULT_TRUE = 1;  // 成功
    const OPERATION_RESULT_FALSE = 2;  // 失败
    const OPERATION_RESULT_NULL = 3;  // 空数据
    public static $OperationResultList = [
        self::OPERATION_RESULT_TRUE => "成功",
        self::OPERATION_RESULT_FALSE => "失败",
        self::OPERATION_RESULT_NULL => "空数据",
    ];

    public static $ModuleNameList = [
        "elephant" => "大象模块",
    ];

    public static $ControllerNameList = [
        "gray" => "灰度",
    ];

    public static $ActionNameList = [
        "processing" => "数据处理",
        "refresh-gray-shop" => "初始化灰度商户",
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'operation_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['module_id', 'controller_id', "action_id", 'operation_id', 'operation_type', 'operation_result', 'domain_name', 'ip'], 'required'],
            [['module_name', "controller_name", "action_name", "operation_name", 'result_description'], 'safe'],
        ];
    }

    /**
     * 属性在页面默认显示的Label
     */
    public function attributeLabels()
    {
        return [

        ];
    }

    /**
     * 属性在页面默认显示的Label
     * @param $params
     */
    public function goLog($params){
        $params['OperationLogModel']['module_name'] = !empty(self::$ModuleNameList[$params['OperationLogModel']['module_id']]) ? self::$ModuleNameList[$params['OperationLogModel']['module_id']] : "";
        $params['OperationLogModel']['controller_name'] = !empty(self::$ControllerNameList[$params['OperationLogModel']['controller_id']]) ? self::$ControllerNameList[$params['OperationLogModel']['controller_id']] : "";
        $params['OperationLogModel']['action_name'] = !empty(self::$ActionNameList[$params['OperationLogModel']['action_id']]) ? self::$ActionNameList[$params['OperationLogModel']['action_id']] : "";
        $this->load($params);
        $this->save();
    }
}