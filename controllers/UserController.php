<?php

namespace app\controllers;

use Yii;
use app\models\user\User;
use app\models\user\UserSearch;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
{
    public function behaviors()
    {
        return [
	            'verbs' => [
	                'class' => VerbFilter::className(),
	                'actions' => [
	                    'delete' => ['post'],
	                ],
	            ],
        		'access' => [
        				'class' => AccessControl::className(),
        				'only' => ['index', 'create', 'view', 'delete'],
        				'rules' => [
        						[
        								'allow' => true,
        								'actions' => ['index', 'view', 'default-pw'],
        								'roles' => ['assignRole'],
        						],
        						[
        								'allow' => true,
        								'actions' => ['create', 'reset-pw'],
        								'roles' => ['updateUser'],
        						],
        						[
        								'allow' => true,
        								'actions' => ['delete'],
        								'roles' => ['deleteUser'],
        						],
        				],
        		],
        		
        		
        ];
    }

    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        Yii::$app->user->returnUrl = Yii::$app->request->url;
    	$model = $this->findModel($id);
    	if (Yii::$app->user->can('assignRole', ['user' => $model])) {
    		$rolesModel = new ActiveDataProvider([
    				'query' => $model->getAssignments(),
    		]);
        	return $this->render('view', [
        			'model' => $model,
        			'rolesModel' => $rolesModel,
         	]);
    	}
    	throw new ForbiddenHttpException("You are not allowed to view this record ({$model->id})");
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new User(['scenario' => User::SCENARIO_CREATE]);

        if ($model->load(Yii::$app->request->post()) && $model->save()) 
            return $this->redirect(['view', 'id' => $model->id]);
        $model->password_clear = User::RESET_USER_PW;
        $model->role = 10;
        if (!isset($model->status))
         	$model->status = User::STATUS_ACTIVE;
        return $this->render('create', ['model' => $model]);
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

    	if (Yii::$app->user->can('updateUser', ['user' => $model])) {
	        if ($model->load(Yii::$app->request->post()) && $model->save()) 
	            return $this->redirect(['view', 'id' => $model->id]);
	        return $this->render('update', ['model' => $model]);
        }
    	throw new ForbiddenHttpException("You are not allowed to perform this action ({$model->id})");
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * @return string|\yii\web\Response
     * @throws \yii\base\Exception
     */
    public function actionResetPw()
    {
		$this->layout = 'login';
		/* @var $model User */
    	$model = Yii::$app->user->identity;

	    $model->scenario = User::SCENARIO_CHANGE_PW;
	    if ($model->load(Yii::$app->request->post()) && $model->validate()) {
	    	$model->setPassword($model->password_new);
	    	$model->save(false);
	    	Yii::$app->session->addFlash('success', "Successfully changed password.  Please logout and back in.");
	    	return $this->goBack();
	    }
	    return $this->render('change-pw', ['model' => $model]);
    }

    /**
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \yii\base\Exception
     */
    public function actionDefaultPw($id)
    {
    	$model = $this->findModel($id);
    	$model->setPassword(User::RESET_USER_PW);
    	$model->save(false);
    	Yii::$app->session->addFlash('success', "Successfully set password to system default.");
    	return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * List builder for user pickllist.  Builds JSON encoded array:
     * ['results'] key provides progressive results. If `id` is provided,
     *               then this key provides the `id` and full name
     *
     * @param string|array $search Criteria used.
     * @param null $role
     * @param string $id Selected user's `id`
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionUserList($search = null, $role = null, $id = null)
    {
    	\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
    	$out = ['results' => ['id' => '', 'text' => '']];
    	if (!is_null($search)) {
    		$condition = (is_null($role)) ? $search : ['full_nm' => $search, 'role' => $role];
    		$data = User::listAll($condition);
    		$out['results'] = array_values($data);
    	}
    	elseif (!is_null($id) && ($id <> 0)) {
    		$out['results'] = ['id' => $id, 'text' => User::findOne($id)->fullName];
    	}
    	return $out;
    }    

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
