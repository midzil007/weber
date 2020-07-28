<?
class cms_Loader extends Zend_Loader
{
    public static function loadClass($class, $dirs = null)
    {
        parent::loadClass($class, $dirs);
    }

    public static function autoload($class)
    {
        try {
            self::loadClass($class);
            return $class;
        } catch (Exception $e) {
            return false;
        }	
    }
} 
  
// Zend_Loader::registerAutoload('cms_Loader');  
Zend_Loader::registerAutoload('cms_Loader');  