<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "t_booking".
 *
 * @property string $id
 * @property integer $id_trip
 * @property integer $id_payment
 * @property double $trip_price
 * @property double $total_price
 * @property string $currency
 * @property integer $total_idr
 * @property integer $exchange
 * @property integer $id_status
 * @property integer $id_payment_method
 * @property double $send_amount
 * @property string $
 * @property integer $process_by
 * @property string $datetime
 *
 * @property TBookingStatus $idStatus
 * @property TPaymentMethod $idPaymentMethod
 * @property TPayment $idCustomer
 * @property TKurs $currency0
 * @property TTrip $idTrip
 * @property TDrop $tDrop
 * @property TPickup $tPickup
 */
class TBooking extends \yii\db\ActiveRecord
{
    const STATUS_ON_BOOK        = 1; // ADULT
    const STATUS_UNPAID         = 2; //CHILD
    const STATUS_VALIDATION     = 3; //INFANT
    const STATUS_PAID           = 4;
    const STATUS_SUCCESS        = 5;
    const STATUS_REFUND_PARTIAL = 6;
    const STATUS_REFUND_FULLL   = 7;
    const STATUS_EXPIRED        = 99;
    const STATUS_INVALID        = 100;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 't_booking';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id','id_payment', 'id_trip', 'trip_price', 'total_price', 'currency', 'total_idr', 'exchange'], 'required'],
            [['id_trip', 'total_idr', 'exchange', 'id_status', 'process_by'], 'integer'],
            [['trip_price', 'total_price'], 'number'],
            [['datetime'], 'safe'],
            [['id'], 'string', 'max' => 6],
            [['currency'], 'string', 'max' => 5],
            [['id_status'],'in','range'=>[self::STATUS_ON_BOOK, self::STATUS_UNPAID, self::STATUS_VALIDATION, self::STATUS_PAID, self::STATUS_SUCCESS, self::STATUS_REFUND_PARTIAL, self::STATUS_REFUND_FULLL, self::STATUS_EXPIRED, self::STATUS_INVALID]],
            [['id_status'], 'exist', 'skipOnError' => true, 'targetClass' => TBookingStatus::className(), 'targetAttribute' => ['id_status' => 'id']],
            [['currency'], 'exist', 'skipOnError' => true, 'targetClass' => TKurs::className(), 'targetAttribute' => ['currency' => 'currency']],
            [['id_trip'], 'exist', 'skipOnError' => true, 'targetClass' => TTrip::className(), 'targetAttribute' => ['id_trip' => 'id']],
             [['id_payment'], 'exist', 'skipOnError' => true, 'targetClass' => TPayment::className(), 'targetAttribute' => ['id_payment' => 'id']],
        ];
    }


public function generateBookingNumber($attribute, $length = 4){
    $pool = array_merge(range(0,9),range('A', 'Z')); 
    for($i=0; $i < $length; $i++) {
        $key[] = $pool[mt_rand(0, count($pool) - 1)];
    }
    // if ($type == '2') {
         //   $kodeBooking = "G".join($key)."Y";
       // }else{
            $kodeBooking = "G".join($key);
       // }          
     
    if(!$this->findOne([$attribute => $kodeBooking])) {
        return $kodeBooking;
    }else{
        return $this->generateBookingNumber($attribute,$length);
    }
            
}
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'Book Code'),
            'id_trip' => Yii::t('app', 'Trip'),
            'id_payment' => Yii::t('app', 'Payment'),
            'trip_price' => Yii::t('app', 'Trip Price'),
            'total_price' => Yii::t('app', 'Total Price'),
            'currency' => Yii::t('app', 'Currency'),
            'total_idr' => Yii::t('app', 'Total Idr'),
            'exchange' => Yii::t('app', 'Exchange'),
            'id_status' => Yii::t('app', 'Status'),
            'process_by' => Yii::t('app', 'Process By'),
            'datetime' => Yii::t('app', 'Datetime'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdStatus()
    {
        return $this->hasOne(TBookingStatus::className(), ['id' => 'id_status']);
    }



    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdPayment()
    {
        return $this->hasOne(TPayment::className(), ['id' => 'id_payment']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCurrency0()
    {
        return $this->hasOne(TKurs::className(), ['currency' => 'currency']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdTrip()
    {
        return $this->hasOne(TTrip::className(), ['id' => 'id_trip']);
    }

    public function getTPassengers()
    {
        return $this->hasMany(TPassenger::className(), ['id_booking' => 'id'])->orderBy(['id_type' => SORT_ASC]);
    }
    public function getAffectedPassengers()
    {
        return $this->hasMany(TPassenger::className(), ['id_booking' => 'id'])->where(['!=','id_type',self::STATUS_VALIDATION]);
    }
    public function getAdultPassengers()
    {
        return $this->hasMany(TPassenger::className(), ['id_booking' => 'id'])->where(['id_type'=>self::STATUS_ON_BOOK]);
    }
    public function getChildPassengers()
    {
        return $this->hasMany(TPassenger::className(), ['id_booking' => 'id'])->where(['id_type'=>self::STATUS_UNPAID]);
    }
    public function getInfantPassengers()
    {
        return $this->hasMany(TPassenger::className(), ['id_booking' => 'id'])->where(['id_type'=>self::STATUS_VALIDATION]);
    }

    public function getPassengersByType($type)
    {
        return $this->hasMany(TPassenger::className(), ['id_booking' => 'id'])->where(['id_type'=>$type]);
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTShuttles()
    {
        return $this->hasOne(TShuttleLocationTmp::className(), ['id_booking' => 'id']);
    }
    public function getShuttleTmp()
    {
        return $this->hasOne(TShuttleLocationTmp::className(), ['id_booking' => 'id']);
    }
}
