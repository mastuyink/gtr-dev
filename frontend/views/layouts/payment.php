<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use frontend\assets\AppAsset;
use common\widgets\Alert;
use yii\helpers\Url;
use rmrevin\yii\fontawesome\AssetBundle;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
  NavBar::begin([
        'brandLabel' => '<img style="height: 25px; width: auto; margin-top:25px;" alt="logo-navbar" src="/img/logo.png">',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar material-navbar material-navbar_primary navbar-fixed-top',
        ],
    ]);
    $menuItems = [
        [
        'label' => 'Home',
        'url' => Yii::$app->homeUrl,
        'linkOptions'=>['class'=>'material-navbar__link'],
        ],
        [
        'label' => 'Fast Boat',
        'url' => ['/content/fastboats'],
        'linkOptions'=>['class'=>'material-navbar__link'],
        ],
        [
        'label' => 'Destination', 'url' => ['/content/destination'],
        'linkOptions'=>['class'=>'material-navbar__link'],
        ],
        [
        'label' => 'Ports', 'url' => ['/content/ports'],
        'linkOptions'=>['class'=>'material-navbar__link'],
        ],
        [
        'label' => 'Hotels', 'url' => ['/content/hotels'],
        'linkOptions'=>['class'=>'material-navbar__link'],
        ],
        [
        'label' => 'Article', 'url' => ['/content/articles'],
        'linkOptions'=>['class'=>'material-navbar__link'],
        ],
        [
        'label' => 'Contact', 'url' => ['/site/contact'],
        'linkOptions'=>['class'=>'material-navbar__link'],
        ],
        
    ];
   
    echo Nav::widget([
        'options' => ['class' => 'nav navbar-nav navbar-right material-navbar__nav'],
        'items' => $menuItems,
    ]);
    NavBar::end();
    ?>

<div class="container-fluid">&nbsp</div>
    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        
        <?= $content ?>
<?= Html::button(' Top <span></span>', [
  'class'=>'btn',
  'id'=>'btn-scroll',
  'style'=>'display:none;',
  'data-toggle'=>'tooltip',
  'title'=>'Back To Top',
  ]); ?>
    </div>
</div>
<?php $customScript = <<< SCRIPT
  $(window).scroll(function(){ 
        if ($(this).scrollTop() > 100) { 
            $('#btn-scroll').fadeIn(); 
        } else { 
            $('#btn-scroll').fadeOut(); 
        } 
    }); 
    $('#btn-scroll').click(function(){ 
        $("html, body").animate({ scrollTop: 0 }, 600); 
        return false; 
    });

SCRIPT;
$this->registerJs($customScript, \yii\web\View::POS_READY); 

$customCss = <<< SCRIPT
#btn-scroll {
    position:fixed;
    right:10px;
    bottom:100px;
    cursor:pointer;
    width:50px;
    height:50px;
    background-color:#f2a12e;
    text-indent:-9999px;
    display:none;
    -webkit-border-radius:60px;
    -moz-border-radius:60px;
    border-radius:60px
}
#btn-scroll span {
    position:absolute;
    top:50%;
    left:50%;
    margin-left:-8px;
    margin-top:-12px;
    height:0;
    width:0;
    border:8px solid transparent;
    border-bottom-color:#ffffff;
}
#btn-scroll:hover {
    background-color:#e74c3c;
    opacity:1;filter:"alpha(opacity=100)";
    -ms-filter:"alpha(opacity=100)";
}
 .navbar {
  min-height: 80px;
}

.navbar-brand {
  padding: 0 15px;
  height: 80px;
  line-height: 80px;
}

.navbar-toggle {
  /* (80px - button height 34px) / 2 = 23px */
  margin-top: 23px;
  padding: 9px 10px !important;
}

@media (min-width: 768px) {
  .navbar-nav > li > a {
    /* (80px - line-height of 27px) / 2 = 26.5px */
    padding-top: 26.5px;
    padding-bottom: 26.5px;
    line-height: 27px;
  }
}
SCRIPT;
$this->registerCss($customCss);
//$test = Yii::$app->gilitransfers->trackFrontendVisitor();
?>
<?= $this->render('_footer'); ?>


<?php $this->endBody() ?>
<?php
$this->registerJs('
     var vurl = "'.Yii::$app->request->url.'";
     $.ajax({
                url:"'.Url::to(["/site/tracking"]).'",
                type: "POST",
                async: true,
                data:{url: vurl},
              });
    ');
 ?>
</body>
</html>
<?php $this->endPage() ?>
