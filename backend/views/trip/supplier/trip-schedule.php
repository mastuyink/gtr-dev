<?php

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use kartik\switchinput\SwitchInput;
use kartik\widgets\TouchSpin;
use common\models\TTrip;
/* @var $this yii\web\View */
/* @var $searchModel common\models\TTripSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title =  'Trip List';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php  
$varmonth = $monthYear;
$month= date('m',strtotime($monthYear));
$year=date('Y',strtotime($monthYear));
$day=date("d");
$endDate=date("t",mktime(0,0,0,$month,$day,$year));

?>
<?= $this->render('_search',[
    'listBulan'=>$listBulan,
    'listTahun'=>$listTahun,
    'varmonth'=>$varmonth,
]) ?>   
<?php
$this->registerJs("
$(function(){
    $('[data-toggle=popover]').popover({
        html : true,
        content: function() {
          var content = $(this).attr('data-popover-content');
          return $(content).children('.popover-body').html();
        },
        container:'.table',
        title: function() {
          var title = $(this).attr('data-popover-content');
          return $(title).children('.popover-heading').html();
        }
    });
  });

$('.trip-text').mouseenter(function(){
  var bg = $(this).css(\"background-color\");
  $(this).css(\"background-color\", \"yellow\");
  $('.trip-text').mouseleave(function(){
  $(this).css(\"background-color\", bg);
  
});
});
    ");
 ?>
  <?php 

// $this->registerJs('
//   var vcom   = '.$session['filter']['company'].';
//   var vroute = '.$session['filter']['islandRoute'].';
//   var vtime  = "'.$session['filter']['time'].'";
//   $("#header-trip-schedule").html("<center>Please Wait...<br><img height=\'50px\' src=\'/spinner.svg\'></center>");
// $.ajax({
//       url:"'.Url::to(["header-trip-schedule"]).'",
//       type: "POST",
//       data:{
//           company: vcom,
//           route: vroute,
//           time: vtime,
//         },
//       success:function(data){
//         $("#header-trip-schedule").html(data);
//       },
//       error:function(data){
//         $("#header-trip-schedule").html("<center>Something Its Wrong...<br>Please Try Again</center>");
//       },
//     });
//   ', \yii\web\View::POS_READY);
 ?>
 <div class="row">
   <div id="header-trip-schedule" class="col-md-12">
     
   </div>
 </div>   
<?php 
echo "<div class='col-md-12' id='judul-table'><h2 align='center'>".Html::encode(date('F',strtotime($monthYear)))." ".Html::encode(date('Y',strtotime($monthYear)))."</h2>";
echo '<span class="pull-left"><div class="main-container__column material-checkbox-group material-checkbox-group_primary">
                          '.Html::checkbox('checkbox-all-trip', $checked = false, [
                            'id' => 'checkbox-table',
                            'class'=>'material-checkbox',
                            'onchange'=>'
                            if ($(this).is(":checked")) {
                                $(".checkbox-trip").prop("checked", true);
                            }else{
                                $(".checkbox-trip").prop("checked", false);
                            }
                              ',
                            ]).'<label class="material-checkbox-group__label" for="checkbox-table">Select All</label>
                          </div></span></div>';

echo '<table id="table-trip" align="center" class="table table-striped table-responsive">
<thead>
  <tr class="info">
  <td align=center><font color=red>Minggu</font></td>
  <td align=center>Senin</td>
  <td align=center>Selasa</td>
  <td align=center>Rabu</td>
  <td align=center>Kamis</td>
  <td align=center>Jumat</td>
  <td align=center>Sabtu</td>
  </tr>
</thead>
  <tbody>';
//cek tanggal 1 hari sekarang
$s=date ("w", mktime (0,0,0,$month,1,$year));
for ($ds=1;$ds<=$s;$ds++) {
echo "<td style=\"font-family:arial;color:#B3D9FF\" align=center valign=middle >
</td>";
}
for ($d=1;$d<=$endDate;$d++) {
    if (date("w",mktime (0,0,0,$month,$d,$year)) == 0) {
        echo "<tr class='baris'>"; 
    }
    if (date("d",mktime (0,0,0,$month,$d,$year)) == "Sun") {  }
      $today = date("Y-m-d",mktime (0,0,0,$month,$d,$year));

            $trips = TTrip::find()->joinWith(['idBoat.idCompany','idRoute.departureHarbor departure','idRoute.arrivalHarbor as arrival'])->select(['t_trip.*','CONCAT( departure.id_island, "-", arrival.id_island) as islandRoute'])->where(['t_company.id_user'=>Yii::$app->user->identity->id,'id_company'=>$session['filter']['company'],'dept_time'=>$session['filter']['time'],'date'=>$today,'CONCAT( departure.id_island, "-", arrival.id_island)'=>$session['filter']['islandRoute']])->groupBy(['islandRoute'])->orderBy(['dept_time'=>SORT_ASC])->all();

      
    //tanggal 
    echo "<td style=\"font-family:arial;color:#333333\" align=center valign=middle> <span><li style='list-style: none; background-color: #ccc;'>".date("d",mktime (0,0,0,$month,$d,$year));

     
    //trip list
    // echo Html::checkbox('checkbox-multi-'.$today, $checked = false, [
    //       'class' => 'pull-right',
    //       'onchange'=>'
    //         if ($(this).is(":checked")) {
    //             $(".checkbox-'.$today.'").prop("checked", true);
    //         }else{
    //             $(".checkbox-'.$today.'").prop("checked", false);
    //         }
    //           ',
            
    //       ])." ";
    echo Html::a('', ['add-dayli','date'=>$today], ['class' => ' text-danger btn btn-xs glyphicon glyphicon-plus pull-left'])."</li><br><br>"; 
    
    if (!empty($trips)) {
        foreach ($trips as $key => $value) {
          if ($value->id_season == null) {
            echo Html::a(date('H:i',strtotime($value->dept_time))." ".substr($value->idBoat->idCompany->name, 0,5)."... (".$value->stock.")", null, ['class' =>'trip-text pull-left text-warning append text-info tip','data-toggle'=>'popover', 'data-trigger'=>'hover focus', 'data-popover-content'=>'#'.$value->id,'data-placement'=>'bottom']);

          }else{
            if ($value->status == 1) {
              $warna_text = "trip-text pull-left text-success append text-info tip";
            }elseif ($value->status == 2) {
              $warna_text = "trip-text pull-left text-danger append text-info tip";
            }else{
              $warna_text = "trip-text pull-left bg-danger text-danger append tip";
            }
           echo Html::a(date('H:i',strtotime($value->dept_time))." ".substr($value->idBoat->idCompany->name, 0,5)."... (".$value->stock.")",
           null, ['class' => $warna_text,'data-toggle'=>'popover', 'data-trigger'=>'hover focus', 'data-popover-content'=>'#'.$value->id,'data-placement'=>'bottom']);

          }
        //checkbox per trip
        echo Html::checkbox('checkbox-'.$value->id, $checked = false, ['class' => 'pull-right checkbox-trip checkbox-'.$today,'dept-time'=>$value->dept_time,'date'=>$value->date,'island-route'=>$value->islandRoute,'id'=>'checkbox-'.$value->id])."<br>";

        // Popover Start
        echo "<div id='".$value->id."' class='hidden panel panel-primary'>
          <div class='col-lg-12 popover-heading panel bg-primary'><center><strong>".$value->idBoat->idCompany->name."</strong><div class='pull-right'>".
          // Html::a('', ['update','id'=>$value->id], ['class'=>'btn btn-xs btn-primary glyphicon glyphicon-pencil'])." ".
          // Html::a('', ['delete', 'id' => $value->id], [
          //   'class' => 'btn btn-xs btn-danger glyphicon glyphicon-trash',
          //   'data' => [
          //       'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
          //       'method' => 'post',
          //   ],
          // ]).
          "</div></center></div>
          <div class='popover-body list-group col-lg-12' >
          <div class='col-sm-3' style='font-weight:bold;'>Date</div><div class='col-sm-9'>".date('d-m-Y',strtotime($value->date))."</div>
          <div class='col-sm-3' style='font-weight:bold;'>Boat</div><div class='col-sm-9'>".$value->idBoat->name."</div>
          <div class='col-sm-3' style='font-weight:bold;'>Route</div><div class='col-sm-9'>".$value->idRoute->departureHarbor->idIsland->island." -> ".$value->idRoute->arrivalHarbor->idIsland->island."</div>
          <div class='col-sm-3'style='font-weight:bold;'>Avaibile</div><div class='col-sm-9'>".$value->stock."</div>";
          if ($value->status == 1) {
            echo "<div class='col-sm-3'style='font-weight:bold;'>Status</div><div class='col-sm-9 text-success'>".$value->status0->status."</div>";
          }else{
            echo "<div class='col-sm-3'style='font-weight:bold;'>Status</div><div class='col-sm-9  text-danger'>".$value->status0->status."</div>";
          }
           
          echo "<div class='col-sm-3' style='font-weight:bold;'>Type</div>";
          if ($value->id_price_type != null) {
            echo "<div class='col-sm-9' style='font-weight:bold;'>".$value->idPriceType->type." price</div>";
          }else{
            echo "<div class='col-sm-9 text-danger' style='font-weight:bold;'>Unset price</div>";
          }
          

          

          if ($value->id_season == null) {
            echo "<div class='col-sm-12 bg-danger text-danger'style='font-weight:bold;'>Unset Session</div><br>&nbsp
          ";
          }else{
            echo "<div class='col-sm-12 bg-info'style='font-weight:bold;'>Season</div>
                 <div class='col-sm-3'>Type</div><div class='col-sm-9'>".$value->idSeason->idSeasonType->season."</div>
                  <div class='col-sm-3'>Start</div><div class='col-sm-9'>".date('d-m-Y',strtotime($value->idSeason->start_date))."</div>
                  <div class='col-sm-3'>End</div><div class='col-sm-9'>".date('d-m-Y',strtotime($value->idSeason->end_date))."</div>
                  ";
          }
          echo $value->description."</div> </div>";
          //popover end
      }
    }else{
      echo "<span class='text-danger'>Not Avaible</span>";
    }
    
    echo "</span></td>";

    //jika variabel w= 6 disini 6 adalah hari sabtu maka akan pindah baris dengan menutup baris </tr>
    if (date("w",mktime (0,0,0,$month,$d,$year)) == 6) { echo "</tr>"; }
}
echo '</table></tbody>';
?>

