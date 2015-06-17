<?php

namespace app\models\value;

use Yii;

/**
 * This is the model class for table "TradeSpecialties".
 *
 * @property string $specialty
 * @property string $lob_cd
 *
 * @property Lob $lob
 */
class TradeSpecialty extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'TradeSpecialties';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['specialty', 'lob_cd'], 'required'],
            [['specialty'], 'string', 'max' => 50],
        	[['lob_cd'], 'exist', 'targetClass' => '\app\models\value\Lob'],
            [['specialty'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'specialty' => 'Specialty',
            'lob_cd' => 'Trade',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLob()
    {
        return $this->hasOne(Lob::className(), ['lob_cd' => 'lob_cd']);
    }
}
