<?php

namespace backend\controllers;

use Yii;
use common\models\TBooking;
use common\models\TTrip;
use app\models\TTripSearch;
use common\models\TBoat;
use common\models\TRoute;
use common\models\TCompany;
use common\models\TAvaibility;
use common\models\TAvaibilityTemplate;
use common\models\TSeasonPrice;
use common\models\TEstTime;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use mdm\admin\components\Helper;
/**
 * TripController implements the CRUD actions for TTrip model.
 */
class TripController extends Controller
{
   // public $_userid = Yii::$app->user->identity->id;
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    public function beforeAction($action)
    {    
        if ($action->id == 'topup-by-email') {

            $this->enableCsrfValidation = false;
        }    
        return true;
    }

    public function actionTopupSuccess(){
        $this->layout = 'main-login';
        return $this->render('topup-successfull');
    }
    public function actionTopupFailed(){
        $this->layout = 'main-login';
        return $this->render('topup-failed');
    }
    public function actionTopupByEmail($token,$date,$dept_time,$island_route,$value){
        if (Yii::$app->request->isGet) {
            $this->layout = 'main-login';
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $userId = $this->findUserByMAskToken($token);
                $modelTrips = TTrip::find()->joinWith(['idBoat.idCompany','idRoute.departureHarbor departure','idRoute.arrivalHarbor as arrival'])->where(['t_company.id_user'=>$userId,'dept_time'=>$dept_time,'date'=>$date,'CONCAT( departure.id_island, "-", arrival.id_island)'=>$island_route])->all();
                foreach ($modelTrips as $key => $modelTrip) {
                    $modelTrip->stock = $modelTrip->stock+$value;
                    $modelTrip->validate();
                    $modelTrip->save(false);
                }
                $transaction->commit();
                return $this->redirect(['topup-success']);
            } catch(\Exception $e) {
                $transaction->rollBack();
                return $this->redirect(['topup-failed']);
            }
            
        }else{
            return $this->goHome();
        }
    }

    protected function findUserByMAskToken($maskToken){
        $unmaskToken = Yii::$app->getSecurity()->unmaskToken($maskToken);
        if (($modelUSer = \backend\models\User::find()->where(['auth_key'=>$unmaskToken])->asArray()->one()) !== null) {
            return $modelUSer['id'];
        }else{
            return $this->goHome();
        }
    }

    public function actionGetAvaibleRoute(){
       // if (Yii::$app->request->isPost) {
            $Trip = TTrip::find()->joinWith(['idBoat.idCompany','idRoute'])->where(['t_company.id_user'=>Yii::$app->user->identity->id])->groupBy('id_route')->all();
            foreach ($Trip as $key => $value) {
                $list[$key] = ['id'=>$value->idRoute->id,'route'=>$value->idRoute->departureHarbor->name." to ".$value->idRoute->arrivalHarbor->name,'island'=>$value->idRoute->departureHarbor->idIsland->island." -> ".$value->idRoute->arrivalHarbor->idIsland->island];
            }
        return $list; 
        //}
    }

    public function actionGetAvaibleTime(){
        $ListTime = TTrip::find()->joinWith(['idBoat.idCompany'])->select('concat(dept_time) as dept_time,id_boat')->where(['t_company.id_user'=>Yii::$app->user->identity->id])->asArray()->orderBy(['dept_time'=>SORT_ASC])->all();
        return $ListTime;
    }
    public function actionHeaderTripSchedule(){
        if (Yii::$app->request->isAjax) {
            $data = Yii::$app->request->post();
            $company = $this->findCompany()->where(['id'=>$data['company']])->asArray()->one();
            $route = TRoute::find()->where(['id'=>$data['route']])->one();
            echo $this->renderAjax('_header-trip-schedule',['company'=>$company,'route'=>$route,'time'=>$data['time']]);
        }else{
            return $this->goHome();
        }
    }

    public function actionGetRoute(){
        $Route = $this->findRoute();
        foreach ($Route as $key => $value) {
           $list[$key] = ['id'=>$value->id,'route'=>$value->departureHarbor->name."->".$value->arrivalHarbor->name,'island'=>$value->departureHarbor->idIsland->island." -> ".$value->arrivalHarbor->idIsland->island];
        }
        return $list; 
    }

    public function actionSummaryTrip(){
        if (Yii::$app->request->isAjax) {

            $ListTrip = TTrip::find()->select('*,MIN(date) AS minDate,MAX(date) maxDate')->groupBy('id_boat,id_route,dept_time')->asArray()->orderBy(['id_route'=>SORT_ASC,'dept_time'=>SORT_ASC])->all();

                echo $this->renderAjax('summary-trip',['ListTrip'=>$ListTrip]);
        }else{
            return $this->goHome();
        }
    }

    public function actionUpdateMultiple(){
        if (Yii::$app->request->isAjax) {
            $data = Yii::$app->request->post();
            // Update By Form Start
         if (!isset($data['idtrip'])) {
                $TripList = $this->findTrip()->where(['t_boat.id_company'=>$data['company']])->andWhere(['id_route'=>$data['route']])->andWhere(['dept_time'=>$data['dtime']])->andWhere(['between','date',$data['start'],$data['end']])->all();

            
                if ($TripList != null) {
                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        foreach ($TripList as $x => $value) {
                            if ($data['dept'] != null) {
                                $value->dept_time = $data['dept'];
                            }
                            if ($data['est'] != null) {
                                $value->id_est_time = $data['est'];
                            }
                            if ($data['stock'] != null && isset($data['type'])) {
                                if ($data['type'] == '1') {
                                    $value->stock = $value->stock+$data['stock'];
                                }elseif($data['type'] == '2'){
                                    $value->stock = $value->stock-$data['stock'];
                                }   
                            }
                            if ($data['sts'] != null) {
                                $value->status = $data['sts'];
                            }
                            if ($data['adult'] != null) {
                                $adult_price =  preg_replace('/\D/','',$data['adult']);
                                $value->adult_price = $adult_price;
                                $value->id_price_type = '2';
                            }
                            if ($data['child'] != null) {
                                $child_price =  preg_replace('/\D/','',$data['child']);
                                $value->child_price = $child_price;
                                $value->id_price_type = '2';
                            }
                            if ($data['desc'] != null) {
                                $value->description = $data['desc'];
                            }
                            $value->save(false);
                        }
                        $transaction->commit();
                    } catch(\Exception $e) {
                        $transaction->rollBack();
                        throw $e;
                    }
                    
                }else{
                    return true;
                }
            // UPdate By Form  End
            // UPdate By Checkbox Start
            }else{
                 $transaction = Yii::$app->db->beginTransaction();
                    try {
                        $TripList = $data['idtrip'];
                        foreach ($TripList as $x => $val) {
                            $value = $this->findModel($val);
                            if ($data['dept'] != null) {
                                $value->dept_time = $data['dept'];
                            }
                            if ($data['est'] != null) {
                                $value->id_est_time = $data['est'];
                            }
                            if ($data['stock'] != null && isset($data['type'])) {
                                if ($data['type'] == '1') {
                                    $value->stock = $value->stock+$data['stock'];
                                }elseif($data['type'] == '2'){
                                    $value->stock = $value->stock-$data['stock'];
                                }   
                            }
                            if ($data['sts'] != null) {
                                $value->status = $data['sts'];
                            }
                            if ($data['adult'] != null) {
                                $adult_price =  preg_replace('/\D/','',$data['adult']);
                                $value->adult_price = $adult_price;
                                $value->id_price_type = '2';
                            }
                            if ($data['child'] != null) {
                                $child_price =  preg_replace('/\D/','',$data['child']);
                                $value->child_price = $child_price;
                                $value->id_price_type = '2';
                            }

                            if ($data['desc'] != null) {
                                $value->description = $data['desc'];
                            }
                            $value->save(false);
                        }
                        $transaction->commit();
                    } catch(\Exception $e) {
                        $transaction->rollBack();
                        throw $e;
                    }
            }
        // Update By Checkbox End
         }else{
            return $this->goBack();
         }
    }


    protected function findBoat($type = null){
        return TBoat::find();
    }
    protected function findCompany($type = null){
        if ($type == null) {
           return TCompany::find();
        }else{
            return TCompany::find()->where(['id_user'=>Yii::$app->user->identity->id]);
        }
        
    }
    protected function findRoute(){
        return TRoute::find()->all();
    }

    public function actionBoatList(){
        if (Yii::$app->request->isAjax) { 
            $data = Yii::$app->request->post();
            $idCom = $data['cpn'];
            //$boot = $this->findBoat()->where(['id_company'=>$idCom])->one();
            $listBoat = $this->findBoat()->where(['id_company'=>$idCom])->all();
            if (!empty($listBoat)) {
                echo "<option value=''>- > Select Boat <-</option>";
                foreach ($listBoat as $key => $value) {
                echo "<option value='".$value->id."'>".$value->name."</option>";
                }
            }else{
                echo "<option value=''>-> Company Don't Have Boat <-</option>";
            }
            

        }
    }


    public function actionListTime(){
        $estTime = TEstTime::find()->all();
        echo "<option value=''>Est Time ...</option>";
        foreach ($estTime as $key => $value) {
            echo "<option value='".$value->id."'>".$value->est_time."</option>";
        }
    }
    public function actionMultipleDelete(){
        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();
            if ($data['company'] == null || $data['route'] == null || $data['dtime'] == null || $data['start'] == null || $data['end'] == null) {
                return false;
            }else{
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    $trips = $this->findTrip()->where(['t_boat.id_company'=>$data['company']])->andWhere(['id_route'=>$data['route']])->andWhere(['dept_time'=>$data['dtime']])->andWhere(['between','date',$data['start'],$data['end']])->all();
                    foreach ($trips as $key => $value) {
                        $value->delete();
                    }
                    $transaction->commit();
                } catch(\Exception $e) {
                    $transaction->rollBack();
                    throw $e;
                }
            }
        }else{
            return $this->goBack();
        }
    }
    public function actionDeleteCheckbox(){
        if (Yii::$app->request->isAjax) {
            $data = Yii::$app->request->post();
            $idTrip = $data['id'];
            if($idTrip == null){
                return false;
            }else{
                foreach ($idTrip as $key => $value) {
                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        $Trip         = $this->findModel($value);
                        $Trip->delete();
                        $transaction->commit();
                    } catch(\Exception $e) {
                        $transaction->rollBack();
                        throw $e;
                    }
                
                }
            }
        }
    }
    public function actionChangeStatusByIsland(){
        if (Yii::$app->request->isAjax) {
            $data        = Yii::$app->request->post();
            $deptTime    = $data['dtime'];
            $date        = $data['date']; //is Array
            $islandRoute = $data['iroute'];
            $status      = $data['sts'];

            if(Helper::checkRoute('/booking/validation')){
                foreach ($idTrip as $key => $value) {
                    $Trip         = $this->findModel($value);
                    $Trip->status = $status;
                    $Trip->validate();
                    $Trip->save(false);
                }
            }else{
                foreach ($date as $value) {

                    $modelTrip         = $this->findTripByIsland($value,$deptTime,$islandRoute);
                    $transaction = Yii::$app->db->beginTransaction();
                    try {
                        foreach ($modelTrip as $key => $value) {
                        if ($value->status == '3') {
                        
                        }else{
                        $value->status = $status;
                        $value->validate();
                        $value->save(false);
                        }
                    }
                        $transaction->commit();
                    } catch(\Exception $e) {
                        $transaction->rollBack();
                        throw $e;
                    }
                    
                    
                }
            }
        }
    }

    public function actionUpdateStockByIsland(){
        if (Yii::$app->request->isAjax) {
            $data        = Yii::$app->request->post();
            $deptTime    = $data['dtime'];
            $date        = $data['date']; //is Array
            $islandRoute = $data['iroute'];
            $topvalue    = $data['topup'];
            $type        = $data['type'];
                if ($type == '1') {
                    foreach ($date as $date1) {
                        $modelTrip         = $this->findTripByIsland($date1,$deptTime,$islandRoute);
                        $transaction = Yii::$app->db->beginTransaction();
                        try {
                            foreach ($modelTrip as $key => $val) {
                                $val->stock = $val->stock+$topvalue;
                                $val->validate();
                                $val->save(false);
                            }
                            $transaction->commit();
                        } catch(\Exception $e) {
                            $transaction->rollBack();
                            throw $e;
                        }
                        
                    } 
                }elseif ($type == '2') {
                    foreach ($date as $date2) {
                        $modelTrip         = $this->findTripByIsland($date2,$deptTime,$islandRoute);
                        $transaction = Yii::$app->db->beginTransaction();
                        try {
                            foreach ($modelTrip as $key => $val2) {
                                $val2->stock = $val2->stock-$topvalue;
                                $val2->validate();
                                $val2->save(false);
                            }
                            $transaction->commit();
                        } catch(\Exception $e) {
                            $transaction->rollBack();
                            throw $e;
                        }
                        
                    } 
                }else{
                    return true;
                }   
        }
    }

    protected function findTripByIsland($date, $deptTime, $islandRoute){
        return TTrip::find()->joinWith(['idBoat.idCompany','idRoute.departureHarbor departure','idRoute.arrivalHarbor as arrival'])->where(['t_company.id_user'=>Yii::$app->user->identity->id,'dept_time'=>$deptTime,'date'=>$date,'CONCAT( departure.id_island, "-", arrival.id_island)'=>$islandRoute])->all();
    }


    // public function actionChangeStatus(){
    //     if (Yii::$app->request->isAjax) {
    //         $data = Yii::$app->request->post();
    //         $idTrip = $data['id'];
    //         $status = $data['sts'];
    //         if(Helper::checkRoute('/booking/validation')){
    //             foreach ($idTrip as $key => $value) {
    //             $Trip         = $this->findModel($value);
    //             $Trip->status = $status;
    //             $Trip->validate();
    //             $Trip->save(false);
    //             }
    //         }else{
    //             foreach ($idTrip as $key => $value) {
    //                 $Trip         = $this->findModel($value);
    //                 if ($Trip->status == '3') {
                        
    //                 }else{
    //                 $Trip->status = $status;
    //                 $Trip->validate();
    //                 $Trip->save(false);
    //                 }
    //             }
    //         }
    //     }
    // }

    // public function actionTopup(){
    //     if (Yii::$app->request->isAjax) {
    //         $data = Yii::$app->request->post();
    //         $idTrip = $data['id'];
    //         $topvalue = $data['topup'];
    //         $type = $data['type'];
    //         if ($type == '1') {
    //            foreach ($idTrip as $key => $value) {
    //             $modelTrip         = $this->findModel($value);
    //             $modelTrip->stock = $modelTrip->stock+$topvalue;
    //             $modelTrip->validate();
    //             $modelTrip->save(false);
    //             } 
    //         }elseif ($type == '2') {
    //             foreach ($idTrip as $key => $value) {
    //             $modelTrip         = $this->findModel($value);
    //             $modelTrip->stock = $modelTrip->stock-$topvalue;
    //             $modelTrip->validate();
    //             $modelTrip->save(false);
    //             } 
    //         }else{
    //             return true;
    //         }
            
    //     }
    // }

    public function actionChangePrice(){
        if (Yii::$app->request->isAjax) {
            $data = Yii::$app->request->post();
            $idTrip = $data['id'];
            $adult_price =  preg_replace('/\D/','',$data['adult']);
            $child_price =  preg_replace('/\D/','',$data['child']);
            if ($child_price == null) {
                foreach ($idTrip as $key => $value) {
                    $Trip                = $this->findModel($value);
                    $Trip->adult_price   = $adult_price;
                    $Trip->id_price_type = '2';
                    $Trip->validate();
                    $Trip->save(false);
                }
            }elseif ($adult_price == null) {
                foreach ($idTrip as $key => $value) {
                    $Trip                = $this->findModel($value);
                    $Trip->child_price   = $child_price;
                    $Trip->id_price_type = '2';
                    $Trip->validate();
                    $Trip->save(false);
                }
            }else{
                foreach ($idTrip as $key => $value) {
                    $Trip                = $this->findModel($value);
                    $Trip->adult_price   = $adult_price;
                    $Trip->child_price   = $child_price;
                    $Trip->id_price_type = '2';
                    $Trip->validate();
                    $Trip->save(false);
                }
            }
            return true;
        }else{
            return $this->goBack();
        }
    }

protected function findTrip(){
   // $today = date
    return TTrip::find()->joinWith('idBoat.idCompany');
}

    /**
     * Lists all TTrip models.
     * @return mixed
     */
    public function actionIndex($month = null,$company = null)
    {

       if (Yii::$app->request->isPost) {
        $request = Yii::$app->request;
        $session = Yii::$app->session;
        
        
            $listBulan = ['01'=>'January','02'=>'February','03'=>'March','04'=>'April','05'=>'Mei','06'=>'June','07'=>'July','08'=>'August','09'=>'September','10'=>'October','11'=>'November','12'=>'December'];
            $listTahun = ['2017-'=>'2017','2018-'=>'2018','2019-'=>'2019','2020','2021-'=>'2021'];
            $listCompany = ArrayHelper::map($this->findCompany()->all(), 'id', 'name');
           if ($month == null) {
               $monthYear = date('Y-m-d');
           }else{
            $monthYear = $month.'-01';
          //  $month = '2017-10-01';
           }
          
           if (Helper::checkRoute('/booking/validation')) {
                $model2 = $this->findTrip();
                $Route = $this->findRoute();
                foreach ($Route as $key => $value) {
                    $list[$value->id] = $value->departureHarbor->name."->".$value->arrivalHarbor->name;
                }
                $listRoute = $list;
                if ($request->post('company') != null) {
                    $session['filter']=[
                    'company'=>$request->post('company'),
                    'route'=>$request->post('route'),
                    'time'=>$request->post('time'),
                    ];
                }
                return $this->renderAjax('trip-schedule',[
                    'session'=>$session,
                    'model2'=>$model2,
                    'monthYear'=>$monthYear,
                    'listBulan'=>$listBulan,
                    'listTahun'=>$listTahun,
                    'listCompany'=>$listCompany,
                    'listRoute'=>$listRoute,
                    ]);
            }else{
                if ($request->post('company') != null) {
                    $session['filter']=[
                    'company'     => $request->post('company'),
                    'islandRoute' => $request->post('islandRoute'),
                    'time'        => $request->post('time'),
                    ];
                }
                return $this->renderAjax('supplier/trip-schedule',[
                    'session'=>$session,
                    'monthYear'=>$monthYear,
                    'listBulan'=>$listBulan,
                    'listTahun'=>$listTahun,
                    ]);
            }
       }else{
            $searchModel = new TTripSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
          if (Helper::checkRoute('/booking/validation')) {
                return $this->render('index',[
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                    ]);
            }else{
              return $this->render('supplier/index',[
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                    ]);
        }
            }
    }

    /**
     * Displays a single TTrip model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    protected function findTemplate($id){
        return TAvaibilityTemplate::findOne($id);
    }



    /**
     * Creates a new TTrip model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */

    

    public function actionCreate()
    {
        $model = new TTrip();
        $modelSeasonPrice = new TSeasonPrice();
        $est_time = ArrayHelper::map(TEstTime::find()->all(), 'id', 'est_time');
      //  $listBoat = ArrayHelper::map($this->findBoat()->all(), 'id', 'name');

        $template = ArrayHelper::map(TAvaibilityTemplate::find()->all(), 'id', 'name');
        if (Helper::checkRoute('/booking/validation')) {
          $listCompany = ArrayHelper::map($this->findCompany()->all(), 'id', 'name');
          $listBoat = ArrayHelper::map($this->findBoat()->all(), 'id', 'name');
        }else{
            $listCompany = $this->findCompany('1')->one();
            $listBoat = ArrayHelper::map($this->findBoat()->where(['id_company'=>$listCompany->id])->all(), 'id', 'name');
        }
        
        $Route = $this->findRoute();
        foreach ($Route as $key => $value) {
            $list[$value->id] = $value->departureHarbor->name."->".$value->arrivalHarbor->name;
        }
        $listRoute = $list;

        if ($model->load(Yii::$app->request->post())) {

           // if ($model->date != null && $model->endDate != null) {
                $startDate = $model->date;
                $endDate   = $model->endDate;
                $transaction = Yii::$app->db->beginTransaction();
                try {
                 if ($model->template == null) {
                    while (strtotime($startDate) <= strtotime($endDate)) {
                        $model->saveRangeTrip($model,$startDate,$model->dept_time);
                        $startDate = date ("Y-m-d", strtotime("+1 day", strtotime($startDate)));
                    }
                  }else{
                        $dateAvaible = $this->findTemplate($model->template);
                 
                    while (strtotime($startDate) <= strtotime($endDate)) {
                            
                            $numofdDay = date('w',strtotime($startDate));
                            if ($numofdDay == $dateAvaible->senin) {
                                $model->saveRangeTrip($model,$startDate,$dateAvaible->time_senin);
                                $modelTrip->save(false);
                            }elseif ($numofdDay == $dateAvaible->selasa) {
                                $model->saveRangeTrip($model,$startDate,$dateAvaible->time_selasa);
                                $modelTrip->save(false);
                            }elseif ($numofdDay == $dateAvaible->rabu) {
                                $model->saveRangeTrip($model,$startDate,$dateAvaible->time_rabu);
                                $modelTrip->save(false);
                                
                            }elseif ($numofdDay == $dateAvaible->kamis) {
                                $model->saveRangeTrip($model,$startDate,$dateAvaible->time_kamis);
                                $modelTrip->save(false);
                                
                            }elseif ($numofdDay == $dateAvaible->jumat) {
                                $modelTrip = new TTrip();
                                $model->saveRangeTrip($model,$startDate,$dateAvaible->time_jumat);
                                $modelTrip->save(false);
                                
                            }elseif ($numofdDay == $dateAvaible->sabtu) {
                                $model->saveRangeTrip($model,$startDate,$dateAvaible->time_sabtu);
                                $modelTrip->save(false);
                                
                            }elseif ($numofdDay == $dateAvaible->minggu) {
                                $model->saveRangeTrip($model,$startDate,$dateAvaible->time_minggu);
                                $modelTrip->save(false);
                                
                            }
                        
                        
                            $startDate = date ("Y-m-d", strtotime("+1 day", strtotime($startDate)));
                    }
                 }
                    $transaction->commit();
                    return $this->redirect('index');
                } catch(\Exception $e) {
                    $transaction->rollBack();
                    throw $e;
                }
                
           // }
           // $model->save();
           // return $this->redirect('index');
        } else {
            return $this->render('create', [
                'model' => $model,
                'listCompany'=>$listCompany,
                'listRoute'=>$listRoute,
                'modelSeasonPrice'=>$modelSeasonPrice,
                'template' => $template,
                'est_time' => $est_time,
                'listBoat' => $listBoat,
            ]);
        }
    }

    public function actionAddDayli($date)
    {
        $model = new TTrip();
        $modelAvaibility = new TAvaibility();
       if (Helper::checkRoute('/booking/validation')) {
          $listCompany = ArrayHelper::map($this->findCompany()->all(), 'id', 'name');
          $listBoat = ArrayHelper::map($this->findBoat()->all(), 'id', 'name');
        }else{
            $listCompany = $this->findCompany('1')->one();
            $listBoat = ArrayHelper::map($this->findBoat()->where(['id_company'=>$listCompany->id])->all(), 'id', 'name');
        }
        $Route = $this->findRoute();
        $est_time = ArrayHelper::map(TEstTime::find()->all(), 'id', 'est_time');
        foreach ($Route as $key => $value) {
            $list[$value->id] = $value->departureHarbor->name."->".$value->arrivalHarbor->name;
        }
        $listRoute = $list;
        $model->date = $date;
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->saveRangeTrip($model,$model->date,$model->dept_time);
            return $this->redirect('/trip/index');
        } else {
            return $this->render('_add-dayli', [
                'model' => $model,
                'listBoat'=>$listBoat,
                'listCompany'=>$listCompany,
                'listRoute'=>$listRoute,
                'modelAvaibility'=>$modelAvaibility,
                'est_time'=>$est_time,

            ]);
        }
    }

    /**
     * Updates an existing TTrip model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $listBoat = ArrayHelper::map($this->findBoat()->all(), 'id', 'name');
        if(Helper::checkRoute('/booking/validation')){
            $listCompany = ArrayHelper::map($this->findCompany()->all(), 'id', 'name');
        }else{
            
            $listCompany = ArrayHelper::map($this->findCompany($type = 'company')->all(), 'id', 'name');
        }
        $Route = $this->findRoute();
        foreach ($Route as $key => $value) {
            $list[$value->id] = $value->departureHarbor->name."->".$value->arrivalHarbor->name;
        }
        $listRoute = $list;
        
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if (($model->adult_price != $model->getOldAttribute('adult_price')) || ($model->child_price != $model->getOldAttribute('child_price'))) {
               $model->id_price_type = '2';
            }
            
            $model->save(false);
            return $this->redirect('/trip/index');
        } else {
            return $this->render('update', [
                'model' => $model,
                'listBoat'=>$listBoat,
                'listCompany'=>$listCompany,
                'listRoute'=>$listRoute,

            ]);
        }
    }

    protected function findAvaibility($id_trip)
    {
        if (($model = TAvaibility::findOne(['id_trip'=>$id_trip])) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * Deletes an existing TTrip model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the TTrip model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return TTrip the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if(Helper::checkRoute('/booking/*')){

            if (($model = TTrip::findOne($id)) !== null) {
                return $model;
            } else {
                throw new NotFoundHttpException('The requested page does not exist.');
            }
        }else{
            if (($model = TTrip::find()->joinWith('idBoat.idCompany')->where('t_company.id_user = :userid',[':userid' => Yii::$app->user->identity->id])->andWhere(['t_trip.id'=>$id])->one()) !== null) {
                return $model;
            } else {
                throw new NotFoundHttpException('The requested page does not exist.');
            }
        }
    }
}
