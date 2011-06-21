<?php
class MessageController extends Controller {

    public function setup() {
        $this->setAllowActions(array('default','show', 'insert', 'create'));
        $this->setView(Globals::getConfig()->template->path . 'index', null);
    }

    public function defaultAction() {

        $m = Logger::getInstance()->getCurrentMessage('error');

        $c = Cookies::getInstance();
        $cookie = $c->getCookie(Globals::getConfig()->cookie->auth->name, true);

        $user = null;

        if(trim($cookie) != '' || $cookie != null) {
            $user = unserialize($cookie);
        }

        $messages = MessageDAO::run(MessageDAO::$getAllMessage);
        $comments = MessageDAO::run(MessageDAO::$getMessageCommentCount);

        $map = array();

        foreach($comments as $comment) {
            $map[$comment['message_id']] = $comment['total'];
        }

        $this->getView()->setViewObject('user', $user)
                ->setViewObject('error', $m)
                ->setViewObject('map', $map)
                ->setViewObject('messages', $messages);
    }

    public function createAction() {
        MessageDAO::createUser();
    }

    public function showAction() {

        $user = null;

        $m = Logger::getInstance()->getCurrentMessage('error');

        $c = Cookies::getInstance();
        $cookie = $c->getCookie(Globals::getConfig()->cookie->auth->name, true);

        if(trim($cookie) != '' || $cookie != null) {
            $user = unserialize($cookie);
        }

        $messages = MessageDAO::run(MessageDAO::$getMessageById, $this->getRouter()->getId());

        if(count($messages) == 0) {
            Util::redirect(Util::getLink('/message'));
        }

        $comments = MessageDAO::run(MessageDAO::$getCommentByMessageId, $this->getRouter()->getId());

        $this->getView()->setViewObject('user', $user)
                ->setViewObject('comments', $comments)
                ->setViewObject('error', $m)
                ->setViewObject('messages', $messages);


    }

    public function insertAction() {
        $post = Router::getPost();

        $c = Cookies::getInstance();
        $cookie = $c->getCookie(Globals::getConfig()->cookie->auth->name, true);

        if(trim($cookie) != '' || $cookie != null) {
            $user = unserialize($cookie);
        }

        $token = $post['_form_secret'];

        // avoid multiple submit
        if(isset($_SESSION['FORM_SECRET'])) {
            if(strcasecmp($token ,$_SESSION['FORM_SECRET'])===0) {
                unset($_SESSION['FORM_SECRET']);

                if($post['_submit'] == 'discussion') {

                    $param = array(
                            'user_id' => $user['id'],
                            'title' => $post['title'],
                            'content' => $post['content']
                    );

                    $id = MessageDAO::saveMessage($param);

                    Util::redirect(Util::getLink('/message'));

                } elseif($post['_submit'] == 'comment') {

                    $param = array(
                            'user_id' => $user['id'],
                            'message_id' => $post['message_id'],
                            'comment' => $post['comment']
                    );

                    $id = MessageDAO::saveComment($param);
                   
                    Util::redirect(Util::getLink('/message/show/').$post['message_id']);
                }
            }
        }

        Util::redirect('/message');
    }

}