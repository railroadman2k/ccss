<?php
/**
 * search_tickets.php - form page for search tickets
 *
 * ticket search handler and search results form by tickets.php
 * include validator.php
 */

include "validator.php";
if(array_key_exists("searching_parameters", $_SESSION)) unset($_SESSION["searching_parameters"]);

$xtpl->assign("SECTION_NAME", "Search");

if($act == "search")
{
    $_SESSION["searching_parameters"] = array(
                           "owner"=>$_REQUEST["owner"],
                           "owner_cond"=>$_REQUEST["owner_cond"],
                           "email"=>AddSlashes(trim(StripSlashes($_REQUEST["email"]))),
                           "email_cond"=>$_REQUEST["email_cond"],
                           "subject"=>AddSlashes(trim(StripSlashes(rawurldecode($_REQUEST["subject"])))),
                           "subj_cond"=>$_REQUEST["subj_cond"],
                           "queue"=>(array_key_exists("queue", $_REQUEST)?$_REQUEST["queue"]:array()),
                           "queue_cond"=>$_REQUEST["queue_cond"],
                           "status"=>(array_key_exists("status", $_REQUEST)?$_REQUEST["status"]:array()),
                           "status_cond"=>$_REQUEST["status_cond"],
                           "complain"=>(array_key_exists("complain", $_REQUEST)?$_REQUEST["complain"]:'-1'),
                           "rate"=>$_REQUEST["rate"],
                           "cc_member_id"=>$_REQUEST["cc_member_id"],
                           "older_than"=>$_REQUEST["older_than"],
                           "older_than_d"=>$_REQUEST["older_than_d"]
                                           );
 
    header("Location: tickets.php");
    exit;
}

$cond1 = array("appear"=>"appear", "not appear"=>"not appear");
$cond2 = array("contain"=>"contain","not contain"=>"not contain","appear"=>"appear", "not appear"=>"not appear");

$query = "SELECT id, login FROM " . T_USERS . " WHERE access=1 ORDER BY login";
$rows = SQL_select($query);
$users = array();
foreach($rows as $val) $users[$val["id"]] = $val["login"];

$xtpl->assign("OWNERS", get_drop_down_list($users, "owner", "", "-"));
$xtpl->assign("CONDITION1", get_drop_down_list($cond1, "owner_cond"));
$xtpl->assign("CONDITION2", get_drop_down_list($cond2, "email_cond"));
$xtpl->assign("CONDITION3", get_drop_down_list($cond2, "subj_cond"));
$query = "SELECT DISTINCT g.id, g.name
            FROM " . T_USERS_GROUPS . " as ug, " . T_GROUPS . " as g
           WHERE ug.user_id=" . $_SESSION["uid"] . "
             AND ug.group_id=g.id";
$rows = SQL_select($query);
$queues = array();
foreach($rows as $val) $queues[$val["id"]] = $val["name"];

$xtpl->assign("QUEUES", get_check_boxs($queues, "queue", "", 3));
$xtpl->assign("CONDITION4", get_drop_down_list($cond1, "queue_cond"));
$xtpl->assign("STATUS", get_check_boxs(array(), "status", T_TICKETS, 4));
$xtpl->assign("CONDITION5", get_drop_down_list($cond1, "status_cond"));
//$xtpl->assign("COMPLAIN", "complain or not");
//$xtpl->assign("RATE", "");

get_queues_status($xtpl);

$xtpl->parse("main.search_tickets");
$xtpl->parse("main");
$xtpl->out("main");
?>