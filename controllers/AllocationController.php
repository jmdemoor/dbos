<?php

namespace app\controllers;

use app\models\accounting\AllocatedMember;
use Exception;
use ReflectionClass;
use Yii;
use app\models\accounting\DuesAllocation;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\db\StaleObjectException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use app\models\accounting\BaseAllocation;
use app\modules\admin\models\FeeType;
use yii\web\Response;

class AllocationController extends Controller
{
    /**
     * @param $alloc_memb_id
     * @return string|Response
     * @throws Exception
     */
	public function actionCreate($alloc_memb_id)
	{
		$model = new BaseAllocation();
		
		if ($model->load(Yii::$app->request->post())) {
			$model->alloc_memb_id = $alloc_memb_id;
			if ($model->save()) {
				return $this->goBack();
			}
			throw new Exception	('Problem with post.  Errors: ' . print_r($model->errors, true));
		}

		$receipt = AllocatedMember::findOne($alloc_memb_id)->receipt;
		$feeOptions = $receipt->feeOptions;
		return $this->renderAjax('create', compact('model', 'feeOptions'));
	}

    /**
     * @throws NotFoundHttpException
     * @throws Exception
     */
	public function actionEditAlloc()
	{
		if(Yii::$app->request->post('hasEditable')) {
			$id = Yii::$app->request->post('editableKey');
			$model = $this->findModel($id);
			$class = (new ReflectionClass(get_class($model)))->getShortName();

			// $posted is the posted data for StagedAllocation without any indexes
			$posted = current($_POST[$class]);
			// $post is the converted array for single model validation
			$post = [$class => $posted];
			$message = '';
		
			if ($model->load($post)) {

			    /* @var $model BaseAllocation */
			    if (in_array($model->fee_type, $model->statusGenerators) && ($model->allocation_amt != $model->oldAttributes['allocation_amt'])) {
			        $model->backOutMemberStatus();
                    if ($model instanceof DuesAllocation) {
                        /* @var $model DuesAllocation */
                        $model->backOutDuesThru(true);
                    }
                }

				if ($model->save()) {
			
					$output = Yii::$app->formatter->asDecimal($model->allocation_amt, 2);
					return $this->asJson(['output' => $output, 'message' => $message]);
				}
			}
			throw new Exception ('Problem with post. Errors: ' . print_r($model->errors, true));
		}
		return null;
	}
	
	public function actionSummaryAjax()
	{
		
		$alloc_query = BaseAllocation::find();
		$alloc_query->where(['alloc_memb_id' => $_POST['expandRowKey']])
			  		->andWhere(['!=', 'fee_type', FeeType::TYPE_DUES])
			  		->andWhere(['!=', 'fee_type', FeeType::TYPE_HOURS])
			  		;
		$allocProvider = new ActiveDataProvider(['query' => $alloc_query]);
		
		$dues_query = BaseAllocation::find();
		$dues_query->where([
				'alloc_memb_id' => $_POST['expandRowKey'],
				'fee_type' => FeeType::TYPE_DUES,
		]);		
		$duesProvider = new ActiveDataProvider(['query' => $dues_query]);
		
		$hrs_query = BaseAllocation::find();
		$hrs_query->where([
				'alloc_memb_id' => $_POST['expandRowKey'],
				'fee_type' => FeeType::TYPE_HOURS,
		]);		
		$hrsProvider = new ActiveDataProvider(['query' => $hrs_query]);
		
		return $this->renderAjax('_summary', [
				'allocProvider' => $allocProvider,
				'duesProvider' => $duesProvider,
				'hrsProvider' => $hrsProvider,
		]);
		
	}

	public function actionUpdateGridAjax()
    {
        $alloc_memb_id = $_POST['expandRowKey'];
        $query = BaseAllocation::find()->where(['alloc_memb_id' => $_POST['expandRowKey']])->orderBy('fee_type');
        $allocProvider = new ActiveDataProvider(['query' => $query]);

        return $this->renderAjax('../receipt/_allocgrid', [
            'allocProvider' => $allocProvider,
            'alloc_memb_id' => $alloc_memb_id,
        ]);
    }
	
	public function actionDetailAjax()
	{
		$model = DuesAllocation::findOne(['id' => $_POST['expandRowKey']]);
		return $this->renderAjax('_detail', ['duesProvider' => $model]);
	}

    /**
     * Deletes an existing ActiveRecord model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws StaleObjectException
     */
	public function actionDelete($id)
	{
		$this->findModel($id)->delete();
	
		return $this->goBack();
	}

    /**
     * Finds the ActiveRecord model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return ActiveRecord the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
	protected function findModel($id)
	{
	    $model = BaseAllocation::find()->where(['id' => $id])->one();
		if (!$model) {
			throw new NotFoundHttpException('The requested page does not exist.');
		}
		return $model;
	}
	
	
}
