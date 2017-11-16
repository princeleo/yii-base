<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/08
 * Time: 16:11
 */

namespace app\controllers;
use app\common\errors\BaseError;
use app\common\exceptions\CustomException;
use Yii;


/**
 * 官网首页
 * Class IndexController
 * @package app\controllers
 */
class IndexController extends BaseController
{
    public function actionIndex()
    {
        throw new CustomException(__METHOD__,BaseError::API_TIMEOUT,'不是超时');
    }
}