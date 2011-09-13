<?php
/**
 * login.php  -  duplicate validate.php
 *
 * include all nessesary files and libs:
 * config.php,
 * libs/session_handler.php,
 * libs/common.php,
 * libs/xtpl.php,
 * libs/class.xxPage_Selector.php
 *
 * define most of all global variables: $xtpl, $act, $pages, $sort
 *
 * made user authorization check - if not - redirect to the login page
 * begin session start
 */

error_reporting  (E_ERROR | E_WARNING | E_PARSE);

//includes
require_once "config.php";
include_once("libs/session_handler.php");
//session start
session_start();
require_once "libs/common.php";
require_once "libs/xtpl.php";
$xtpl = new XTemplate("xtpl/login.html");
$xtpl->assign("MAIN_HOST", MAIN_HOST);

//user authentication check
if(array_key_exists("user_login", $_SESSION))
{
    $location = (isset($_SESSION['prev_link'])) ? $_SESSION['prev_link']:"account.php";
    header("Location: $location");
    exit;
}

// setup global $act variable
if(array_key_exists("act", $_REQUEST)) $act = (string) AddSlashes(trim($_REQUEST["act"]));
else $act = "";

//authentication block
if((!array_key_exists("user_login", $_SESSION))&&($act=="login"))
{
    if(array_key_exists("login", $_REQUEST))
    {
        $login = preg_replace('/[^0-9a-zA-Z\_\-]/','',StripSlashes($_REQUEST['login']));
    }else $login = "";
    if(array_key_exists("password", $_REQUEST))
    {
        $password = preg_replace('/[^0-9a-zA-Z\_\-]/','',StripSlashes(rawurldecode($_REQUEST['password'])));
    }else $password = "";
    list($user_id, $permission) = check_user($login, $password);
    if($user_id)
    {
        $location = (isset($_SESSION['prev_link'])) ? $_SESSION['prev_link']:"account.php";
        $_SESSION = array();
        $_SESSION['uid'] = $user_id;
        $_SESSION['user_login'] = $login;
        $_SESSION['user_password'] = $password;
        $_SESSION['permission'] = $permission;
        $query = "UPDATE " . T_USERS . " SET last_login=now() WHERE id=" . $user_id;
        SQL_request($query);
        $query = "INSERT INTO " . T_LOGIN_HISTORY . " (user_id, last_login) VALUES (" . $user_id . ", now())";
        SQL_request($query);
        header("Location: $location");
        exit;
    }else $xtpl->assign("LOGIN", $login);
}

$xtpl->parse("main");
$xtpl->out("main");
?>