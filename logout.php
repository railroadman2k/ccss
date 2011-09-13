<?php
/**
 * login.php  -  redirect to login.php
 */

error_reporting  (E_ERROR | E_WARNING | E_PARSE);

require_once "config.php";
include_once("libs/session_handler.php");
//session start - duplicated in login.php
session_start();
require_once "libs/common.php";
$_SESSION = array();

header("Location: login.php");
?>