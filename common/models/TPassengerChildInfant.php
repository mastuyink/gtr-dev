<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "t_passenger".
 *
 * @property integer $id
 * @property string $id_booking
 * @property string $name
 * @property integer $id_nationality
 * @property string $birthday
 * @property integer $id_type
 * @property string $datetime
 *
 * @property TBooking $idBooking
 * @property TPassengerType $idType
 * @property TNationality $idNationality
 */
class TPassengerChildInfant extends \yii\db\ActiveRecord
{
    const TYPE_CHILD  = 2;
    const TYPE_INFANT = 3;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 't_passenger';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id_booking', 'name', 'id_nationality', 'id_type','birthday'], 'required', 'message' => 'This field Required'],
            [['id', 'id_nationality', 'id_type'], 'integer'],
            [['birthday', 'datetime'], 'safe'],
            [['id_booking'], 'string', 'max' => 5],
            [['name'], 'string', 'max' => 50],
            [['id_booking'], 'exist', 'skipOnError' => true, 'targetClass' => TBooking::className(), 'targetAttribute' => ['id_booking' => 'id']],
            [['id_type'], 'exist', 'skipOnError' => true, 'targetClass' => TPassengerType::className(), 'targetAttribute' => ['id_type' => 'id']],
            [['id_nationality'], 'exist', 'skipOnError' => true, 'targetClass' => TNationality::className(), 'targetAttribute' => ['id_nationality' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'id_booking' => Yii::t('app', 'Id Booking'),
            'name' => Yii::t('app', 'Name'),
            'id_nationality' => Yii::t('app', 'Id Nationality'),
            'birthday' => Yii::t('app', 'Birthday'),
            'id_type' => Yii::t('app', 'Id Type'),
            'datetime' => Yii::t('app', 'Datetime'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdBooking()
    {
        return $this->hasOne(TBooking::className(), ['id' => 'id_booking']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdType()
    {
        return $this->hasOne(TPassengerType::className(), ['id' => 'id_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIdNationality()
    {
        return $this->hasOne(TNationality::className(), ['id' => 'id_nationality']);
    }
}
