<?php

namespace app\modules\admin\controllers;


class TestController extends BaseController{
    public $layout  = false;

    public function actionIndex()
    {
        die("hello base boss");
    }
} 