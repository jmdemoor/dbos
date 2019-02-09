<?php

namespace app\controllers;

use app\models\accounting\DuesRateFinder;
use app\models\member\Standing;
use app\models\member\Status;
use Yii;
use yii\data\ActiveDataProvider;
use app\controllers\receipt\BaseController;
use app\models\accounting\Receipt;
use app\models\accounting\ReceiptMember;
use app\models\accounting\ReceiptMemberSearch;
use app\models\accounting\AllocatedMember;
use app\models\accounting\BaseAllocation;
use app\models\accounting\AllocationBuilder;
use app\models\member\Member;
use app\models\accounting\CcOtherLocal;
use yii\data\SqlDataProvider;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;

class ReceiptMemberController extends BaseController
{
    /**
     * Displays a single Receipt model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
    	$model = $this->findModel($id);

    	/* @var $model ReceiptMember */
    	if ($model->isUpdating())
    	    return $this->redirect([
    	        'update',
                'id' => $model->id,
            ]);

        if($model->outOfBalance != 0.00)
            return $this->redirect([
                'itemize',
                'id' => $model->id,
            ]);

        $allocProvider = $this->buildAllocProvider($id);
    	 
    	return $this->render('view', compact('model', 'allocProvider'));
    }

    /**
     *
     * @param $lob_cd
     * @param null $id
     * @return string
     * @throws \Exception
     * @throws \yii\db\Exception
     */
	public function actionCreate($lob_cd, $id = null)
	{
		$model = new ReceiptMember(['scenario' => Receipt::SCENARIO_CREATE]);
		if (!isset($lob_cd))
			throw new \Exception('lob_cd is required');
		$model->lob_cd = $lob_cd;
		
		$modelMember = new AllocatedMember();
		if (isset($id))
			$modelMember->member_id = $id;

		if ($model->load(Yii::$app->request->post()) && $modelMember->load(Yii::$app->request->post())) {
			$model->payor_type = Receipt::PAYOR_MEMBER;
			if (empty($model->payor_nm)) 
				$model->payor_nm = Member::findOne($modelMember->member_id)->fullName;
				
			$transaction = \Yii::$app->db->beginTransaction();
			try {
				if ($model->save(false)) {
					$modelMember->receipt_id = $model->id;
					if (!$modelMember->save())
						throw new \Exception("Error when trying to stage Allocated Member `{$modelMember->errors}`");
					$builder = new AllocationBuilder();
					$result = $builder->prepareAllocs($modelMember, $model->fee_types);
					if ($result != true)
						throw new \Exception('Uncaught validation errors: ' . $result);
					if ($model->other_local > 0) {
						$modelNextLocal = new CcOtherLocal([
								'alloc_memb_id' => $modelMember->id,
								'other_local' => $model->other_local,
						]);
						if (!$modelNextLocal->save())
							throw new \Exception("Error when trying to stage Receiving Local `{$modelNextLocal->errors}`");
					}
					$transaction->commit();
					return $this->redirect(['itemize', 'id' => $model->id]); 
				}
				$transaction->rollBack();
			} catch (\Exception $e) {
				$transaction->rollBack();
				throw new \Exception('Error when trying to save created Receipt: ' . $e);
			}
		} 
		
		$this->initCreate($model);

		if (!isset($model->received_amt) && isset($modelMember->member_id)) {
		    $member = $modelMember->member;
            if (isset($member->currentStatus) && isset($member->currentClass)) {
                $standing = new Standing(['member' => $member]);
                $balance = $standing->totalAssessmentBalance - $member->overage;
                if ($member->currentStatus->member_status != Status::OUTOFSTATE) {
                    $rate_finder = new DuesRateFinder($member->currentStatus->lob_cd, $member->currentClass->rate_class);
                    $balance += $standing->getDuesBalance($rate_finder);
                }
                $model->received_amt = number_format($balance, 2);
            }
        }

		if (Yii::$app->request->isAjax)
			return $this->renderAjax('create', [
					'model' => $model,
					'modelMember' => $modelMember,
			]);
				
		return $this->render('create', [
				'model' => $model,
				'modelMember' => $modelMember,
		]);
		
	}

    /**
     * @param $id integer Receipt ID
     * @return string
     * @throws NotFoundHttpException
     */
	public function actionItemize($id)
	{
		$this->storeReturnUrl();
		$modelReceipt = $this->findModel($id);
		$allocProvider = $this->buildAllocProvider($id);
		return $this->render('itemize', [
				'modelReceipt' => $modelReceipt,
				'allocProvider' => $allocProvider,
		]);
	}

	public function actionUpdate($id)
    {
        // Assume 1 allocated member on a member receipt
        $this->config['allocProvider'] = $this->buildAllocProvider($id);

        return parent::actionUpdate($id);
    }

    public function actionSummaryAjax($id)
	{
		$searchModel = new ReceiptMemberSearch();
		$searchModel->member_id = $id;
        /** @noinspection PhpUndefinedMethodInspection */
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
		return $this->renderAjax('_summary', [
				'dataProvider' => $dataProvider,
				'searchModel' => $searchModel,
				'payorPicklist' => Receipt::getPayorOptions(),
		]);
	}

    /**
     * @param $member_id
     * @throws \yii\db\Exception
     */
	public function actionSummFlattenedJson($member_id)
    {
        if (!Yii::$app->user->can('browseReceipt')) {
            echo Json::encode($this->renderAjax('/partials/_deniedview'));
        } else {

            $typesSubmitted = ReceiptMember::getFeeTypesSubmitted($member_id);

            $count = Yii::$app->db->createCommand(
                'SELECT COUNT(*) FROM AllocatedMembers WHERE member_id = :member_id',
                [':member_id' => $member_id]
            )->queryScalar();

            $sqlProvider = new SqlDataProvider([
                'sql' => ReceiptMember::getFlattenedReceiptsByMemberSql($typesSubmitted),
                'params' => [':member_id' => $member_id],
                'totalCount' => $count,
                'pagination' => [
                    'pageSize' => 10,
                ],
            ]);

            echo Json::encode($this->renderAjax('_summflattened', [
                'sqlProvider' => $sqlProvider,
                'typesSubmitted' => $typesSubmitted,
            ]));
        }
    }

	protected function buildAllocProvider($id)
	{
		$query = BaseAllocation::find()->joinWith(['allocatedMember'])->where(['receipt_id' => $id])->orderBy('fee_type');
		return new ActiveDataProvider(['query' => $query]);		
	}
}