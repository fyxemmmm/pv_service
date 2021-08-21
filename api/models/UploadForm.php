<?php

namespace api\models;

use yii\base\Model;
use yii\web\UploadedFile;

class UploadForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $uploadFile;

    public function rules()
    {
        return [
            [['uploadFile'], 'file', 'skipOnEmpty' => false],
        ];
    }

    public function upload()
    {
        if ($this->validate()) {
            if (!file_exists('uploads')) {
                mkdir ("uploads");
            }

            $file = 'uploads/' . $this->uploadFile->baseName . '.' . $this->uploadFile->extension;
            $this->uploadFile->saveAs($file);
            return $file;
        } else {
            return false;
        }
    }
}