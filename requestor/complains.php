<?php
include "validator.php";
if(array_key_exists("searching_parameters", $_SESSION)) unset($_SESSION["searching_parameters"]);

$xtpl->assign("SECTION_NAME", "Search");

    $_SESSION["searching_parameters"] = array(

                                               "owner" => 0,
                                               "owner_cond" => "appear",
                                               "email" =>"",
                                               "email_cond" => "contain",
                                               "subject" =>"",
                                               "subj_cond" => "contain",
                                               "queue" => Array
                                                   (
                                                   ),
                                               "queue_cond" => "appear",
                                               "status" => Array
                                                   (
                                                       "0" => "new",
                                                       "1" => "opened"
                                                   ),
                                               "status_cond" => "appear",
                                               "complain" => 1,
                                               "rate" => "-",
                                               "cc_member_id" =>"",
                                               "older_than" => 1,
                                               "older_than_d" => ""
                                              );
//  print "<pre>";
//  print "<a href='tickets.php'>see search result</a>";
//  print_r($_SESSION["searching_parameters"]);
//  print "</pre>";
    header("Location: tickets.php");
    exit;
?>