<?php

namespace app\controllers;

use app\controllers\base\SummaryController;
use app\models\contractor\Contractor;
use Throwable;
use Yii;
use app\models\member\Member;
use app\models\employment\Employment;
use yii\db\Exception;
use yii\db\StaleObjectException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * EmploymentController implements the CRUD actions for Employment model.
 */
class EmploymentController extends SummaryController
{
	public $recordClass = 'app\models\employment\Employment';
	public $relationAttribute = 'member_id';

    /**
     * Overrides summary controller action to process image attachment
     *
     * (non-PHPdoc)
     * @param $relation_id
     * @return string|Response
     * @see \app\controllers\base\SubmodelController::actionCreate()
     */
    public function actionCreate($relation_id)
    {
        $model = new Employment;
        
        // Loan has its own action
		$model->is_loaned = 'F';
        if ($model->load(Yii::$app->request->post())) {
	        // Prepopulate referencing column
	        $model->member_id = $relation_id;
        	if	($model->save()) {
                return $this->goBack();
        	}
        } 
        return $this->renderAjax('create', compact('model'));

    }

    /**
     * @param int $id
     * @return void
     * @throws NotFoundHttpException
     */
	public function actionUpdate($id)
	{
		throw new NotFoundHttpException('Non-supported feature.  Cannot update employment this way.');
	}
	
	public function actionSummaryJson($id)
	{
		$member = Member::findOne($id);
        $employer = 'Unemployed';
        $curr_effective_dt = null;
		if (isset($member->employer)) {
		    $employer = $member->employer->descrip;
		    $curr_effective_dt = $member->employer->effective_dt;
        }
		$this->viewParams = [
		    'employer' => $employer,
            'curr_effective_dt' => $curr_effective_dt,
        ];
		return parent::actionSummaryJson($id);
	}

    /**
     * @param $member_id
     * @param $effective_dt
     * @return Response
     * @throws NotFoundHttpException
     */
	public function actionResume($member_id, $effective_dt)
    {
        $model = $this->findByDate($member_id, $effective_dt);

        if($model !== null) {
            $model->end_dt = null;
            $model->term_reason = null;

            if ($model->save()) {
                Yii::$app->session->addFlash('success', "Employment resumed");
            } else {
                Yii::$app->session->addFlash('error', "Could not update {$this->getBasename()}. Check log for details. Code `EC010`");
                Yii::error("*** EC010 Employment save error.  Messages: " . print_r($model->errors, true));
            }

            return $this->goBack();
        }
        throw new NotFoundHttpException('Unable to locate employment record.');

    }

	public function actionEdit($member_id, $effective_dt)
	{
		$model = $this->findByDate($member_id, $effective_dt);
		
        if ($model->load(Yii::$app->request->post())) {

        	if	($model->save()) {
    			Yii::$app->session->addFlash('success', "{$this->getBasename()} entry updated");
        		return $this->goBack();
        	}
        } 
        		
        return $this->render('edit', compact('model'));
	}

    /**
     * Replaces the inherited controller actionDelete with a different signature
     *
     * @param string $member_id
     * @param string $effective_dt
     * @return Response
     * @throws StaleObjectException
     * @throws NotFoundHttpException  If the model is not found
     * @throws Throwable
     */
	public function actionRemove($member_id, $effective_dt)
	{
		$model = $this->findByDate($member_id, $effective_dt);
		if ($model !== null) {

		    if (!empty($model->documents)) {
                Yii::$app->session->addFlash('error', "Cannot delete {$this->getBasename()}. Record has attached documents.");
                return $this->goBack();
            }
			
			$removing_current = $model->end_dt == null;

        	$model->delete();

        	if ($removing_current)
        		 Employment::openLatest($member_id);
			
        	Yii::$app->session->addFlash('success', "{$this->getBasename()} entry deleted");
			return $this->goBack();
		}
		throw new NotFoundHttpException('Unable to locate employment record.');
	}
	
	public function actionLoan($relation_id)
	{
		/** @var Employment $model */
		$model = new $this->recordClass;
		// Prepopulate referencing column
		$model->{$this->relationAttribute} = $relation_id;
		// Assumptions
		$model->employer = $this->findCurrent($relation_id)->employer;
		$model->is_loaned = 'T';
		if ($model->load(Yii::$app->request->post()) && $model->save()) {
			Yii::$app->session->addFlash('success', "{$this->getBasename()} Loan entry created");
			return $this->goBack();
		}
		
		return $this->renderAjax('loan', compact('model'));
	}
	
	public function actionTerminate($relation_id)
	{
		$model = $this->findCurrent($relation_id);
		if ($model->load(Yii::$app->request->post()) && $model->save()) {
			return $this->goBack();
		}
    	$termReasonOptions = Employment::getTermReasonOptions();
		return $this->renderAjax('terminate', compact('model', 'termReasonOptions'));
	}

    /**
     * List builder for employee pickllist.  Builds JSON encoded array:
     * ['results'] key provides progressive results. If a member_id is provided,
     *               then this key provides the member_id and member's full name
     *
     * @param string|array $search Criteria used.
     * @param null $employer
     * @param string $member_id Selected member's member_id
     * @return Response
     * @throws Exception
     */
	public function actionEmployeeList($search = null, $employer = null, $member_id = null)
	{
		$out = ['results' => ['id' => '', 'text' => '']];
		if (!is_null($search)) {
			$condition = (is_null($employer)) ? $search : ['full_nm' => $search, 'employer' => $employer];
			$data = Employment::listEmployees($condition);
			$out['results'] = array_values($data);
		}
		elseif (!is_null($member_id) && ($member_id <> '0')) {
			$out['results'] = ['member_id' => $member_id, 'text' => Member::findOne($member_id)->fullName];
		}
		return $this->asJson($out);
	}

	public function actionPrint9a($license_nbr)
    {
        $this->layout = 'noheadreport';

        $contractorModel = Contractor::findOne($license_nbr);

        return $this->render('/employment/doc9a-preview', [
            'contractorModel' => $contractorModel,
        ]);
    }
	
	protected function findCurrent($id)
	{
		return Employment::findCurrentEmployer($id);
	}
	
	protected function findByDate($id, $dt)
	{
		return Employment::findEmployerByDate($id, $dt);
	}
	
}
