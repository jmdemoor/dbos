<?php

namespace app\models\accounting;

use app\helpers\TokenHelper;
use app\models\base\BaseEndable;
use app\models\value\Lob;
use app\models\value\RateClass;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;


/**
 * This is the model class for table "DuesRates".
 *
 * @property integer $id
 * @property string $lob_cd
 * @property string $rate_class
 * @property number $rate
 *
 * @property Lob $lobCd
 * @property RateClass $rateClass
 * @property DuesStripeProduct $duesStripeProduct
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
     * @param $lob_cd
     * @param $rate_class
     * @param $date
     * @return false|float|null
     * @noinspection PhpReturnDocTypeMismatchInspection
     */
    public static function findCurrentValByTrade($lob_cd, $rate_class, $date = null)
    {
        $query = self::find()->select('rate')
            ->where([
                'lob_cd' => $lob_cd,
                'rate_class' => $rate_class,
            ]);
        if (isset($date)) {
            $query->andWhere(['<=', 'effective_dt', $date]);
            $query->andWhere(['or',
                ['end_dt' => null],
                ['>=', 'end_dt', $date],
            ]);
        } else {
            $query->andWhere(['end_dt' => null]);
        }
        return $query->scalar();
    }

    /**
     * @param $lob_cd
     * @param $rate_class
     * @param null $date
     * @return array|ActiveRecord|null
     */
    public static function findCurrentByTrade($lob_cd, $rate_class, $date = null)
    {
        $query = self::find()
            ->where([
                'lob_cd' => $lob_cd,
                'rate_class' => $rate_class,
            ]);
        if (isset($date)) {
            $query->andWhere(['<=', 'effective_dt', $date]);
            $query->andWhere(['or',
                ['end_dt' => null],
                ['>=', 'end_dt', $date],
            ]);
        } else {
            $query->andWhere(['end_dt' => null]);
        }
        return $query->one();
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

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        TokenHelper::setToken(FeeCalendar::TOKEN_REFRESH, FeeCalendar::TOKEN_REFRESH_DATA);
    }

    public function afterDelete()
    {
        parent::afterDelete();
        TokenHelper::setToken(FeeCalendar::TOKEN_REFRESH, FeeCalendar::TOKEN_REFRESH_DATA);
    }

    /**
     * @return ActiveQuery
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
     * @return ActiveQuery
     */
    public function getRateClass()
    {
        return $this->hasOne(RateClass::className(), ['rate_class' => 'rate_class']);
    }

    public function getRateClassOptions()
    {
        return ArrayHelper::map(RateClass::find()->orderBy('descrip')->all(), 'rate_class', 'descrip');
    }

    public function getDuesStripeProduct()
    {
        return $this->hasOne(DuesStripeProduct::className(), ['dues_rate_id' => 'id']);
    }

}
