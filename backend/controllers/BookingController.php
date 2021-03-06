<?php

namespace backend\controllers;

use Yii;
use common\models\TBooking;
use app\models\TBookingSearch;
use app\models\BookingValidate;
use backend\models\TBookingLog;
use backend\models\TPaymentLog;
use common\models\TPassenger;
use common\models\TRoute;
use common\models\THarbor;
use common\models\TMailQueue;
use common\models\TTrip;
use common\models\TCompany;
use common\models\TShuttleTime;
use common\models\TConfirmPayment;
use common\models\TPayment;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use mdm\admin\components\Helper;
use yii\helpers\ArrayHelper;
use kartik\mpdf\Pdf;
use yii\helpers\FileHelper;
/**
 * BookingController implements the CRUD actions for TBooking model.
 */
class BookingController extends Controller
{
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

    protected function findRoute($departure,$return){
     return TRoute::find()->where(['departure'=>$departure])->andWhere(['arrival'=>$return])->asArray()->one();
    }


    public function actionBookingModify(){
        
        if (Yii::$app->request->isPjax) {
            $data = Yii::$app->request->queryParams['TBooking'];
            if(($route = $this->findRoute($data['departurePort'],$data['arrivalPort'])) !== null){
             $formData = ['date'=>$data['date'],'id_route'=>$route['id'],'pax'=>Yii::$app->request->queryParams['pax']];
             $modelTrip = TTrip::getAvailableTrip($formData);
                return $this->renderAjax('result-one',[
                            'departureList' =>$modelTrip,
                            'id_booking'    =>$data['id'],
                            'pax' => Yii::$app->request->queryParams['pax'],
                            ]);
            }else{
                return "<h1>No Route Available</h1>";
            }
        }
        if (Yii::$app->request->isAjax) {
            $data = Yii::$app->request->post();
            $modelBooking = $this->findModel($data['id_booking']);
            $route = THarbor::find()->joinWith('idIsland')->asArray()->orderBy(['id_island'=>SORT_ASC,'name'=>SORT_ASC])->all();
            foreach ($route as $key => $value) {
                $arrayRoute[] = ['id'=>$value['id'],'name'=>$value['name'],'island'=>$value['idIsland']['island']];
            }
            $listDept =ArrayHelper::map($arrayRoute, 'id', 'name', 'island');
            return $this->renderAjax('booking-modify',[
                    'modelBooking' =>$modelBooking,
                    'listDept'     =>$listDept,
                    ]);
        }
        if (Yii::$app->request->isPost) {
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $data                  = Yii::$app->request->post();
                $modelNewTrip          = $this->findOneTrip($data['id_trip']);
                $modelBooking          = $this->findModel($data['id_booking']);
                $modelTrip             = $modelBooking->idTrip;
                $affectedPassengers    = count($modelBooking->affectedPassengers);
                $modelTrip->stock      = $modelTrip->stock+$affectedPassengers;
                $modelTrip->cancel     = $modelTrip->cancel+$affectedPassengers;
                $modelTrip->sold       = $modelTrip->sold-$affectedPassengers;
                $modelTrip->save(false);
                $modelBooking->id_trip = $data['id_trip'];
                $modelBooking->save(false);
                $modelNewTrip->stock   = $modelNewTrip->stock-$affectedPassengers;
                $modelNewTrip->sold    = $modelNewTrip->sold+$affectedPassengers;
                $modelNewTrip->save(false);
                $note = $modelTrip->idBoat->idCompany->name."-".$modelTrip->idRoute->departureHarbor->name."=>".$modelTrip->idRoute->arrivalHarbor->name."-".date('d-m-Y H:i',strtotime($modelTrip->date." ".$modelTrip->dept_time))." <br>=TO=<br>".$modelNewTrip->idBoat->idCompany->name."-".$modelNewTrip->idRoute->departureHarbor->name."=>".$modelNewTrip->idRoute->arrivalHarbor->name."-".date('d-m-Y H:i',strtotime($modelNewTrip->date." ".$modelNewTrip->dept_time));
                TBookingLog::addLog($modelBooking->id,TBookingLog::EVENT_MODIFY,$note);
                $transaction->commit();
                return $this->redirect(['index']);
            } catch(\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
            
        }

    }

    protected function findOneTrip($id_trip){
        if(($model = TTrip::findOne($id_trip)) !== null){
            return $model;
        }else{
            throw new NotFoundHttpException('Data Not Found... Please try Again');
        }
    }

    public function actionResendTicketBooking(){
        if (Yii::$app->request->isAjax) {
            $data = Yii::$app->request;
            if (($modelBooking = $this->findModel($data->post('id_booking'))) !== null) {
                $ticket = $this->generateTicket([$modelBooking],$modelBooking->idPayment,$data->post('receipt'));
                TBookingLog::addLog($modelBooking->id,TBookingLog::EVENT_RES_TICK);
                return $ticket;
            }else{
                return "Data Booking Not Found";
            }

        }
    }

    protected function generateTicket(array $modelBooking, $modelPayment, $receipt){
        try {
            $savePath =  Yii::$app->basePath."/E-Ticket/".$modelPayment->token."/";
            FileHelper::createDirectory ( $savePath, $mode = 0777, $recursive = true );
            $Ticket = new Pdf([
            'filename'=>$savePath.'E-Ticket.pdf',
            // A4 paper format
            'format' => Pdf::FORMAT_A4, 
            // portrait orientation
            'orientation' => Pdf::ORIENT_PORTRAIT,
            'tempPath'    => Yii::getAlias('@console/runtime/mpdf'),
            // simpan file
            'destination' => Pdf::DEST_FILE,
            'content' => "
                ".$this->renderAjax('/email-ticket/pdf-ticket',[
                    'modelBooking' =>$modelBooking,
                    'modelPayment' =>$modelPayment,
                    'tempdir'      =>$savePath,
                    ])." ",
                            // any css to be embedded if required
                            'cssInline' => '.kv-heading-1{
                                                font-size:18px
                                            }
                                            .judul{
                                                font-size:25px;
                                            }
                                            @media print{
                                                .page-break{display: block;page-break-before: always;}
                                            }
                                            .secondary-text{
                                                color: #212121;
                                            }
                                            .primary-text{
                                                color: #212121;
                                            }
                                            .island{
                                                color: #424242;
                                                font-size:17px;
                                                
                                            }
                                            .ports{
                                                color: #616161;
                                                font-size:12px;
                                            }
                                            '
                                            , 
                            //set mPDF properties on the fly
                            'options'   => ['title' => 'E-Ticket Gilitransfers'],
                            // call mPDF methods on the fly
                            'methods'   => [ 
                            'SetHeader' =>['E-Ticket Gilitransfers'], 
                            'SetFooter' =>[
                                'Please take this Ticket on your trip as a justification<br>
                                <span style="width:100%;"><img style="width:100%; height: 75px;" src="'.Yii::$app->basePath.'/E-Ticket/banner.jpeg"></span>'],
                    ]
                ]);
            $Ticket->render();
            if ($receipt == "1") {
                $Receipt = new Pdf([
                'filename'=>$savePath.'Receipt.pdf',
                // A4 paper format
                'format' => Pdf::FORMAT_A4, 
                // portrait orientation
                'orientation' => Pdf::ORIENT_PORTRAIT, 
                // simpan file
                'destination' => Pdf::DEST_FILE,
                'content' => "
                    ".$this->renderAjax('/email-ticket/pdf-receipt',[
                        'modelBooking'=>$modelBooking,
                        'modelPayment'=>$modelPayment,
                        'tempdir'      =>$savePath,
                        ])." ",
                                // any css to be embedded if required
                                'cssInline' => '.kv-heading-1{
                                                    font-size:18px
                                                }
                                                .judul{
                                                    font-size:25px;
                                                }
                                                .primary-text{
                                                    text-color: #212121;
                                                    font-size:25px;
                                                }
                                                .ports{
                                                color: #616161;
                                                font-size:10px;
                                                }
                                                ', 
                                //set mPDF properties on the fly
                                'options'   => ['title' => 'Receipt Gilitransfers'],
                                // call mPDF methods on the fly
                                'methods'   => [ 
                                'SetHeader' =>['Receipt Gilitransfers'], 
                                'SetFooter' =>['This receipt automatically printed by system and doesnt require a signature'],
                        ]
                    ]);
                $Receipt->render();
            }
              $sendTicket = Yii::$app->mailReservation->compose()
                ->setFrom('reservation@gilitransfers.com')
                ->setTo($modelPayment->email)
                ->setSubject('E-Ticket GiliTransfers')
                ->setHtmlBody($this->renderAjax('/email-ticket/email-ticket',[
                    'modelBooking'=>$modelBooking,
                    'modelPayment'=>$modelPayment,
                    ]))
                ->attach($savePath."E-Ticket.pdf");
            if ($receipt == "1") {
                $sendTicket->attach($savePath."Receipt.pdf");
            }
             $sendTicket->send();
                FileHelper::removeDirectory($savePath);
                return 'Sending Ticket Successsfull';
            } catch(\Exception $e) {
                 FileHelper::removeDirectory($savePath);
                return '<center><h1>Sending Ticket Failed</h1></center><br>'.$e;
            }

    }


    public function actionResendReservationPayment(){
        if (Yii::$app->request->isAjax) {
            $data = Yii::$app->request->post();
            $type = $data['type'];
            $modelPayment = TPayment::findOne($data['id_payment']);
            $modelBooking = $modelPayment->tBookings;
            try {
                foreach ($modelBooking as $key => $value) {

                    if ($value->idTrip->idRoute->departureHarbor->id_island == '2' && $value->idTrip->idBoat->idCompany->email_gili != null) {          
                       $mailSupplier =  $this->sendMailSupplier($value->idTrip->idBoat->idCompany->email_gili, $value, $modelPayment,$type);
                    }else{
                       $mailSupplier =  $this->sendMailSupplier($value->idTrip->idBoat->idCompany->email_bali,  $value, $modelPayment,$type);
                    }
                    if ($type == '1') {
                        TPaymentLog::addPaymmentLog($modelPayment->id,TPaymentLog::EVENT_RES_RESV);
                    }else {
                        TPaymentLog::addPaymmentLog($modelPayment->id,TPaymentLog::EVENT_FAST_CANCEL);
                    }
                    
                }

                return 'Successsfull';
            } catch (Exception $e) {
                return 'Failed. Please Try Again';
            }
            
        }else{
            return $this->goHome();
        }
    }

    public function actionResendReservationBooking(){
        if (Yii::$app->request->isAjax) {
            $data = Yii::$app->request;
            $type = $data->post('type');
            if (($modelBooking = $this->findModel($data->post('id_booking'))) !== null) {
                $modelPayment = $modelBooking->idPayment;
                try {
                        if ($modelBooking->idTrip->idRoute->departureHarbor->id_island == '2' && $modelBooking->idTrip->idBoat->idCompany->email_gili != null) {          
                           $mailSupplier =  $this->sendMailSupplier($modelBooking->idTrip->idBoat->idCompany->email_gili, $modelBooking, $modelPayment,$type);
                        }else{
                           $mailSupplier =  $this->sendMailSupplier($modelBooking->idTrip->idBoat->idCompany->email_bali,  $modelBooking, $modelPayment,$type);
                        }

                        if ($type == '1') {
                            TBookingLog::addLog($modelBooking->id,TBookingLog::EVENT_RES_RESV);
                        }else {
                            TBookingLog::addLog($modelBooking->id,TBookingLog::EVENT_FAST_CANCEL);
                        }
                    return 'Successsfull';
                } catch (Exception $e) {
                    return 'Failed. Please Try Again<br>'.$e;
                }
            }else{
                return "Data Not Found";
            }
            
        }else{
            return $this->goHome();
        }
    }

    protected function sendMailSupplier($to,$modelBooking,$modelPayment,$type){

        $mail = Yii::$app->mailReservation->compose()
                    ->setFrom('reservation@gilitransfers.com')
                    ->setTo($to);

        if (($mailCC = $modelBooking->idTrip->idBoat->idCompany->email_cc) !==  null) {
            $mail->setCc($mailCC);
        }
        if ($type == '1') {
            $messange = 'Booking';
        }elseif ($type == '2') {
            $messange = 'Cancellation Booking';
        }
        
        $mail->setSubject($messange.' For ('.date('d-m-Y',strtotime($modelBooking->idTrip->date)).") ".$modelPayment->name)
                    ->setHtmlBody($this->renderAjax('/email-ticket/email-supplier',[
                        'modelBooking' => $modelBooking,
                        'modelPayment' => $modelPayment,
                        'user_token'   => Yii::$app->getSecurity()->maskToken($modelBooking->idTrip->idBoat->idCompany->idUser->auth_key),
                        'date'         => $modelBooking->idTrip->date,
                        'dept_time'    => $modelBooking->idTrip->dept_time,
                        'island_route' => $modelBooking->idTrip->idRoute->departureHarbor->id_island.'-'.$modelBooking->idTrip->idRoute->arrivalHarbor->id_island,
                        'type'         => $type
                        ]))->send();
        return true;
    }



    public function actionResendTicketPayment(){
        if (Yii::$app->request->isAjax) {
           $data = Yii::$app->request;
           if (($modelPayment = $this->findOnePayment($data->post('id_payment'))) !== null) {
                    $ticket          = $this->generateTicket($modelPayment->tBookings,$modelPayment,$data->post('receipt'));
                    $modelLogPayment = TPaymentLog::addPaymmentLog($modelPayment->id,TPaymentLog::EVENT_RES_TICK);
                    return $ticket;
            }else{
                    return "Data Booking Not Found";
           }
            
        }else{
            return $this->goHome();
        }
    }

    protected function findOnePayment($id_payment){
        return TPayment::findOne($id_payment);
    }

    protected function findOneBookingAsArray($id_booking){
        if(Helper::checkRoute('/booking/*')){
            return TBooking::find()->where(['id'=>$id_booking])->asArray()->one();           
        }else{

            return TBooking::find()->joinWith('idTrip.idBoat.idCompany')->where(['t_company.id_user'=>Yii::$app->user->identity->id])->andWhere(['t_booking.id'=>$id])->asArray()->one();
        }
    }

    public function actionReadCheckPayment(){
        if (Yii::$app->request->isAjax) {
            $data = Yii::$app->request->post();
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $modelLogPayment = TPaymentLog::addPaymmentLog($data['idp'],TPaymentLog::EVENT_READ_CHECK);
                $transaction->commit();
            } catch(\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }
            
        }
    }

    public function actionCheckLog($id_payment){
        if(($modelLogPayment = TPaymentLog::find()->joinWith(['idUser','idEvent'])->where(['id_payment'=>$id_payment])->asArray()->all()) != null){
            $btn = '<a data-toggle="popover" data-trigger="hover focus" data-popover-content="#log-'.$id_payment.'" data-placement="right" class="btn btn-xs btn-success fa fa-check-square-o"></a>';
            return $btn.'<div id="log-'.$id_payment.'" class="hidden panel panel-primary">'.$this->renderPartial('_log',['modelLog'=>$modelLogPayment]).'</div>';
           
        }else{
            return "<a data-toggle='tooltip' title='Mark As Read & Check' class='read-btn btn material-btn material-btn_xs fa fa-check-square-o' value='".$id_payment."'></a>";
        }
    }

    public function actionCheckLogBooking($id_booking){
        if(($modelLogBooking = TBookingLog::find()->joinWith(['idUser','idEvent'])->where(['id_booking'=>$id_booking])->asArray()->all()) != null){
            $btn = '<a data-toggle="popover" data-trigger="hover focus" data-popover-content="#log-booking-'.$id_booking.'" data-placement="right" class="btn-default btn-xs glyphicon glyphicon-time"></a>';
            return $btn.'<div id="log-booking-'.$id_booking.'" class="hidden panel panel-primary">'.$this->renderPartial('_log',['modelLog'=>$modelLogBooking]).'</div>';
           
        }else{
            return null;
        }
    }

    public function actionPaymentSlip($id){
        $modelSlip = TConfirmPayment::find()->where(['id'=>$id])->asArray()->one();
        $response = Yii::$app->getResponse();
        return $response->sendFile($modelSlip['proof_payment'],'slip.jpg', [
                //'mimeType' => 'image/jpg',
               //'fileSize' => '386',
                'inline' => true
        ]);
    }

    public function actionShuttleTime(){
        if (Yii::$app->request->isAjax) {
            $data = Yii::$app->request->post();
            if (($modelShuttleTime = TShuttleTime::find()->where(['id_company'=>$data['keylist'][0],'id_route'=>$data['keylist'][1],'dept_time'=>$data['keylist'][2],'id_area'=>$data['keylist'][3]])->one()) !== null) {
                echo " ".$modelShuttleTime->shuttle_time_start." <span style='font-size: 10px;' class='fa fa-arrow-right'></span> ".$modelShuttleTime->shuttle_time_end;
            }else{
                echo " Unknown";
            }
            
        }
    }

    public function actionDetailModal($id_booking){
        $modelBooking = $this->findModel($id_booking);
        if(Helper::checkRoute('/booking/*')){
            return $this->renderAjax('_modal-detail-booking',['modelBooking'=>$modelBooking]);
        }else{
            return $this->renderAjax('supplier/_modal-detail-booking',['modelBooking'=>$modelBooking]);
        }
    }

    public function actionCountPassenger(array $var){
        $modelBooking = TBooking::find()->joinWith('idTrip.idBoat')->select('t_booking.id')->where(['t_boat.id_company'=>$var['id_company']])->andWhere(['t_trip.id_route'=>$var['id_route']])->andWhere(['t_trip.date'=>$var['date']])->andWhere(['t_trip.dept_time'=>$var['dept_time']])->andWhere(['between','id_status',TBooking::STATUS_PAID,TBooking::STATUS_REFUND_FULL])->all();
        foreach ($modelBooking as $key => $value) {
            $jumlahPax[] = count($value->affectedPassengers);
        }
        return array_sum($jumlahPax);

    }

    public function actionCountBooking(array $var){
        $modelBooking = TBooking::find()->joinWith('idTrip.idBoat')->where(['t_boat.id_company'=>$var['id_company']])->andWhere(['t_trip.id_route'=>$var['id_route']])->andWhere(['t_trip.date'=>$var['date']])->andWhere(['t_trip.dept_time'=>$var['dept_time']])->andWhere(['between','id_status',TBooking::STATUS_PAID,TBooking::STATUS_REFUND_FULL])->count();
        return $modelBooking;

    }
    public function actionDetail(){
        if (isset($_POST['expandRowKey'])) {
            $model = $this->findModel($_POST['expandRowKey']);
            $modelBooking = TBooking::getBookingGroupPayment($model);
           //$modelPassenger = TPassenger::find();
            return $this->renderAjax('_detail-booking', [
                'modelBooking'=>$modelBooking,
                //'modelPassenger'=> $modelPassenger,
                //'mode'
                ]);
        } else {
            return '<div class="alert alert-danger">Data Not Found</div>';
        }
    }

    protected function findOneBookingArray($id){
        if (($modelBooking = TBooking::find()->where(['id'=>$id])->asArray()->one()) !== null) {
            return $modelBooking;
        }else{
            throw new Exception("Error Processing Request");
            
        }
    }

    protected function findAllRoute(){
        return TRoute::find()->all();
    }

    public function actionSummary(){
        if (Yii::$app->request->isAjax) {
           
            $Booking = TBooking::find()->joinWith('idTrip.idBoat.idCompany');
            if (Helper::checkRoute('/booking/*')) {
                 $Route = $this->findAllRoute();
                $Booking->where('id_status > :zero',[':zero'=>0]);
                foreach ($Route as $key => $value) {
                $result = $Booking->andWhere('t_trip.id_route = :idroute',[':idroute'=>$value->id])->all();
                echo '<li class=" col-xs-6 list-group-item material-list-group__item material-list-group__item">
                '.$value->departureHarbor->name.' -> '.$value->arrivalHarbor->name.'
                <span class="pull-right label label-primary material-label material-label_sm material-label_primary main-container__column">'.count($result).'</span>
                </li>';
            }
            }
            /*else{
                $user = Yii::$app->user->identity->id;
                $Trip = TTrip::find()->joinWith('idBoat.idCompany')->where('t_company.id_user = :iduser',[':iduser'=>$user])->groupBy('id_route')->all();
                $Booking->where('t_company.id_user = :iduser',[':iduser'=>$user])->andWhere(['between','id_status',4,5]);

                foreach ($Trip as $x => $val) {
                $Route = TRoute::findOne($val->id_route);
                $result = $Booking->andWhere('t_trip.id_route = :idroute',[':idroute'=>$Route->id])->all();
                echo '<li class=" col-xs-6 list-group-item material-list-group__item material-list-group__item">
                '.$Route->departureHarbor->name.' -> '.$Route->arrivalHarbor->name.'
                <span class="pull-right label label-primary material-label material-label_sm material-label_primary main-container__column">'.count($result).'</span>
                </li>';
                 }
            }*/
           
            
          
        }
    }

protected function findAllBooking(){
    if(Helper::checkRoute('/booking/*')){
        return TBooking::find()->where(['between','id_status',4,6])->all();
    }else{
        return TBooking::find()->joinWith('idTrip.idBoat.idCompany')->where(['between','id_status',4,6])->andWhere(['t_company.id_user'=>Yii::$app->user->identity->id])->all();
    }

}
    /**
     * Lists all TBooking models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new TBookingSearch();
        $dataProvider = $searchModel->summarySearch(Yii::$app->request->queryParams);
        $findPassengers = TPassenger::find();
        $listCompany = ArrayHelper::map(TCompany::find()->asArray()->all(), 'id', 'name');
        foreach ($this->findAllBooking() as $key => $value) {
            $res[] = $value->id;
        }
        if(Helper::checkRoute('/booking/*')){
            $listDept = ArrayHelper::map(THarbor::find()->all(), 'id', 'name', 'idIsland.island');
            $listBuyer = ArrayHelper::map(TPayment::find()->asArray()->groupBy(['name'])->all(), 'name', 'name');
            $request = Yii::$app->request;
            $table_layout = isset(Yii::$app->request->queryParams['TBookingSearch']['table_layout']) ? Yii::$app->request->queryParams['TBookingSearch']['table_layout'] : null;
            if ($table_layout == $searchModel::LAYOUT_GROUP) {
                $dataProvider->pagination->pageSize=100;
                return $this->render('index-group', [
                    'searchModel'    => $searchModel,
                    'dataProvider'   => $dataProvider,
                    'findPassengers' => $findPassengers,
                    'bookingList'    => isset($res) ? $res : $res = ['empty'=>'empty'],
                    'listDept'       => $listDept,
                    'listCompany'    => $listCompany,
                    'listBuyer'      => $listBuyer,
                ]);
            }else{

                return $this->render('index-flat', [
                    'searchModel'    => $searchModel,
                    'dataProvider'   => $dataProvider,
                    'findPassengers' => $findPassengers,
                    'bookingList'    => isset($res) ? $res : $res = ['empty'=>'empty'],
                    'listDept'       => $listDept,
                    'listCompany'    => $listCompany,
                    'listBuyer'      => $listBuyer,
                ]);
            }
        }else{
            $listDept = ArrayHelper::map(TTrip::find()->joinWith(['idBoat.idCompany','idRoute.departureHarbor'])->where(['t_company.id_user'=>Yii::$app->user->identity->id])->groupBy('id_route')->asArray()->all(), 'idRoute.departure', 'idRoute.departureHarbor.name');
            return $this->render('supplier/index', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'findPassengers' => $findPassengers,
                'bookingList' => isset($res) ? $res : $res = ['empty'=>'empty'],
                'listDept' => $listDept,
            ]);
        }
    }

        public function actionValidation()
    {
        $searchModel = new BookingValidate();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('validation', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionValidationAccept(){
        if (Yii::$app->request->isAjax) {
            $data = Yii::$app->request->post();
            $modelPayment = $this->findPayment($data['id']);
            $transaction = Yii::$app->db->beginTransaction();
            try {
                $modelPayment->setPaymentBookingStatus($modelPayment::STATUS_CONFIRM_NOT_RECEIVED,$modelPayment::STATUS_CONFIRM_RECEIVED,true,1);
                $modelPayment->validate();
                $modelPayment->save(false);
                
                //payment status 3 booking status 4;
                $modelQueue = TMailQueue::addTicketQueue($modelPayment->id);
               $transaction->commit();
                return true;
            } catch(\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }

        }else{
            return $this->goHome();
        }
    }

    protected function findPayment($id){
        if (($modelPayment = TPayment::findOne($id)) !== null) {
            return $modelPayment;
        }else{
            throw new Exception("Error Processing Request");
        }
    }
        public function actionValidationReject(){
        if (Yii::$app->request->isAjax) {
            $data = Yii::$app->request->post();
            $idPayment = $data['id'];
            $modelPayment = $this->findPayment($data['id']);
            $transaction = Yii::$app->db->beginTransaction();
           try {
                $modelPayment->setPaymentBookingStatus($modelPayment::STATUS_INVALID,$modelPayment::STATUS_INVALID,true,2);
                $modelPayment->save(false);
                //payment status 100 booking status 100;
               $transaction->commit();
            } catch(\Exception $e) {
                $transaction->rollBack();
                throw $e;
            }

        }else{
            return $this->goHome();
        }
    }

    protected function findBookingByPayment($idPayment){
        if (($model = TBooking::find()->where(['id_payment'=>$idPayment])->all()) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('Booking Data does not exist.');
        }
    }

    /**
     * Displays a single TBooking model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new TBooking model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new TBooking();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing TBooking model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing TBooking model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the TBooking model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return TBooking the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if(Helper::checkRoute('/booking/*')){
            return TBooking::findOne($id);           
        }else{

            return TBooking::find()->joinWith('idTrip.idBoat.idCompany')->where(['t_company.id_user'=>Yii::$app->user->identity->id])->andWhere(['t_booking.id'=>$id])->one();
        }
    }
}
