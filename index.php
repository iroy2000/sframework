<?php
require_once('main.php');
Base::dispatcher(new Router($request))->dispatch();

?>