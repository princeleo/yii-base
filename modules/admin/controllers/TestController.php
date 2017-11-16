<?php

namespace app\modules\baseboss\controllers;




use app\controllers\BaseController;

class TestController extends BaseController{
    public $layout  = false;

    public function actionIndex()
    {
        die("hello base boss");
    }
} 