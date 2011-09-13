<?php
/**
 * validator.php  - the header of almost all php files
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

require_once "config.php";

	



include_once("libs/session_handler.php");
//begin session start
session_start();

if (isset($_REQUEST['int']))
	{
		$_SESSION['internal']="true";
	}
require_once "libs/common.php";
require_once "libs/xtpl.php";
require_once "libs/class.xxPage_Selector.php";

$xtpl = new XTemplate("xtpl/main.html");

$xtpl->assign("MAIN_HOST", MAIN_HOST);

//global variables definitions
if(array_key_exists("act", $_REQUEST)) $act = (string) AddSlashes(trim($_REQUEST["act"]));
else $act = "";
if(array_key_exists("pages", $_REQUEST)) $pages = (int) $_REQUEST["pages"];
else $pages = 0;
if(array_key_exists("sort", $_REQUEST)) $sort = (array) $_REQUEST["sort"];
else $sort = array();
if(!isset($valid_info)) $valid_info = -1;

//user authorization block
if(array_key_exists("user_login", $_SESSION))
{
    $member_login = preg_replace('/[^0-9a-zA-Z\_\-]/','',StripSlashes($_SESSION['user_login']));
    if(array_key_exists("user_password", $_SESSION))
       $member_password = preg_replace('/[^0-9a-zA-Z\_\-]/','',StripSlashes($_SESSION['user_password']));
    else
       $member_password = "";
    list($user_id, $permission) = check_user($member_login, $member_password);
    if(!$user_id)
    {
        header("Location: logout.php");
        exit;
    }
    
    
    
    if(array_key_exists("showmanagers",$_REQUEST))
    {
    	
    	$_SESSION['showmanagers']=$_REQUEST["showmanagers"];
    }
    	
    
     if(array_key_exists("showinternal",$_REQUEST)) 
     
    {
    	
    	$_SESSION['showinternal']=$_REQUEST["showinternal"];
    }
    	
    if($main_menu)
    {
        $top_menu = "";
        foreach($main_menu[$_SESSION['permission']] as $key=>$val) $top_menu .= "<a href=\"" . MAIN_HOST . $val . "\" class=\"topmenu\">" . $key . "</a>&nbsp;&nbsp;";
        $xtpl->assign("MAIN_MENU", $top_menu);
    }
    $xtpl->assign("NAME_OF_USER", $_SESSION['user_login']);
    $query = "SELECT count(DISTINCT u.id) as num_users
                FROM " . T_USERS . " as u
          INNER JOIN " . T_SESSION . " as s ON u.id=s.uid";
    $row = SQL_select($query, 0);
    $query = "SELECT count(id) as num_users FROM " . T_USERS;
    $row1 = SQL_select($query, 0);

    //-- ticket menu section
    if(array_key_exists("ticket_id", $_REQUEST))
    {
        $quick_ticket_id = $_REQUEST["ticket_id"];
    }elseif(array_key_exists("selected_ticket_id", $_SESSION))
    {
        $quick_ticket_id = $_SESSION["selected_ticket_id"];
    }else $quick_ticket_id = 0;

    $prev_ticket_id  = $quick_ticket_id-1;
    $next_ticket_id  = $quick_ticket_id+1;

    $xtpl->assign("TICKET_NAV", "<form action='/show_ticket.php' id='quick_ticket_frm' style='display:inline;'><a href='/show_ticket.php?ticket_id={$prev_ticket_id}'>prev</a>&nbsp;<input type='text' name='ticket_id' class='input1' size='7' value='$quick_ticket_id' style='text-align:center'><!--<input type='submit' value='>' style='width:10px;'>-->&nbsp;<a href='/show_ticket.php?ticket_id={$next_ticket_id}'>next</a></form>");
    //ticket menu section--
    //  $xtpl->assign("USERS_STATUS", "<img src=\"" . MAIN_HOST . "images/on-line.gif\" align=\"absmiddle\" alt=\"On-line\"><span class=\"whitetext2\">" . $row["num_users"] . "</span>&nbsp;&nbsp;&nbsp;<img src=\"" . MAIN_HOST . "images/off-line.gif\" align=\"absmiddle\" alt=\"Off-line\"><span class=\"whitetext2\">" . $row1["num_users"] . "</span>");//replaced by ticket menu

    if(array_key_exists("website", $_REQUEST))
    {
        $_SESSION["website_url"] = $_REQUEST["website"];
    }elseif(!array_key_exists("website_url", $_SESSION))
    {
        $def_website = get_enum(T_TICKETS, "website");
        $_SESSION["website_url"] = $def_website[0];
    }
    $xtpl->assign("WEBSITE", get_drop_down_list(array(), "website", T_TICKETS, "", 0, $_SESSION["website_url"], "onchange=\"document.getElementById('website_form').submit();\""));
    
    	if ($myDB=="ccss")
        	{
        		$xtpl->assign("CURRENT", "External");
        		$link=$_SERVER['REQUEST_URL'];
        		$link .="?int=true";
        		$xtpl->assign("LINKS",$link);
        		
        	}
        	else
        	{
        		$link=$_SERVER['REQUEST_URL'];
        		$link="?ext=true";
        		$xtpl->assign("LINKS",$link);
        		$xtpl->assign("CURRENT","Internal");
        	}
}

if(!array_key_exists("user_login", $_SESSION))
{
    $_SESSION['prev_link'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit;
}

?>