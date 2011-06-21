<?php
/**
 * A centralize place for Global Settings
 *
 * @author Roy Yu
 */
class Globals {
    private static $_db = null;
    private static $_config = null;
    private static $_memcache = null;
    private static $_mongo = null;
    private static $_solr = null;

    static public function getDBInstance() {
        if(self::$_db != null)
            return self::$_db;
        
        try {
            self::$_db = new PDO(self::getConfig()->db->dns,
                    self::getConfig()->db->username,
                    self::getConfig()->db->password,
                    array(PDO::ATTR_EMULATE_PREPARES => true));


            return self::$_db;

        } catch (PDOException $pe) {
            return null;
        } catch(Exception $e) {
            return null;
        }
    }

    static public function getMongoInstance() {
        if(self::$_mongo != null)
            return self::$_mongo;

        try {
            self::$_mongo = new Mongo();

            return self::$_mongo;

        } catch (MongoException $me) {
            var_dump($me);
            return null;
        } catch(Exception $e) {
            return null;
        }
    }

    static public function getMemcache() {
        if(self::$_memcache != null)
            return self::$_memcache;

        try {
            self::$_memcache = new Memcache();
            self::$_memcache->connect(self::getConfig()->memcache->host, 11211);

            return self::$_memcache;
        } catch (Exception $e) {
            return null;
        }
    }

    static public function getConfig() {
        if(self::$_config != null)
            return self::$_config;
        try {
            self::$_config = new Config_Ini(
                dirname(__FILE__) . DIRECTORY_SEPARATOR . 'config.ini',
                'default', true
            );

            return self::$_config;

        } catch (Zend_Exception $e) {
            return null;
        }
    }

    static public function getMessage() {
        if(self::$_config != null)
            return self::$_config;
        try {
            self::$_config = new Config_Ini(
                dirname(__FILE__) . DIRECTORY_SEPARATOR . 'message.ini',
                'default', true
            );

            return self::$_config;

        } catch (Zend_Exception $e) {
            return null;
        }
    }
}
?>