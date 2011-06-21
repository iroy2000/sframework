<?php
require_once('interface/Cache.php');
/**
 * FileCache
 * Caching page as a file
 * The cache should reflect the request
 * controller_action_id_extra.html
 * @author ryu
 *
 */
class FileCache implements Cache {
    
    protected $_request  = null;
    //protected $_filename = null;
    protected $_fullpath = null;
    protected $_ttl      = null;
    protected $_save     = false;

    public static $cache = null;

    public function __construct() {

    }

    public function setRequest(Router $request = null) {
        $this->_request = $request;
        return $this;
    }

    public function setTTL($ttl = null) {
        if($ttl == null)
            $this->_ttl = Globals::getConfig()->filecache->ttl;
        else
            $this->_ttl = $ttl;

        return $this;
    }

    public function getRequest() {
        return $this->_request;
    }

    public function getTTL() {
        return $this->_ttl;
    }

    public static function getFileCacheInstance(Router $request = null, $ttl = null) {
        if(self::$cache != null) {
            self::$cache->setRequest($request)->setTTL($ttl);
            return self::$cache;
        }

        try {
            self::$cache = new FileCache();
            self::$cache->setRequest($request)->setTTL($ttl);
            return self::$cache;
        }catch(Exception $e) {
            return null;
        }
    }

    public static function getFullPath($request) {
        if($request == null) {
            $filename = 'index.html';
        } else {
            $filename = md5($request->getController().
                    $request->getAction().
                    $request->getId().
                    $request->getExtra()).'.html';
        }

        return Globals::getConfig()->filecache->dir . $filename;
    }

    // getExtra return Array and this is just a hack
    public static function deleteByReferrer() {
        $fn = implode('', Router::getReferer());
        $filename = Globals::getConfig()->filecache->dir . md5($fn.'Array').'.html';
        self::unlink($filename);
    }

    public static function deleteByPath($path) {

        $filename = Globals::getConfig()->filecache->dir . md5($path).'.html';

        self::unlink($filename);
    }

    public function init($save = false, $retrieveOnly = false) {
        if(Globals::getConfig()->cache->activate == 0) {
            $save = false;
            $retrieveOnly = false;
        }

        $this->_fullpath = self::getFullPath($this->getRequest());

        $this->_save = $save;

        if($save) {
            if($this->isExisted() && !$this->isExpired()) {
                $this->retrieve();
            } elseif(!$this->isExisted()) {
                $this->create();
            } elseif($this->isExpired()) {
                $this->update();
            }
        }

        if($retrieveOnly) {
            if($this->isExisted() && !$this->isExpired()) {
                $this->retrieve();
            }
        }

        return $this;
    }

    public function isExpired() {
        return (time() - $this->_ttl >= filemtime($this->_fullpath));
    }

    public function isExisted() {
        return file_exists($this->_fullpath);
    }

    public function create() {
        ob_start('ob_gzhandler');
    }

    public function update() {
        $this->create();
    }

    public function retrieve() {
        include_once($this->_fullpath);
        exit;
    }

    public function delete() {
        self::unlink($this->_fullpath);
    }

    public static function unlink($fullpath = null) {
        if($fullpath != null && file_exists($fullpath)) {
            unlink($fullpath);
        }
    }

    public function ignoreList() {

    }

    public function save() {
        if($this->_save) {
            $fp = fopen($this->_fullpath, 'w'); // open the cache file for writing
            fwrite($fp, ob_get_contents()); // save the contents of output buffer to the file
            fclose($fp);
            ob_end_flush();
        }
    }
}

?>