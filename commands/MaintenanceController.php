<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\base\InvalidParamException;
use app\components\utilities\OpDate;
use app\models\member\Member;

class MaintenanceController extends Controller
{
	CONST STAGE_TABLE_NM = 'StagedMemberSuspends';
	CONST CLOSE_PREV_TABLE_NM = 'StageClosePrevious';
	
	public function actionSuspend($today = null)
	{
		Yii::info('Starting monthly suspend run');
		$effective_dt = new OpDate();
		if (isset ($today))
			$effective_dt->setFromMySql($today);
		$effective_dt->setToMonthBegin();
		$cutoff_dt = clone $effective_dt;
		$cutoff_dt->modify('-' . Member::MONTHS_DELINQUENT . ' month');
		$cutoff_dt->setToMonthEnd();

		$db = Yii::$app->db;
		$drop_stage_cmd = $db->createCommand('DROP TABLE IF EXISTS ' . self::CLOSE_PREV_TABLE_NM);
		$drop_close_prev_cmd = $db->createCommand('DROP TABLE IF EXISTS ' . self::STAGE_TABLE_NM);	
		
		// Just in case
		$drop_close_prev_cmd->execute();
		$drop_stage_cmd->execute();
		
		try {
			$db->createCommand($this->stageSuspSql())
			   ->bindValues([':effective_dt' => $effective_dt->getMySqlDate(), ':cutoff_dt' => $cutoff_dt->getMySqlDate()])
			   ->execute();	
			$count = $db->createCommand($this->insertStatusSql())->execute();
			Yii::info("Members suspended as of {$effective_dt->getDisplayDate()}: {$count}");
			$db->createCommand($this->stagePrevCloseSql())->execute();
			$db->createCommand($this->updateStatusSql())->execute();
			$drop_close_prev_cmd->execute();
			$drop_stage_cmd->execute();
			Yii::info('Monthly suspend run completed');
		} catch (yii\db\Exception $e) {
			Yii::error('*** MC020 Problem with a suspend DB action. Messages: ' . print_r($e->getMessage(), true));
		}
					
	}

	private function stageSuspSql()
	{
		return "  CREATE TABLE " . self::STAGE_TABLE_NM . " AS "
			  ."    SELECT "
			  ."        Me.member_id, "
			  ."        :effective_dt AS effective_dt,  "
			  ."        MS.lob_cd, "
			  ."        'S' AS member_status, "
			  ."        'Dues over " . Member::MONTHS_DELINQUENT  . " months delinquent' AS reason   "
			  ."      FROM Members AS Me "
			  ."        JOIN MemberStatuses AS MS ON MS.member_id = Me.member_id "
			  ."                                   AND MS.member_status = 'A' "
			  ."                                   AND MS.end_dt IS NULL "
			  ."      WHERE dues_paid_thru_dt < :cutoff_dt ;"
		;
	}
	
	private function insertStatusSql()
	{
		return " INSERT INTO dc50.MemberStatuses (member_id, effective_dt, lob_cd, member_status, reason) "
			  ."   SELECT distinct member_id, effective_dt, lob_cd, member_status, reason "
			  ."     FROM " . self::STAGE_TABLE_NM . ";"
		;
		
	}
	
	private function stagePrevCloseSql()
	{
		return "CREATE TABLE " . self::CLOSE_PREV_TABLE_NM . " AS " 
			  ."  SELECT "
			  ."      (@row_number:=@row_number + 1) AS stat_nbr, "
			  ."      MS.member_id, "
			  ."      MS.effective_dt "
			  ."    FROM dc50.MemberStatuses AS MS, (SELECT @row_number:=0) AS t "
			  ."    WHERE MS.end_dt IS NULL "
			  ."      AND MS.member_id IN (SELECT DISTINCT member_id FROM " . self::STAGE_TABLE_NM . ") "
			  ."  ORDER BY MS.member_id, MS.effective_dt;"
		;	
	}
	
	private function updateStatusSql()
	{
		return "UPDATE dc50.MemberStatuses AS MS "
			  ."  JOIN " . self::CLOSE_PREV_TABLE_NM . " AS B ON MS.member_id = B.member_id AND MS.effective_dt = B.effective_dt "
			  ."    LEFT OUTER JOIN StageClosePrevious AS N ON N.member_id = B.member_id AND N.stat_nbr = B.stat_nbr + 1 "
			  ."  SET MS.end_dt = DATE_ADD(N.effective_dt, INTERVAL -1 DAY) "
		      ."  WHERE N.effective_dt IS NOT NULL; "
		;
	}
	
}
