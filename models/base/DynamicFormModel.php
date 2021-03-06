<?php
namespace app\models\base;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * Special extension to Model adds a static function to populate an array of models 
 */
class DynamicFormModel extends \yii\base\Model
{
	/**
	 * Creates and populates a set of models.
	 *
	 * @param string $modelClass
	 * @param array $multipleModels
	 * @param array $data
	 * @return array
	 */
	public static function createMultiple($modelClass, $multipleModels = [], $data = null)
	{
		$model    = new $modelClass;
		$formName = $model->formName();
		$post     = empty($data) ? Yii::$app->request->post($formName) : $data[$formName];
		$models   = [];

		if (! empty($multipleModels)) {
			$keys = array_keys(ArrayHelper::map($multipleModels, 'id', 'id'));
			$multipleModels = array_combine($keys, $multipleModels);
		}

		if ($post && is_array($post)) {
			foreach ($post as $i => $item) {
				if (isset($item['id']) && !empty($item['id']) && isset($multipleModels[$item['id']])) {
					$models[] = $multipleModels[$item['id']];
				} else {
					$models[] = new $modelClass;
				}
			}
		}

		unset($model, $formName, $post);

		return $models;
	}
}
