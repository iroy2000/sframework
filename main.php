<?php
session_start();
require_once('class/Loader.php');
require_once('dao/DBLoader.php');
require_once('model/ModelLoader.php');

Loader::registerAutoload();
DBLoader::registerAutoload();
ModelLoader::registerAutoload();

//It nullify the v0 value 
HttpSecurity::execute();

$request = htmlspecialchars($_REQUEST['v0']);

// Any initialize put here

?>