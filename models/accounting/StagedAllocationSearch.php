<?php

namespace app\models\accounting;

use yii\base\Model;

/**
 * StagedAllocationSearch represents the model behind the search form about `app\models\accounting\StagedAllocation`.
 */
class StagedAllocationSearch extends StagedAllocation
{
	
	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
				[['alloc_memb_id'], 'integer'],
				[['member_id', 'classification', 'fullName', 'reportId'], 'safe'],
		];
	}
	
	/**
	 * @inheritdoc
	 */
	public function scenarios()
	{
		// bypass scenarios() implementation in the parent class
		return Model::scenarios();
	}
	
    /**
     * Builds search data provider
     *
     * @see \yii\base\Component::behaviors()
     */
	public function behaviors()
	{
        /** @noinspection PhpUnnecessaryFullyQualifiedNameInspection */
        return [
		    [
		    	'class' => \app\components\behaviors\OpAllocatedMemberSearchBehavior::className(),
		    	'recordClass' => StagedAllocation::className(),
		    ],
		];
	}
	
}
