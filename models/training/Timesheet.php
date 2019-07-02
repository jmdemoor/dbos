<?php

namespace app\models\training;

use app\components\behaviors\OpImageBehavior;
use app\components\utilities\OpDate;
use app\helpers\OptionHelper;
use app\models\contractor\Contractor;
use app\models\member\Member;
use app\models\user\User;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "Timesheets".
 *
 * @property integer $id
 * @property string $member_id
 * @property string $acct_month
 * @property integer $created_at
 * @property integer $created_by
 *
 * @property Member $member
 * @property Contractor $contractor
 * @property WorkHour[] $workHour
 * @property mixed $totalHours
 * @property User $createdBy
 * @property string $enteredBy
 * @property string $total_hours [decimal(9,2)]
 * @property string $doc_id [varchar(20)]
 * @property string $remarks
 * @property string $license_nbr [varchar(8)]
 *
 * @method uploadImage()
 *
 */
class Timesheet extends ActiveRecord
{
    /**
     * @var mixed	Stages document to be uploaded
     */
    public $doc_file;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'Timesheets';
    }

    /**
     * @param $processes  array Work processes that will become columns
     * @return string
     */
    public static function getFlattenedTimesheetsSql($processes)
    {
        $path = Yii::$app->urlManager->baseUrl . Yii::$app->params['docDir'];
        $cols = '';
        foreach ($processes as $process)
            $cols .= "MAX(CASE WHEN WH.wp_seq = " . $process['seq'] . " THEN WH.hours ELSE NULL END) AS `" . $process['descrip'] . "`, ";

        $sql =

            "SELECT
                 T.`id`,
                 DATE_FORMAT(CONCAT(SUBSTRING(T.acct_month, 1, 4), '-', SUBSTRING(T.acct_month, 5, 2), '-01'), '%b %Y') AS acct_month, " .
            $cols .
            "    T.total_hours AS total,
                 COALESCE(SUM(WH.hours), 0.00) AS computed,
                 T.doc_id,
                 T.remarks,
                 CONCAT('{$path}', T.doc_id) AS imageUrl,
                 U.username,
                 T.created_at,
                 C.contractor
               FROM Timesheets AS T 
                 LEFT OUTER JOIN WorkHours AS WH ON T.`id` = WH.timesheet_id
                 JOIN Users AS U ON T.created_by = U.`id`
                 LEFT OUTER JOIN Contractors AS C ON C.license_nbr = T.license_nbr
               WHERE T.member_id = :member_id
               GROUP BY T.`id`, T.acct_month
            ORDER BY T.acct_month DESC
            ";

        return $sql;
    }

    public function behaviors()
    {
        return [
            ['class' => OpImageBehavior::className()],
            ['class' => TimestampBehavior::className(), 'updatedAtAttribute' => false],
            ['class' => BlameableBehavior::className(), 'updatedByAttribute' => false],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['member_id', 'acct_month'], 'required'],
            [['remarks', 'license_nbr'], 'safe'],
            [['created_at', 'created_by'], 'integer'],
            [['member_id'], 'string', 'max' => 11],
            [['acct_month'], 'string', 'max' => 6],
            [['member_id'], 'exist', 'skipOnError' => true, 'targetClass' => Member::className(), 'targetAttribute' => ['member_id' => 'member_id']],
            [['license_nbr'], 'exist', 'skipOnError' => true, 'targetClass' => Contractor::className(), 'targetAttribute' => ['license_nbr' => 'license_nbr']],
            [['license_nbr'], 'default', 'value' => null],
            [['doc_id'], 'string', 'max' => 20],
            [['doc_file'], 'file', 'checkExtensionByMimeType' => false, 'extensions' => 'pdf, png, jpg'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'member_id' => 'Member ID',
            'acct_month' => 'Period',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'doc_id' => 'Doc',
            'total_hours' => 'Total Hours',
            'remarks' => 'Remarks',
            'license_nbr' => 'Contractor',
        ];
    }

    public function popTotalHours()
    {
        $this->total_hours = $this->getTotalHours();
        $this->save();
    }

    /**
     * @return ActiveQuery
     */
    public function getMember()
    {
        return $this->hasOne(Member::className(), ['member_id' => 'member_id']);
    }

    /**
     * @return ActiveQuery
     */
    public function getContractor()
    {
        return $this->hasOne(Contractor::className(), ['license_nbr' => 'license_nbr']);
    }

    /**
     * @return ActiveQuery
     */
    public function getWorkHour()
    {
        return $this->hasMany(WorkHour::className(), ['timesheet_id' => 'id']);
    }

    /**
     * @return mixed
     */
    public function getTotalHours()
    {
        return $this->hasMany(WorkHour::className(), ['timesheet_id' => 'id'])->sum('hours');
    }

    public function getUnusedProcesses()
    {
        $options = $this->member->getProcOptions();
        $procs = $this->workHour;
        foreach ($procs as $proc) {
            unset($options[$proc->wp_seq]);
        }

        return $options;
    }

    /**
     * @return string
     */
    public function getAcctMonthText()
    {
        return OptionHelper::getPrettyMonthYear($this->acct_month);
    }

    /**
     * @param OpDate|null $base_dt
     * @return array
     */
    public function getAcctMonthOptions(OpDate $base_dt = null)
    {
        if (!isset($base_dt)) {
            $base_dt = new OpDate();
        }
        return OpDate::getMonthsList($base_dt, 6);
    }

    /**
     * @return ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::className(), ['id' => 'created_by']);
    }

    public function getEnteredBy()
    {
        return $this->createdBy->username . ' on ' . date('m/d/Y h:i a', $this->created_at);
    }

    /**
     * Override this function when testing with fixed date
     *
     * @return OpDate
     */
    protected function getToday()
    {
        return new OpDate();
    }

}
