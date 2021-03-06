<?php

namespace backend\controllers;

use Yii;
use common\models\TMailQueue;
use common\models\TPassenger;
use common\models\TBooking;
use common\models\TShuttleLocationTmp;
use common\models\TPayment;
use yii\web\Controller;
use kartik\mpdf\Pdf;
use yii\helpers\FileHelper;

/**
 * KursController implements the CRUD actions for TKurs model.
 */
class MailerController extends Controller
{
    /*public function behaviors()
    {
        Yii::$app->view->params['bookvalidation'] = count(TBooking::find()->joinWith('idPayment')->where(['t_payment.id_payment_method'=>2])->andWhere(['between','t_booking.id_status',2,3])->all());
    }*/


    public function beforeAction($action)
    {    
       // if ($action->id == 'paypal') {

            $this->enableCsrfValidation = false;
            return true;
        //}    
    }


    protected function findPassenger(){
        return TPassenger::find();
    }

    protected function findShuttle(){
        return TShuttleLocationTmp::find();
    }

    public function actionPaypal(){
        
        if (($modelQueue = TMailQueue::getQueueList(TMailQueue::STATUS_QUEUE)) !== null) {
            $modelQueue->setQueueStatus(TMailQueue::STATUS_PROCESS);
            $modelPayment = TPayment::findOne($modelQueue->id_payment);
            $modelBooking = $modelPayment->tBookings;
            $findShuttle = $this->findShuttle();
            $findPassenger = $this->findPassenger();
            try {
                $savePath =  Yii::$app->basePath."/E-Ticket/".$modelPayment->token."/";
            FileHelper::createDirectory ( $savePath, $mode = 0777, $recursive = true );
            $Ticket = new Pdf([
            'filename'=>$savePath.'E-Ticket.pdf',
            // A4 paper format
            'format' => Pdf::FORMAT_A4, 
            // portrait orientation
            'orientation' => Pdf::ORIENT_PORTRAIT, 
            // simpan file
            'destination' => Pdf::DEST_FILE,
            'content' => "
                ".$this->renderAjax('/email-ticket/pdf-ticket',[
                    'modelBooking'=>$modelBooking,
                    'modelPayment'=>$modelPayment,
                    'findShuttle'=>$findShuttle,
                    'findPassenger'=>$findPassenger,
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
                        'findPassenger'=>$findPassenger,
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
               Yii::$app->mailReservation->compose()
                ->setFrom('reservation@gilitransfers.com')
                ->setTo($modelPayment->email)
                ->setBcc('reservation@gilitransfers.com')
                ->setSubject('E-Ticket GiliTransfers')
                ->setHtmlBody($this->renderAjax('/email-ticket/email-ticket',[
                    'modelBooking'=>$modelBooking,
                    'modelPayment'=>$modelPayment,
                    ]))
                ->attach($savePath."E-Ticket.pdf")
                ->attach($savePath."Receipt.pdf")
                ->send();

                foreach ($modelBooking as $key => $value) {

                    if ($value->idTrip->idRoute->departureHarbor->id_island == '2' && $value->idTrip->idBoat->idCompany->email_gili != null) {          
                        $this->sendMailSupplier($value->idTrip->idBoat->idCompany->email_gili, $value, $modelPayment);
                    }else{
                        $this->sendMailSupplier($value->idTrip->idBoat->idCompany->email_bali,  $value, $modelPayment);
                    }
                    
                }
                

                FileHelper::removeDirectory($savePath);
                
                $modelQueue->setQueueStatus(TMailQueue::STATUS_SUCCESS);
            } catch(\Exception $e) {
                $modelQueue->setQueueStatus(TMailQueue::STATUS_RETRY);
                 FileHelper::removeDirectory($savePath);
                throw $e;
            }
            
        }else{
            return true;
        }
    }

    protected function sendMailSupplier($to,$modelBooking,$modelPayment){

        $mail = Yii::$app->mailReservation->compose()
                    ->setFrom('reservation@gilitransfers.com')
                    ->setTo($to);

        if (($mailCC = $modelBooking->idTrip->idBoat->idCompany->email_cc) !==  null) {
            $mail->setCc($mailCC);
        }
        $mail->setSubject('Booking For ('.date('d-m-Y',strtotime($modelBooking->idTrip->date)).") ".$modelPayment->name)
                    ->setHtmlBody($this->renderAjax('/email-ticket/email-supplier',[
                        'modelBooking' => $modelBooking,
                        'modelPayment' => $modelPayment,
                        'user_token'   => Yii::$app->getSecurity()->maskToken($modelBooking->idTrip->idBoat->idCompany->idUser->auth_key),
                        'date'         => $modelBooking->idTrip->date,
                        'dept_time'    => $modelBooking->idTrip->dept_time,
                        'island_route' => $modelBooking->idTrip->idRoute->departureHarbor->id_island.'-'.$modelBooking->idTrip->idRoute->arrivalHarbor->id_island,
                        ]))->send();
        return true;
    }

    public function actionInvoice(){
        if (($modelQueue = TMailQueue::getQueueList(TMailQueue::STATUS_PROCESS)) !== null) {
            $modelPayment = TPayment::findOne($modelQueue->id_payment);
            $modelBooking = $modelPayment->tBookings;
            $findShuttle = $this->findShuttle();
            $findPassenger = $this->findPassenger();
            $modelQueue->setQueueStatus(TMailQueue::STATUS_PROCESS);
            try {
                 Yii::$app->mailReservation->compose()->setFrom('reservation@gilitransfers.com')
                 ->setTo($modelPayment->email)
                 ->setSubject('Invoice GiliTransfers')
                 ->setHtmlBody($this->renderAjax('/email-ticket/email-invoice',[
                 'modelBooking'  => $modelBooking,
                 'modelPayment'  => $modelPayment,
                 'findPassenger' => $findPassenger,
                 'maskToken'     => Yii::$app->getSecurity()->maskToken($modelPayment->token),
                 ]))
                 ->send();
                 $modelQueue->setQueueStatus(TMailQueue::STATUS_SUCCESS);
                 return true;
            } catch (Exception $e) {
                $modelQueue->setQueueStatus(TMailQueue::STATUS_RETRY);
                throw $e;
            } 

        }else{
            return true;
        }

    }

}
