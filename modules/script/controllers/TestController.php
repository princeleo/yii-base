<?php

namespace app\modules\script\controllers;




use app\controllers\BaseController;

class TestController extends BaseController{
    public $layout  = false;

    public function actionIndex()
    {
        die("hello script");
    }
} 