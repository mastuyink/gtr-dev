<?php
use yii\helpers\Html;
use mdm\admin\components\Helper;
use yii\helpers\Url;
/* @var $this \yii\web\View */
/* @var $content string */
$Valbook = Yii::$app->gilitransfers->Valbook();

// $this->registerJs("

// if (Notification.permission !== \"granted\")
//     Notification.requestPermission();
//     hitungMundur(); 

// function hitungMundur(){
//     setTimeout(function(){
//         //cekNotif();
//         }, 5000);
// }

// function cekNotif(){
//     $.ajax({
//         url:'".Url::to(['/site/index'])."',
//         type: 'POST',
//         async: true,
//         success:function(data){
//             if (data == 'kosong') {
//                 alert('kosong');
//                 hitungMundur();
//             }else{
//                 notifikasi(data);
//             }
            
//         },
//         error: function(){
//             console.log('Notification Error');
//             hitungMundur();
//         },
//     });
// };
                
// function notifikasi(data) {
//     if (!Notification) {
//         alert('Browsermu tidak mendukung Web Notification. Silahkan Upgrade Browser yg mendukung seperti Mozila Versi Terbaru'); 
//         return;
//     }
//     if (Notification.permission !== \"granted\")
//         Notification.requestPermission();
//     else {
//         var audio = new Audio('/audio/notif-sound.ogg');
//         audio.play();
//         var notifikasi = new Notification('Pesanan Baru', {
//             icon: '/logo.png',
//             body: \"Silahkan Periksa Dengan Klik Notifikasi Ini\",
//        });
//         notifikasi.onclick = function () {
//             window.location.href = '".Url::to(['/booking/index'])."';   
//         };
//         setTimeout(function(){
//             notifikasi.close();
//             hitungMundur();
//         }, 5000);
//     }
// };

//     ", \yii\web\View::POS_READY);
?>
<header class="main-header">

    <?= Html::a('<span class="logo-mini">GTR</span><span class="logo-lg">GILITRANSFERS</span>', Yii::$app->homeUrl, ['class' => 'logo']) ?>

    <nav class="navbar navbar-static-top" role="navigation">

        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>

        <div class="navbar-custom-menu">

            <ul class="nav navbar-nav">
            <?php if(Helper::checkRoute('/booking/*')): ?>
                <li class="dropdown notifications-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-bell-o"></i>
                        <span class="label label-danger"><?= $Valbook ?></span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header"><?= $Valbook ?> Booking Need Validation</li>
                        <li>
                            <!-- inner menu: contains the actual data 
                            <ul class="menu">
                                <li>
                                    <a href="#">
                                        <i class="fa fa-users text-aqua"></i> 5 new members joined today
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i class="fa fa-warning text-yellow"></i> Very long description here that may
                                        not fit into the page and may cause design problems
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i class="fa fa-users text-red"></i> 5 new members joined
                                    </a>
                                </li>

                                <li>
                                    <a href="#">
                                        <i class="fa fa-shopping-cart text-green"></i> 25 sales made
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i class="fa fa-user text-red"></i> You changed your username
                                    </a>
                                </li>
                            </ul> -->
                        </li>
                        <li class="footer"><a href="/booking/validation">View all</a></li>
                    </ul>
                </li>
               <?php endif; ?>
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="<?= $directoryAsset ?>/img/user2-160x160.jpg" class="user-image" alt="User Image"/>
                        <span class="hidden-xs"><?= Yii::$app->user->identity->username ?></span>
                    </a>
                    <ul class="dropdown-menu">
                        <!-- User image -->
                        <li class="user-header">
                            <img src="<?= $directoryAsset ?>/img/user2-160x160.jpg" class="img-circle"
                                 alt="User Image"/>

                            <p>
                                <?= Yii::$app->user->identity->username ?>
                                <small></small>
                            </p>
                        </li>
                        <!-- Menu Body -->
                        <li class="user-body">
                            <div class="col-xs-4 text-center">
                                <a href="#">Followers</a>
                            </div>
                            <div class="col-xs-4 text-center">
                                <a href="#">Sales</a>
                            </div>
                            <div class="col-xs-4 text-center">
                                <a href="#">Friends</a>
                            </div>
                        </li>
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="#" class="btn btn-default btn-flat">Profile</a>
                            </div>
                            <div class="pull-right">
                                <?= Html::a(
                                    'Sign out',
                                    ['/site/logout'],
                                    ['data-method' => 'post', 'class' => 'btn btn-default btn-flat']
                                ) ?>
                            </div>
                        </li>
                    </ul>
                </li>

                <!-- User Account: style can be found in dropdown.less 
                <li>
                    <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
                </li>-->
            </ul>
        </div>
    </nav>
</header>
