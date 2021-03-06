<?php

namespace app\components\behaviors;

use Yii;
use yii\base\Behavior;
use yii\base\Exception;
use yii\web\UploadedFile;

/**
 * Handles file uploads and deletions.  Should be attached to model that manages the ID
 * of the uploaded
 *
 * NOTE:  May have to define
 * 
 * @author jmdemoor
 */
class OpImageBehavior extends Behavior
{
	/**
	 * @var String Directory (subfolder) where images are stored
	 */
	public $doc_dir = 'docDir';
	/**
	 * @var String Generated ID of the uploaded file
	 */
	public $doc_id = 'doc_id';
	/**
	 * @var String Name of the variable in the model that stages the file to upload
	 */
	public $doc_file = 'doc_file';

    /**
     * Fetch document file name with complete path (FQDN)
     *
     * @return null|string
     */
	public function getImagePath()
	{
		$doc_id = $this->doc_id;
		$path = Yii::getAlias('@webroot') . Yii::$app->params[$this->doc_dir];
		return isset($this->owner->$doc_id) ? $path . $this->owner->$doc_id : null;
	}
	
	/**
	 * Fetch stored document URL
	 *
	 * @return string
	 */
	public function getImageUrl()
	{
		$doc_id = $this->doc_id;
		$path =  Yii::$app->urlManager->baseUrl . Yii::$app->params[$this->doc_dir];
		return isset($this->owner->$doc_id) ? $path . $this->owner->$doc_id : null;
	}

    /**
     * Process upload of image
     *
     * @return boolean|UploadedFile the uploaded image instance
     * @throws Exception
     */
	public function uploadImage()
	{
	    // $this->owner is normally a controller, not a model, so inspection is bypassed
        /** @noinspection PhpParamsInspection */
        $image = UploadedFile::getInstance($this->owner, $this->doc_file);
		if (empty($image)) {
			return false;
		}
	
		// generate a unique file name for storage
		$doc_id = $this->doc_id;
		$parts = explode(".", $image->name);
		$ext = end($parts);
        if ($ext =='jpeg')
            $ext = 'jpg';
		$this->owner->$doc_id = Yii::$app->security->generateRandomString(16).".{$ext}";
	
		return $image;
	}
	
	/**
	 * Process deletion of image
	 *
	 * @return boolean the status of deletion
	 */
	public function deleteImage()
	{
		$file = $this->getImagePath();
	
		// check if file exists on server
		if (empty($file) || !file_exists($file)) {
			return false;
		}
	
		// check if uploaded file can be deleted on server
		if (!unlink($file)) {
			return false;
		}
	
		// if deletion successful, reset your file attributes
		$doc_id = $this->doc_id;
		$this->owner->$doc_id = null;
	
		return true;
	}
	
}