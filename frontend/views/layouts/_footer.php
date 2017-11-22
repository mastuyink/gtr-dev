<?php 
use yii\helpers\Html;

?>
<link href="https://fortawesome.github.io/Font-Awesome/assets/font-awesome/css/font-awesome.css" rel="stylesheet">
<!--footer start from here-->
<footer>
  <div class="container">
    <div class="row">
      <div class="col-md-3 col-sm-6 footer-col">
        <div class="logofooter"><?=Html::a(Html::img('/img/logo.png', ['style'=>'height: 40px; width: auto;','alt' => 'footer-logo']), Yii::$app->homeUrl, ['option' => 'value']); ?>
</div>
        <p>Transfer from Bali to Gili Trawangan, Gili Air, Gili Meno, and Lombok by Fast Boat or Flight. Easy online booking. Pay in your currency and save money.</p>
        <p><i class="fa fa-map-pin"></i> Perum Bukit pratama Jl Goa Gong Gg.Gong Suling 2 No.10 Jimbaran. Kab.Badung Kuta Selatan Bali - Indonesia.</p>
        <p><i class="fa fa-phone"></i> Phone (Indonesia) : +62-813-5330-4990</p>
        <p><i class="fa fa-envelope"></i> E-mail :reservation@gilitransfers.com</p>
        
      </div>
      <div class="col-md-3 col-sm-6 footer-col">
        <h6 class="heading7">RELATED LINKS</h6>
        <ul class="footer-ul">
          <li><?= Html::a('Home', Yii::$app->homeUrl); ?></li>
          <li><?= Html::a('Contact Us', ['/site/contact']); ?></li>
          <li><?= Html::a('Terms & Conditions', ['/content/terms-conditions']); ?></li>
          <li><?= Html::a('Privacy Policy', ['/content/privacy-policy']); ?></li>
          <li><?= Html::a('FAQ', ['/site/faq']); ?></li>
          <li><?= Html::a('How To Book', ['/site/how-to-book']); ?></li>
          <li><?= Html::a('About Us', ['/site/about']); ?></li>
        </ul>
      </div>
      <div class="col-md-3 col-sm-6 footer-col">
        <h6 class="heading7">LATEST POST</h6>
        <div class="post footer-ul">
          <?= Yii::$app->gilitransfers->LatestPost() ?>
         
        </div>
      </div>
      <div class="col-md-3 col-sm-6 footer-col">
        <h6 class="heading7">Follow Us</h6>
        <ul class="footer-social">
          <!--<li><i class="fa fa-linkedin social-icon linked-in" aria-hidden="true"></i></li>-->
          <li><a target="_blank" href="https://www.facebook.com/Gilitransferscom/"><i class="fa fa-facebook social-icon facebook" aria-hidden="true"></i></a></li>
          <li><a target="_blank" href="https://www.youtube.com/channel/UC_NtnHWVkbECkOqAB-2tWcA"><i class="fa fa-youtube social-icon youtube" aria-hidden="true"></i></a></li>
          <li><a target="_blank" href="https://plus.google.com/114019474249953149637"><i class="fa fa-google-plus social-icon google" aria-hidden="true"></i></a></li>
        </ul>
      </div>
       <div class="col-md-3 col-sm-6 footer-col">
        <h6 class="heading7">Payment Channel</h6>
        <ul class="footer-social">
          <li><img style="height: 100px; width: auto;"  src="/img/paypal.png"></div></li>
         
        </ul>
      </div>
    </div>
  </div>
</footer>
<!--footer start from here-->

<div class="copyright">
  <div class="container">
    <div class="col-md-6">
      <p>© <?= date('Y') ?> Gilitransfers - All Rights Reserved</p>
    </div>
    <!--
    <div class="col-md-6">
      <ul class="bottom_ul">
        <li><a href="#">prabuuideveloper.com</a></li>
        <li><a href="#">About us</a></li>
        <li><a href="#">Blog</a></li>
        <li><a href="#">Faq's</a></li>
        <li><a href="#">Contact us</a></li>
        <li><a href="#">Site Map</a></li>
      </ul>
    </div>-->
  </div>
</div>


<?php 
$customCss = <<< SCRIPT
  @import url(https://fonts.googleapis.com/css?family=Open+Sans:400,700,300);
ul,li{
    padding:0;
    margin:0;
}
li{
    list-style-type:none;
}



footer { background-color:#0c1a1e; min-height:350px; font-family: 'Open Sans', sans-serif; }
.footer-col { margin-top:50px; }
.logofooter { margin-bottom:10px; font-size:25px; color:#fff; font-weight:700;}

.footer-col p { color:#fff; font-size:12px; font-family: 'Open Sans', sans-serif; margin-bottom:15px;}
.footer-col p i { width:20px; color:#999;}

.footer-ul { list-style-type:none;  padding-left:0; margin-left:2px;}
.footer-ul li { line-height:29px; font-size:12px;}
.footer-ul li a { color:#a0a3a4; transition: color 0.2s linear 0s, background 0.2s linear 0s; }
.footer-ul i { margin-right:10px;}
.footer-ul li a:hover {transition: color 0.2s linear 0s, background 0.2s linear 0s; color:#ff670f; }

 .copyright { min-height:40px; background-color:#000000;}
 .copyright p { text-align:left; color:#FFF; padding:10px 0; margin-bottom:0;}
 .heading7 { font-size:21px; font-weight:700; color:#d9d6d6; margin-bottom:22px;}
 .post p { font-size:12px; color:#FFF; line-height:20px;}
 .post p span { display:block; color:#8f8f8f;}
 .bottom_ul { list-style-type:none; float:right; margin-bottom:0;}
 .bottom_ul li { float:left; line-height:40px;}
 .bottom_ul li:after { content:"/"; color:#FFF; margin-right:8px; margin-left:8px;}
 .bottom_ul li a { color:#FFF;  font-size:12px;}
.social-icon {
    width: 30px;
    height: 30px;
    font-size: 15px;
    background-color: blue;
    color: #fff;
    text-align: center;
    margin-right: 10px;
    padding-top: 7px;
    border-radius: 50%;
}
 .footer-social li{
     float:left;
 }
 .linked-in{
     background-color:#007bb6;
 }
 .facebook{
     background-color:#3b5998;
 }
 .twitter{
     background-color:#1da1f2;
 }
 .google{
     background-color:#f63e28;
 }
SCRIPT;
$this->registerCss($customCss);
?>