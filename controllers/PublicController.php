<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/1/29
 * Time: 16:11
 */

namespace app\controllers;
use Yii;
use yii\web\Controller;


class PublicController extends Controller{
    public $layout  = false;

    /**
     * 统一处理异常
     * @return string
     */
    public function actionError()
    {
        $exception = Yii::$app->errorHandler->exception;
        if($exception  !== null){
            $errorMsg = Yii::$app->errorHandler->exception->getMessage();
            $errorCode = Yii::$app->errorHandler->exception->getCode();
            $className = get_class(Yii::$app->errorHandler->exception);
            $className = end(explode('\\',$className));
            if(!empty($errorCode)){
                if(Yii::$app->request->isAjax || strstr($className,'ApiException') !== FALSE || in_array($className,['ApiException'])){
                    if(strstr($errorMsg,' | ') !== FALSE){
                        $errorMsg =  explode(' | ',$errorMsg);
                    }
                    BaseController::result([],$errorCode,$errorMsg);
                }else{
                    return $this->render('error', ['code'=>$errorCode,'message'=>$errorMsg]);
                }
            }
        }

        return $this->render('error', ['code'=>404,'message'=>'404Page']);
    }
} 