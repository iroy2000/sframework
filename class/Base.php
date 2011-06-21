<?php
Loader::loadFile('Globals.php','config', true);
/**
 * Base class saves or processes application level data
 * It maintains user session and also process and pass user request into controller
 * @author  Roy Yu
 */
class Base {
    /**
     * Maintain User Object Session, please read Model/User
     *
     * @param User $user
     */
    public static function saveUser(User $user) {
        $_SESSION['user'] = $user;
    }

    /**
     * Fetch User Object, please read Model/User
     *
     * @return $session of User object
     */
    public static function getUser() {
        return $_SESSION['user'];
    }

    /**
     * Return Controller base on User request
     *
     * @return Controller
     */
    public static function dispatcher(Router $router = null) {
        //retrieve only, won't save, please see class::FileCache
        FileCache::getFileCacheInstance($router, 3600)->init(false, true);


        $defaultController = Globals::getConfig()->controller->default .
                Globals::getConfig()->controller->suffix;

        if($router == null) {
            Loader::loadFile($defaultController . Globals::getConfig()->view->extension,
                    Globals::getConfig()->controller->folder, true);
            return new $defaultController($router);
        }

        if(trim($router->getController()) != '') {
            try {
                $className = $router->getController(true);
                Loader::loadFile($router->getController(true) . Globals::getConfig()->view->extension,
                        Globals::getConfig()->controller->folder, true);
                return new $className($router);
            } catch(Exception $e) {
                Loader::loadFile($defaultController . Globals::getConfig()->view->extension,
                        Globals::getConfig()->controller->folder, true);
                return new $defaultController($router);
            }
        } else {
            Loader::loadFile($defaultController . Globals::getConfig()->view->extension,
                    Globals::getConfig()->controller->folder, true);
            return new $defaultController($router);
        }
    }

}