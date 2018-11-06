<?php

namespace app\models\accounting;

use app\models\base\BaseEndable;
use app\models\value\Lob;
use app\models\value\RateClass;
use yii\helpers\ArrayHelper;


/**
 * This is the model class for table "DuesRates".
 *
 * @property integer $id
 * @property string $lob_cd
 * @property string $rate_class
 * @property string $effective_dt
 * @property string $end_dt
 * @property number $rate
 *
 * @property Lob $lobCd
 * @property RateClass $rateClass
 */
class DuesRate extends BaseEndable
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'DuesRates';
    }

    public static function qualifier()
    {
        return ['lob_cd', 'rate_class'];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['lob_cd', 'rate_class', 'effective_dt', 'rate'], 'required'],
            [['effective_dt', 'end_dt'], 'safe'],
            [['rate'], 'number'],
            [['lob_cd'], 'string', 'max' => 4],
            [['rate_class'], 'string', 'max' => 2],
            [['lob_cd', 'rate_class', 'effective_dt'], 'unique', 'targetAttribute' => ['lob_cd', 'rate_class', 'effective_dt'], 'message' => 'The combination of Local, Rate Class and Effective has already been taken.']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'lob_cd' => 'Local',
            'rate_class' => 'Rate Class',
            'effective_dt' => 'Effective',
            'end_dt' => 'End',
            'rate' => 'Rate',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLobCd()
    {
        return $this->hasOne(Lob::className(), ['lob_cd' => 'lob_cd']);
    }

    public function getLobOptions()
    {
        return ArrayHelper::map(Lob::find()->orderBy('short_descrip')->all(), 'lob_cd', 'descrip');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRateClass()
    {
        return $this->hasOne(RateClass::className(), ['rate_class' => 'rate_class']);
    }

    public function getRateClassOptions()
    {
        return ArrayHelper::map(RateClass::find()->orderBy('descrip')->all(), 'rate_class', 'descrip');
    }


}
