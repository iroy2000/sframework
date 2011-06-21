<?php
// This one doesn't quite work here
require_once('nusoap/nusoap.php');

class Soap {

    private static $_server = null;
    private static $_client = null;

    public static function getServer() {
        if(self::$_server != null)
            return self::$_server;

        try {
            self::$_server = new soap_server;
            return self::$_server;

        } catch(Exception $e) {
            return null;
        }
    }

    public static function getClient($uri = null) {
        if(self::$_client != null)
            return self::$_client;

        if($uri == null)
            return null;

        try {
            self::$_client = new soapclient($uri);
            return self::$_client;

        } catch(Exception $e) {
            return null;
        }
    }
}
?>