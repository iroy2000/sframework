<?php
/**
 * Provide extra static function for queries to reduce redundancy
 *
 * @author  Roy Yu
 */
class DBUtil {
    /**
     * Use this function if an array is expected, else it will return null
     *
     * @param DataBase $db
     * @param Resources $result
     * @return array of data
     */
    static public function fetchArray($db, $result = null) {

        if($result == null)
            return null;

        try {
            return $db->fetch_array($result);

        } catch (Exception $e) {
            return null;
        }
        return null;
    }

    /**
     * Use this function if you want to fetch the first result from an array
     * It will return null otherwise
     *
     * @param DataBase $db
     * @param Resources $result
     * @return data
     */
    static public function fetchFirst($db, $result = null) {

        // A smaple of how to use DBUtil::fetchAt()
        return DBUtil::fetchAt($db, $result, 0);
    }

    /**
     * Use this function if you want to fetch any particular arbitrary result from an array
     * It will return null otherwise
     *
     * @param DataBase $db
     * @param Resources $result
     * @return data
     */
    static public function fetchAt($db, $result = null, $num = 0) {

        if($result == null)
            return null;

        try {
            $ret = DBUtil::fetchArray($db, $result);

            return $ret[$num];

        } catch (Exception $e) {
            return null;
        }
        return null;
    }

    /**
     * This function wraps all the repetitve steps on prepare, execute and fetching
     * @param String $query
     * @param Array $param
     * @param Boolean $fetchAll
     * @return Array $result
     */
    static public function executeCustomDb($db = null, $query = null, $param = null, $md5 = null, $fetchAll = false, $compress = 0, $expire = 5) {

        try {

            if($result = Globals::getMemcache()->get(md5($md5))) {
                return $result;
            }

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

            if(@$stmt->execute()) {

                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if($fetchAll) {
                    Globals::getMemcache()->set(md5($md5), $result, $compress, $expire);
                    return $result;
                }

                Globals::getMemcache()->set(md5($md5), $result[0], $compress, $expire);
                return $result[0];
            }

            Globals::getMemcache()->set(md5($md5), null, $compress, $expire);
            return null;

        } catch(Exception $e) {
            return null;
        }
    }

    /**
     * This function wraps all the repetitve steps on prepare, execute and fetching
     * @param String $query
     * @param Array $param
     * @param Boolean $fetchAll
     * @return Array $result
     */
    static public function execute($query = null, $param = null, $md5 = null, $fetchAll = false, $compress = 0, $expire = 5) {

        try {

            if($result = Globals::getMemcache()->get(md5($md5))) {
                return $result;
            }

            $db = Globals::getDBInstance();

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

            if(@$stmt->execute()) {
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if($fetchAll) {
                    Globals::getMemcache()->set(md5($md5), $result, $compress, $expire);
                    return $result;
                }

                Globals::getMemcache()->set(md5($md5), $result[0], $compress, $expire);
                return $result[0];
            }

            Globals::getMemcache()->set(md5($md5), null, $compress, $expire);
            return null;

        } catch(Exception $e) {
            return null;
        }
    }

}
?>