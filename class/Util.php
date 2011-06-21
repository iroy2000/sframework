<?php
class Util {
    /**
     * Redirect
     * @param <string> $url
     */
    public static function redirect($url) {
        header('Location: '.$url);
        exit();
    }

    /**
     * Write to a file
     * @param <string> $fullpath
     * @param <string> $contents
     */
    public static function write($fullpath, $contents) {
        $fp = fopen($fullpath, 'a'); // open file for appending

        $delim = '\r\n===\r\n';

        $current = $delim.date('m-d-Y m:i:s').$delim;

        if(is_array($contents)) {
            foreach($contents as $content) {
                fwrite($fp, $content . '\r\n');
            }
        } else {
            fwrite($fp, $contents . '\r\n');
        }

        fclose($fp);
    }

    // detect links and make links linkable
    public static function linkify($text) {
        $text= preg_replace("/(^|[\n ])([\w]*?)((ht|f)tp(s)?:\/\/[\w]+[^ \,\"\n\r\t<]*)/is", "$1$2<a href=\"$3\" >$3</a>", $text);
        $text= preg_replace("/(^|[\n ])([\w]*?)((www|ftp)\.[^ \,\"\t\n\r<]*)/is", "$1$2<a href=\"http://$3\" >$3</a>", $text);
        $text= preg_replace("/(^|[\n ])([a-z0-9&\-_\.]+?)@([\w\-]+\.([\w\-\.]+)+)/i", "$1<a href=\"mailto:$2@$3\">$2@$3</a>", $text);
        return($text);
    }

    public static function getLink($link = null) {
        if(Globals::getConfig()->server->modrewrite->support == 0) {
            return "?v0=".$link;
        }

        return $link;
    }

    /**
     * Mail Util
     * @param <string> $from
     * @param <string> $to
     * @param <string> $subject
     * @param <string> $body
     * @return <PHPMailer> $mailer
     */
    static public function mail($from = null, $to = null, $subject = null, $body = null) {
        try {
            $mailer = new PHPMailer();
            $mailer->IsSMTP();
            $mailer->Host     = Globals::getConfig()->mail->host;
            $mailer->SMTPAuth = TRUE;
            $mailer->Username = Globals::getConfig()->mail->username;
            $mailer->Password = Globals::getConfig()->mail->password;
            $mailer->From     = Globals::getConfig()->mail->username;
            $mailer->FromName = $from; // This is the from name in the email, you can put anything you like here
            $mailer->Body     = $body;
            $mailer->Subject  = $subject;

            foreach($to as $address) {
                $mailer->AddAddress($address);
            }

            if(!$mailer->Send()) {
                Logger::logMessage(Globals::getMessage()->send->email->error);
                //echo "Mailer Error: " . $mailer->ErrorInfo;
            }else {
                Logger::logMessage(Globals::getMessage()->send->email->success);
                //echo "Message has been sent";
            }
            return $mailer;
        } catch(Exception $e) {
            return null;
        }
    }

}
