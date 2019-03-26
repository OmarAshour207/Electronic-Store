<?php
namespace PHPMVC\LIB;


class FileUpload
{
    private $name;
    private $tmpPath;
    private $type;
    private $size;
    private $error;

    private $fileExtension;

    private $allowedExtension = [
        'jpg', 'png', 'gif', 'pdf', 'xls', 'doc', 'docx' , 'jpeg'
    ];

    private $uploadErrors = [
        '1' => 'The uploaded file exceeds the ' . MAX_FILE_SIZE_ALLOWED . '.',
        '2' => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
        '3' => 'The uploaded file was only partially uploaded.',
        '4' => 'No file was uploaded.',
        '6' => 'Missing a temporary folder.',
        '7' => 'Failed to write file to disk.',
        '8' => 'A PHP extension stopped the file upload.'
    ];

    public function __construct(array $file)
    {
        $this->name     = $file['name'];
        $this->tmpPath  = $file['tmp_name'];
        $this->type     = $file['type'];
        $this->size     = $file['size'];
        $this->error    = $file['error'];
        $this->name();
    }

    private function name()
    {
        preg_match_all(('/([a-z]{1,4})$/i'), $this->name, $m);
        $this->fileExtension = $m[0][0];
        $name = substr(strtolower(base64_encode($this->name . APP_SALT)),0 , 30);
        $name = preg_replace('/(\w{6})/i','$1_' ,$name);
        $name = rtrim($name , '_');
        $this->name = $name;
        return $name;
    }

    private function isAllowedType()
    {
        return in_array($this->fileExtension, $this->allowedExtension);
    }

    private function isSizeNotAcceptable()
    {
        preg_match_all('/(\d+)([MG])$/i', MAX_FILE_SIZE_ALLOWED, $matches);
        $maxFileSizeToUpload = $matches[1][0];
        $sizeUnit = $matches[2][0];
        $currentFileSize = ($sizeUnit == 'M') ? ($this->size / 1024 / 1024) : ($this->size / 1024 / 1024 / 1024) ;
        $currentFileSize = ceil($currentFileSize);
        return (int) $currentFileSize > (int) $maxFileSizeToUpload;
    }

    private function isImage()
    {
        return preg_match('/image/i', $this->type);
    }

    public function getFileName()
    {
        return $this->name . '.' . $this->fileExtension;
    }

    public function upload()
    {
        if($this->error != 0) {
            throw new \Exception($this->uploadErrors[$this->error]);
        } elseif(!$this->isAllowedType()){
            throw new \Exception('Sorry files of type ' . $this->type . ' are not allowed');
        } elseif ($this->isSizeNotAcceptable()) {
            throw new \Exception('Sorry the file size is exceeds the maximum allowed size ' . MAX_FILE_SIZE_ALLOWED);
       } else {
            $storageFolder = $this->isImage() ? IMAGE_UPLOAD_STORAGE : DOCUMENTS_UPLOAD_STORAGE;
            if(is_writable($storageFolder)) {
                move_uploaded_file($this->tmpPath , $storageFolder . DS . $this->getFileName());
            } else {
                trigger_error('Sorry the destination folder is not writable', E_USER_WARNING);
            }
        }
        return $this;
    }

}