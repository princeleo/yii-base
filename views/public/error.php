<?php
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

?>
<!DOCTYPE html>
<!--[if IE 8]>         <html class="no-js lt-ie9"> <![endif]-->
<!--[if IE 9]>         <html class="no-js lt-ie10"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js"> <!--<![endif]-->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>503</title>
    <?=Html::cssFile('@web/static/css/bootstrap.min.css')?>
    <?=Html::cssFile('@web/static/css/plugins.css')?>
    <?=Html::cssFile('@web/static/css/main.css')?>
    <?=Html::cssFile('@web/static/css/boss.css')?>
    <?=Html::cssFile('@web/static/css/themes.css')?>
    <?=Html::jsFile('@web/static/js/vendor/modernizr-2.7.1-respond-1.4.2.min.js')?>
    <?=Html::jsFile('@web/static/js/vendor/jquery-1.11.1.min.js')?>
</head>
<body class="white-bg">
<!-- Error Container -->
<div id="error-container">

    <div class="row">
        <div class="col-sm-8 col-sm-offset-2 text-center">
            <div></di><img src="<?=Yii::$app->params['assetsUrl']?>img/<?=($code == 404 ? '404' : '503')?>_1.png"></div>
            <div class="h3 text-primary"><?= (!empty($message) ? Html::encode($message) : '网页暂时无法打开，请稍后再试！'); ?></div>
            <div class="new-error-options pt20">
                <a class="btn btn-alt new-btn-lg btn-default mr20" href="javascript:window.parent.location.href='<?=Yii::$app->getHomeUrl()?>'">回首页</a>
                <a class="btn new-btn-lg new-btn-primary" onclick="javascript:history.back(-1);">返回上一页</a>
            </div>
        </div>
    </div>
</div>
<!-- END Error Container -->
</body>
</html>
