<?php
namespace PHPMVC\LIB;

class Language
{
    private $_dictionary = [];

    public function load($path)
    {
        $defaultLanguage = APP_DEFAULT_LANGUAGE;
        if(isset($_SESSION['lang'])) {
            $defaultLanguage = $_SESSION['lang'];
        }
        $pathArray = explode('.', $path);
        $languageFileToLoad = LANGUAGE_PATH . $defaultLanguage . DS .$pathArray[0] . DS .$pathArray[1] . '.lang.php';
        if(file_exists($languageFileToLoad)){
            require $languageFileToLoad;
            if(is_array($_) && !empty($_)){
                foreach ($_ as $key => $value){
                    $this->_dictionary[$key] = $value;
                }
            }
        } else {
            trigger_error('Sorry the Lang file( ' . $path . ' )doesn\'t exist' , E_USER_WARNING);
        }
    }

    public function get($key)
    {
        if(array_key_exists($key, $this->_dictionary)){
            return $this->_dictionary[$key];
        }
    }

    public function feedKey($key, $data)
    {
        if(array_key_exists($key, $this->_dictionary)){
            array_unshift($data ,$this->_dictionary[$key]);
            return call_user_func_array('sprintf', $data);
        }
    }

    public function getDictionary()
    {
        return $this->_dictionary;
    }
}