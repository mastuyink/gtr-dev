<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use frontend\assets\AppAsset;
use common\widgets\Alert;

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
        'brandLabel' => 'GiliTransfers',
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar material-navbar material-navbar_primary navbar-fixed-top',
        ],
    ]);
    $menuItems = [
        
        [
            'label' => 'Fast Boat',
            'url' => ['/content/fastboats'],
            'options'=>['class'=>'lis'],
            'linkOptions'=>['class'=>'material-navbar__link'],
        ],
        [
        'label' => 'Destination', 'url' => ['/content/destinations'],
        'options'=>['class'=>'lis'],
        'linkOptions'=>['class'=>'material-navbar__link'],
        ],
        [
        'label' => 'Ports', 'url' => ['/content/ports'],
        'options'=>['class'=>'lis'],
        'linkOptions'=>['class'=>'material-navbar__link'],
        ],
        [
        'label' => 'Article', 'url' => ['/content/articles'],
        'options'=>['class'=>'lis'],
        'linkOptions'=>['class'=>'material-navbar__link'],
        ],
        [
        'label' => 'About', 'url' => ['/site/about'],
        'options'=>['class'=>'lis'],
        'linkOptions'=>['class'=>'material-navbar__link'],
        ],
        [
        'label' => 'Contact', 'url' => ['/site/contact'],
        'options'=>['class'=>'lis'],
        'linkOptions'=>['class'=>'material-navbar__link'],
        ],
        [
        'label' => 'Cart ('.Yii::$app->gilitransfers->Countcart().')', 'url' => ['/book/detail-data'],
        'options'=>['class'=>'lis'],
        'linkOptions'=>['class'=>'material-navbar__link'],
        ],
        
    ];
    if (Yii::$app->user->isGuest) {
      //  $menuItems[] = ['label' => 'Signup', 'url' => ['/site/signup']];
      //  $menuItems[] = ['label' => 'Login', 'url' => ['/site/login']];
    } else {
        $menuItems[] = '<li>'
            . Html::beginForm(['/site/logout'], 'post')
            . Html::submitButton(
                'Logout (' . Yii::$app->user->identity->username . ')',
                ['class' => 'btn btn-link logout']
            )
            . Html::endForm()
            . '</li>';
    }
    echo Nav::widget([
        'options' => ['class' => 'nav navbar-nav navbar-right material-navbar__nav'],
        'items' => $menuItems,
    ]);
    NavBar::end();
    ?>

    
 

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        
        <?= $content ?>
<?= Html::button(' Top <span></span>', [
  'class'=>'btn',
  'id'=>'btn-scroll',
  'style'=>'display:none;'
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
    background-color:#3498db;
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
SCRIPT;
$this->registerCss($customCss);

?>

<?= $this->render('_footer'); ?>

<!--<footer class="footer">
    <div class="container">
        

       <div class="row">
            <?= Html::a('Home', Yii::$app->homeUrl, ['class' => 'footer-link']); ?> 
            <?= Html::a('About', '/site/about', ['class' => 'footer-link']); ?>
             <center>&copy; Gilitransfers.com <?= date('Y') ?></center> 
      <span class="pull-right "> We Accepted : <?= Html::img('/img/paypal.png', ['class' => 'img-responsive','width'=>'200px','height'=>'auto']); ?></span>
       
       </div>

    </div>
</footer>-->

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
