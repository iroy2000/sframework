<?php
class MessageDAO {

    private static $_db = null;

    public static $getAllComment           = 'select * from `comments`';
    public static $getAllMessage           = 'select *, m.id as id from `messages` m inner join `users` u on m.user_id = u.id order by m.created_at desc limit 10';
    public static $getMessageById          = 'select * from `messages` where id = ?';
    public static $getCommentByMessageId   = 'select * from `comments` c inner join `users` u on c.user_id = u.id where message_id = ?';
    public static $getMessageCommentCount  = 'select message_id, count(message_id) as total from `comments` group by message_id';
    public static $getEmailByAttributes    = 'select * from users where attributes = ?';
    public static $getTwaEmail             = 'select email from users where twa = 1';


    static public function getDBInstance() {
        if(self::$_db != null) 
            return self::$_db;

        try {
            self::$_db = new PDO(Globals::getConfig()->db2->dns,
                    Globals::getConfig()->db->username,
                    Globals::getConfig()->db->password,
                    array(PDO::ATTR_EMULATE_PREPARES => true));


            return self::$_db;

        } catch (PDOException $pe) {
            //die(print_r($pe));
            return null;
        } catch(Exception $e) {
            //die(print_r($e));
            return null;
        }
    }

    public static function createUser() {

        $query = "
                    SELECT *
                    FROM `phpbb_users`
                    WHERE `user_email` != ''
		 ";

        $db = Globals::getDBInstance();

        $stmt  = $db->prepare($query);

        if($stmt->execute()) {
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $results = array();
        }

        if(count($results) > 0) {
            foreach($results as $result) {
                self::insertUser($result);
            }
        }

        return false;
    }

    public static function fetchUser($email = null) {

        if($email == null)
            return null;

        $query = "
                    select * from `phpbb_users`
                    where `user_email` = ?
		 ";

        $db = Globals::getDBInstance();

        $stmt  = $db->prepare($query);

        $stmt->bindParam(1, $email);

        if($stmt->execute()) {
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $result = array();
        }

        return $result;
    }

    public static function createOneUser($emailHash = null) {

        if($emailHash == null)
            return null;

        $query = "
                    select * from `phpbb_users`
                    where `user_email_hash` = ?
		 ";

        $db = Globals::getDBInstance();

        $stmt  = $db->prepare($query);

        $stmt->bindParam(1, $emailHash);

        if($stmt->execute()) {
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $results = array();
        }

        if(count($results) > 0) {
            self::insertUser($result);
        }
        self::auth($emailHash);
    }

    public static function updateTWAFlag($email = null, $flag = 1) {
        if($email == null) {
            return false;
        }

        $query = "
                    update users set twa = ? where email = ?
		 ";

        $db = self::getDBInstance();

        $stmt  = $db->prepare($query);

        $stmt->bindParam(1, $flag);
        $stmt->bindParam(2, $email);

        if($stmt->execute()) {
            return true;
        } else {
            return false;
        }
    }

    public static function insertUser($result = null) {

        if($result == null)
            return false;

        $user = self::auth($result['user_email_hash']);

        if(isset($user['id']))
            return false;

        $query = '
                    INSERT INTO
			users (`name`, `email`, `email_hash`, `created_at`)	
                    VALUES (?, ?, ?, NOW())';

        $db = self::getDBInstance();

        $stmt  = $db->prepare($query);

        $stmt->bindParam(1, $result['username_clean']);
        $stmt->bindParam(2, $result['user_email']);
        $stmt->bindParam(3, $result['user_email_hash']);

        if($stmt->execute()) {
            Globals::getMemcache()->flush();
            //return true;
        } else {
            return false;
        }
    }

    public static function auth($emailHash = null) {

        if($emailHash == null)
            return null;

        $query = "
                    select * from `users`
                    where `email_hash` = ?
		 ";

        $md5 = $query . $emailHash;

        $db = self::getDBInstance();

        $result = DBUtil::executeCustomDb($db, $query, $emailHash, $md5, false, 0, 0);

        return $result;

    }

    public static function authByEmail($email = null) {

        if($email == null)
            return null;

        $query = "
                    select * from `users`
                    where `email` = ?
		 ";

        $result = self::run($query, $email);

        return $result;

    }

    public static function saveMessage($result) {

        if($result == null)
            return false;

        if(trim($result['content']) == '' || trim($result['title']) == '' ) {
            Logger::getInstance()->logMessage('error', 'Blank Contents');
            return false;
        }

        $query = '
                    INSERT INTO
			messages (`user_id`, `title`, `content`, `created_at`)
                    VALUES ( ?, ?, ?, NOW())
		';				 			 

        $db = self::getDBInstance();

        $stmt  = $db->prepare($query);

        $stmt->bindParam(1, $result['user_id'], PDO::PARAM_INT);
        $stmt->bindParam(2, $result['title']);
        $stmt->bindParam(3, $result['content']);

        if($stmt->execute()) {
            FileCache::deleteByReferrer();
            FileCache::deleteByPath('messageArray');
            Logger::getInstance()->destroyMessage('error');
            Globals::getMemcache()->flush();
            //return true;
        } else {
            return false;
        }
    }

    public static function saveComment($result) {

        if($result == null)
            return false;

        if(trim($result['comment']) == '') {
            Logger::getInstance()->logMessage('error', 'Blank Comment.');
            return false;
        }

        $query = '
                    INSERT INTO
			`comments` (`user_id`, `message_id`, `comment`, `created_at`)
                    VALUES (?, ?, ?, NOW())
		 ';

        $db = self::getDBInstance();

        $stmt  = $db->prepare($query);

        $stmt->bindParam(1, $result['user_id'], PDO::PARAM_INT);
        $stmt->bindParam(2, $result['message_id'], PDO::PARAM_INT);
        $stmt->bindParam(3, $result['comment']);
        try {
            if($stmt->execute()) {
                FileCache::deleteByReferrer();
                FileCache::deleteByPath('messageArray');
                Logger::getInstance()->destroyMessage('error');
                Globals::getMemcache()->flush();
                //return true;
            } else {
                //die(print_r($stmt->errorInfo()));
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }


    static public function run($query = null, $param = null) {
        try {

            $db = self::getDBInstance();

            $stmt  = $db->prepare($query);

            if($param != null) {
                if(is_array($param)) {
                    foreach($params as $key => $value) {
                        $stmt->bindParam($key, $value);
                    }
                } else {
                    $stmt->bindParam(1, $param);
                }
            }

            if($stmt->execute()) {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return $result;
            } else {
                //print_r($stmt->errorInfo());
            }

            return null;

        } catch(Exception $e) {
            //print_r($e);
            return null;
        }
    }
}
?>