<?php
namespace app\models\shop;

use app\models\BaseModel;

/**
 * Created by PhpStorm.
 * User: feiyu
 * Date: 2017/6/27
 * Time: 14:06
 */

/**
 * This is the model class for table "shop_printer_log".
 *
 * @property integer $id
 * @property integer $merchant_id
 * @property integer $connect_type
 * @property string $data_log
 * @property integer $created
 * @property string $os
 * @property string $app_version
 * @property string $app_version_code
 */
class ShopPrinterLog extends BaseModel
{
    public static function tableName()
    {
        return 'shop_printer_log';
    }
}