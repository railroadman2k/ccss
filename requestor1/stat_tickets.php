<?php
/**
 * stat_tickets.php  - show selected tickets
 *
 * duplicate of tickets.php
 * include validator.php
 */

include "validator.php";

//from dizel
if(array_key_exists("searching_parameters", $_SESSION)) unset($_SESSION["searching_parameters"]);
$cond1 = array("appear"=>"appear", "not appear"=>"not appear");
$cond2 = array("contain"=>"contain","not contain"=>"not contain","appear"=>"appear", "not appear"=>"not appear");



//query formation
if (isset($_GET['user']))
{
  $r=get_queries($_GET['type'].'_for1day_u');
  $r=replace_queries($r,array("#year#","#month#","#day#","#user#"),array($_GET['year'],$_GET['month'],$_GET['day'],$_GET['user']));
  $xtpl->assign("SECTION_NAME", "You are viewing ".$_GET['type']." tickets for ".$_GET['day']."-".$_GET['month']."-".$_GET['year']." which were maded by ".$_GET['uname']);
}
else
{
  $r=get_queries($_GET['type'].'_for1day');
  $r=replace_queries($r,array("#year#","#month#","#day#"),array($_GET['year'],$_GET['month'],$_GET['day']));
  $xtpl->assign("SECTION_NAME", "You are viewing ".$_GET['type']." tickets for ".$_GET['day']."-".$_GET['month']."-".$_GET['year']);
}
$query=$r[0]['replaced_query'];

    $xtpl->assign("TABLE_HEADER", '<td class="table_header_text" align="center">#</td><td class="table_header_text" align="center"><a href="#" onclick="return false" onmouseup="select_all_checkboxs(\'select[]\',\'form1\')" class="table_header_text">Select</a></td><td align="center" class="table_header_text">Subject</td><td align="center" class="table_header_text">Queues</td><td align="center" class="table_header_text">Status</td><td align="center" class="table_header_text">Owner</td><td class="table_header_text" align="center">From</td><td align="center" class="table_header_text">Complain</td><td align="center" class="table_header_text">Rate</td><td class="table_header_text" align="center">Updated</td><td align="center" class="table_header_text">Created</td> ');

    $pg_sel=SQL_select($query);

        foreach($pg_sel as $i=>$row)
        {
            $xtpl->assign("NUM", ($i+1));
            $xtpl->assign("_ID", $i);
            $xtpl->assign("TICKET_ID", $row["id"]);
            $xtpl->assign("SELECT", "<input type=\"checkbox\" id=\"" . $i . "\" name=\"select[]\" value=\"" . $row["id"] . "\" onclick=\"change_tr_class(this)\">");
            if(strlen(trim($row["subject"])) > 0)
            {
                $subject = trim($row["subject"]);
            }else $subject = "No subject";
            $xtpl->assign("SUBJECT", "<a href=\"show_ticket.php?ticket_id=" . $row["id"] . "\" class=\"ticket\">" . StripSlashes(htmlspecialchars($subject)) . "</a>");
            $query = "SELECT name FROM " . T_GROUPS . " WHERE id=" . $row["group_id"];
            $row1 = SQL_select($query, 0);
            $xtpl->assign("QUEUE", StripSlashes(htmlspecialchars($row1["name"])));
            $query = "SELECT login FROM " . T_USERS . " WHERE id=" . $row["user_id"];
            $row1 = SQL_select($query, 0);
            $xtpl->assign("OWNER", $row1["login"]);
            if($row["cc_member_id"] > 0) $member = "<b>member id:</b> " . $row["cc_member_id"];
            else $member = "<b>member id:</b> none";
            if(strlen($row["from_email"]) > 0) $from_email = "<b>Email:</b>&nbsp;" . $row["from_email"];
            else $from_email = "<b>E-mail:</b> none";
            $applicant_name = "";
            if(trim($row["cc_fname"]) != "" || trim($row["cc_lname"]) != "") $applicant_name = "<br><strong>Name:</strong>&nbsp;" . ucwords(htmlspecialchars($row["cc_fname"])) . "&nbsp;" . ucwords(htmlspecialchars($row["cc_lname"]));
            $xtpl->assign("FROM", $member . $applicant_name . "<br>" . $from_email);
            $xtpl->assign("STATUS_CLASS", $row["status"]);
            $xtpl->assign("TICKET_STATUS", $row["status"]);
            if(!$row["updated_days_left"]) $updated_days_left = "<br><strong>Today</strong>";
            else $updated_days_left = "<br><strong>" . $row["updated_days_left"] . " days ago</strong>";
            $xtpl->assign("UPDATED", $row["updated1"] . $updated_days_left);
            if(!$row["created_days_left"]) $created_days_left = "<br><strong>Today</strong>";
            else $created_days_left = "<br><strong>" . $row["created_days_left"] . " days ago</strong>";
            $xtpl->assign("CREATED", $row["created1"] . $created_days_left);
            if($row["complain"]!='0000-00-00 00:00:00')
            {
                $xtpl->assign("COMPLAIN", "Complain");
                $xtpl->assign("COMPLAIN_STATUS", "complain_on");
            }else
            {
                $xtpl->assign("COMPLAIN", "No complain");
                $xtpl->assign("COMPLAIN_STATUS", "complain_off");
            }
            if($row["rate"] != "") $xtpl->assign("RATE", $row["rate"]);
            else $xtpl->assign("RATE", "No rate");

            $xtpl->parse("main.stat_tickets.list");
        }





$xtpl->parse("main.stat_list");
$xtpl->parse("main.stat_tickets");
$xtpl->parse("main");
$xtpl->out("main");
?>