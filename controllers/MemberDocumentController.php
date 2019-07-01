<?php

namespace app\controllers;

use Yii;
use yii\helpers\Json;
use app\controllers\document\BaseController;


class MemberDocumentController extends BaseController
{
    public $recordClass = 'app\models\member\Document';

	public function actionSummaryJson($id)
	{
        if (!Yii::$app->user->can('browseMemberExt'))
            echo Json::encode($this->renderAjax('/partials/_deniedview'));
        else
            parent::actionSummaryJson($id);
	}

}