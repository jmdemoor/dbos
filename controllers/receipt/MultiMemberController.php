<?php

namespace app\controllers\receipt;

use app\models\accounting\Document;
use Yii;
use app\models\accounting\ResponsibleEmployer;
use app\models\accounting\StagedAllocation;
use app\models\accounting\StagedAllocationSearch;
use app\models\accounting\AllocatedMemberSearch;
use app\models\accounting\Receipt;
use app\models\accounting\ReceiptMultiMember;
use yii\base\InvalidConfigException;
use yii\data\ActiveDataProvider;
use yii\db\Exception;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class MultiMemberController
 * @package app\controllers\receipt
 *
 * Note that this controller should NOT be instantiated.  Views referenced are in folders corresponding to concrete
 * controller classes
 *
 */
class MultiMemberController extends BaseController
{

    /**
     * Displays a single Receipt model.
     * @param integer $id
     * @return string|Response
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $this->storeReturnUrl();
        $model = $this->findModel($id);

        /* @var $model ReceiptMultiMember */
        if ($model->isUpdating())
            return $this->redirect([
                'update',
                'id' => $model->id,
            ]);

        if($model->outOfBalance != 0.00)
            return $this->redirect([
                'itemize',
                'id' => $model->id,
                'fee_types' => $model->feeTypesArray,
            ]);

        $query = Document::find()->where(['receipt_id' => $model->id])->orderBy('doc_type asc');
        $docProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 5],
            'sort' => false,
        ]);

        $searchMemb = new AllocatedMemberSearch(['receipt_id' => $id]);
        /** @noinspection PhpUndefinedMethodInspection */
        $membProvider = $searchMemb->search(Yii::$app->request->queryParams);

        /** @noinspection MissedViewInspection */
        return $this->render('view', compact('model', 'docProvider', 'membProvider', 'searchMemb'));
    }

    /**
     * @param $id
     * @param array $fee_types
     * @return string
     * @throws NotFoundHttpException
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function actionItemize($id, array $fee_types = [])
    {
        $this->storeReturnUrl();
        $modelReceipt = $this->findModel($id);
        StagedAllocation::makeTable($modelReceipt, $fee_types);
        $searchAlloc = new StagedAllocationSearch(['receipt_id' => $id]);
        /** @noinspection PhpUndefinedMethodInspection */
        $allocProvider = $searchAlloc->search(Yii::$app->request->queryParams);

        /** @noinspection MissedViewInspection */
        return $this->render('itemize', [
            'modelReceipt' => $modelReceipt,
            'searchAlloc' => $searchAlloc,
            'allocProvider' => $allocProvider,
        ]);
    }

    public function actionUpdate($id)
    {
        $searchMemb = new AllocatedMemberSearch(['receipt_id' => $id]);
        $this->config['searchMemb'] = $searchMemb;
        /** @noinspection PhpUndefinedMethodInspection */
        $this->config['membProvider'] = $searchMemb->search(Yii::$app->request->queryParams);

        return parent::actionUpdate($id);
    }

    /**
     * Finds the Receipt model based on its primary key value.
     *
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Receipt the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function findModel($id)
    {
        $model = parent::findModel($id);
        /* @var $model ReceiptMultiMember */
        $model->responsible = ResponsibleEmployer::findOne(['receipt_id' => $id]);
        return $model;
    }

}

