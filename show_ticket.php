<?php
/**
 * show_tickets.php shows ticket information and message history
 * you can press reply or add comment buttons here
 *
 * also allows to change status,  and queue
 * (not via group_operations, but via the same code inside this file - this should be done via class group_operations)
 * include validator.php
 */

include "validator.php";

//getting tickets id
if(array_key_exists("selected_tickets", $_REQUEST)) unset($_SESSION["selected_tickets"]);
if(array_key_exists("ticket_id", $_REQUEST))
{
    $ticket_id = $_REQUEST["ticket_id"];
    $_SESSION["selected_ticket_id"] = $ticket_id;
}elseif(array_key_exists("selected_ticket_id", $_SESSION))
{
    $ticket_id = $_SESSION["selected_ticket_id"];
}else $ticket_id = 0;
if(!$ticket_id)
{
    header("Location: " . MAIN_HOST . "index.php");
    exit;
}

//getting ticket group and checking if the user has permission to this group
$query = "SELECT group_id FROM " . T_TICKETS . " WHERE id=" . $ticket_id;
$row = SQL_select($query, 0);
if($row)
{
    $query = "SELECT group_id
                FROM " . T_USERS_GROUPS . "
               WHERE user_id=" . $_SESSION["uid"] . "
                 AND group_id=" . $row["group_id"];
    $row1 = SQL_select($query, 0);
    if(!$row1)
    {
        header("Location: " . MAIN_HOST . "index.php");
        exit;
    }
}else
{
    header("Location: " . MAIN_HOST . "index.php");
    exit;
}

//changing ticket owner(why not via class group_operations?)
if($act == "change_owner")
{
    $query = "SELECT id FROM " . T_USERS . " WHERE id=" . (int)$_REQUEST["owner"];
    $row = SQL_select($query, 0);
    if($row)
    {
        $query = "SELECT status, user_id, group_id FROM " . T_TICKETS . " WHERE id=" . $ticket_id;
        $row1 = SQL_select($query, 0);
        if((int)$_REQUEST["owner"] == $_SESSION["uid"])
        {
            $user_action = "take_ticket";
            $prev_user_id = $row1["user_id"];
            $group_id=$row1["group_id"];
        }else
        {
        	
            $user_action = "gave_ticket";
            $prev_user_id = $_SESSION["uid"];
            
            
          	$query = "SELECT group_id
                FROM " . T_USERS_GROUPS . "
                WHERE user_id=" . (int)$_REQUEST["owner"] . 
          	    " AND group_id=" . $row1["group_id"];
    		$row2 = SQL_select($query, 0);
    			print_r($row2);
    			if(!$row2)
    				{
    					$query = "SELECT group_id
                				FROM " . T_USERS_GROUPS . "
                				WHERE user_id=" . (int)$_REQUEST["owner"] . 
          	    				" LIMIT 1";
          	    		$row2 = SQL_select($query, 0);
          	    		print_r($row2);
          	    		$group_id=$row2["group_id"];
    				}
    			else
    				{
    					$group_id=$row1["group_id"];
    				}
    			
            
        }
        $query = "INSERT
                    INTO " . T_TICKETS_ACTIONS . " (ticket_id,
                                                    user_action,
                                                    now_user_id,
                                                    prev_user_id,
                                                    now_ticket_status,
                                                    prev_ticket_status,
                                                    now_group_id,
                                                    prev_group_id,
                                                    created)
                    VALUES (" . $ticket_id . ",
                            '" . $user_action . "',
                            " . (int)$_REQUEST["owner"] . ",
                            " . $prev_user_id . ",
                            '" . $row1["status"] . "',
                            '" . $row1["status"] . "',
                            " . $group_id . ",
                            " . $row1["group_id"] . ",
                            now())";
        SQL_request($query);
        $query = "UPDATE " . T_TICKETS . " SET group_id=" . $group_id . ", user_id=" . (int)$_REQUEST["owner"] . ", updated=now() WHERE id=" . $ticket_id;
        SQL_request($query);
    }
}
//changing ticket status(why not via class group_operations?)
elseif($act == "change_status")
{
    $query = "SELECT status, user_id, group_id FROM " . T_TICKETS . " WHERE id=" . $ticket_id;
    $row1 = SQL_select($query, 0);
    $query = "INSERT
                INTO " . T_TICKETS_ACTIONS . " (ticket_id,
                                                user_action,
                                                now_user_id,
                                                prev_user_id,
                                                now_ticket_status,
                                                prev_ticket_status,
                                                now_group_id,
                                                prev_group_id,
                                                created)
                VALUES (" . $ticket_id . ",
                        'changed_status',
                        " . $_SESSION["uid"] . ",
                        " . $row1["user_id"] . ",
                        '" . $_REQUEST["status"] . "',
                        '" . $row1["status"] . "',
                        " . $row1["group_id"] . ",
                        " . $row1["group_id"] . ",
                        now())";
    SQL_request($query);
    $query = "UPDATE " . T_TICKETS . " SET status='" . $_REQUEST["status"] . "', updated=now() WHERE id=" . $ticket_id;
    SQL_request($query);
}

//changing priority
elseif($act=="change_priority")
{
	 $query = "SELECT priority, user_id, group_id FROM " . T_TICKETS . " WHERE id=" . $ticket_id;
    $row1 = SQL_select($query, 0);
    $query = "INSERT
                INTO " . T_TICKETS_ACTIONS . " (ticket_id,
                                                user_action,
                                                now_user_id,
                                                prev_user_id,
                                                now_ticket_priority,
                                                prev_ticket_priority,
                                                now_group_id,
                                                prev_group_id,
                                                created)
                VALUES (" . $ticket_id . ",
                        'change_priority',
                        " . $_SESSION["uid"] . ",
                        " . $row1["user_id"] . ",
                        '" . $_REQUEST["priority"] . "',
                        '" . $row1["priority"] . "',
                        " . $row1["group_id"] . ",
                        " . $row1["group_id"] . ",
                        now())";
    SQL_request($query);
    $query = "UPDATE " . T_TICKETS . " SET priority='" . $_REQUEST["priority"] . "', updated=now() WHERE id=" . $ticket_id;
    SQL_request($query);
	
}


//changing ticket group(why not via class group_operations?)
elseif($act == "change_queue")
{
    $query = "SELECT group_id
                FROM " . T_USERS_GROUPS . "
               WHERE user_id=" . $_SESSION["uid"] . "
                 AND group_id=" . $_REQUEST["queue"];
    $row = SQL_select($query, 0);
    if($row)
    {
        $query = "SELECT status, user_id, group_id FROM " . T_TICKETS . " WHERE id=" . $ticket_id;
        $row1 = SQL_select($query, 0);
        $query = "INSERT
                    INTO " . T_TICKETS_ACTIONS . " (ticket_id,
                                                    user_action,
                                                    now_user_id,
                                                    prev_user_id,
                                                    now_ticket_status,
                                                    prev_ticket_status,
                                                    now_group_id,
                                                    prev_group_id,
                                                    created)
                    VALUES (" . $ticket_id . ",
                            'changed_group',
                            " . $_SESSION["uid"] . ",
                            " . $row1["user_id"] . ",
                            '" . $row1["status"] . "',
                            '" . $row1["status"] . "',
                            " . (int)$_REQUEST["queue"] . ",
                            " . $row1["group_id"] . ",
                            now())";
        SQL_request($query);
        $query = "UPDATE " . T_TICKETS . " SET group_id=" . (int)$_REQUEST["queue"] . ", updated=now() WHERE id=" . $ticket_id;
        SQL_request($query);
    }
}

//getting main info about ticket from tickets
$query = "SELECT id,
                 website,
                 cc_member_id,
                 cc_fname,
                 cc_lname,
                 from_email,
                 subject,
                 body_id,
                 status,
                 priority,
                 user_id,
                 group_id,
                 rate,
                 complain,
                 golden,
                 DATE_FORMAT(updated, '%W %d %b %Y %H:%i') as updated,
                 DATE_FORMAT(created, '%W %d %b %Y %H:%i') as created,
                 DATE_FORMAT(complain, '%W %d %b %Y %H:%i') as complained,
                 (TO_DAYS(now()) - TO_DAYS(created)) as created_days_left,
                 (TO_DAYS(now()) - TO_DAYS(updated)) as updated_days_left
            FROM " . T_TICKETS . "
           WHERE id=" . $ticket_id;
$row = SQL_select($query, 0);

$ticket_id_assign=$row['id'];
//assigns main ticket info to the template
$xtpl->assign("TICKET_SUBJECT", StripSlashes(htmlspecialchars($row["subject"])));
$xtpl->assign("TICKET_ID", $ticket_id);


$query = "SELECT id, login FROM " . T_USERS . " WHERE access=1 ORDER BY login";
$rows = SQL_select($query);
$users = array();
$owner = "";
foreach($rows as $val)
{
    $users[$val["id"]] = $val["login"];
    if($row["user_id"] == $val["id"]) $owner = $val["login"];
}
$xtpl->assign("OWNER", $owner);
$xtpl->assign("ANOTHERS_OWNERS", get_drop_down_list($users, "owner", "", "Select another owner", 0, "", "onchange=\"document.getElementById('act').value='change_owner'; document.getElementById('form1').submit();\""));
$xtpl->assign("TSTATUS", $row["status"]);
$xtpl->assign("TRATE", (isset($row["rate"]))?"{$row['rate']}":"not rated");
$xtpl->assign("TCOMPLAIN", ($row["complain"]=='0000-00-00 00:00:00')?"no":$row["complained"]);
$status = get_enum(T_TICKETS, "status");
$tmp_status = array();
foreach($status as $val) if($val != $row["status"]) $tmp_status[$val] = $val;
$xtpl->assign("ANOTHER_STATUS", get_drop_down_list($tmp_status, "status", "", "Select another status", 0, "", "onchange=\"document.getElementById('act').value='change_status'; document.getElementById('form1').submit();\""));
$priority=get_enum(T_TICKETS,"priority");
$tmp_priority=array();

foreach($priority as $val) $tmp_priority[$val] = $val;
$xtpl->assign("PRIORITY",$row["priority"]);
$xtpl->assign("ANOTHER_PRIORITY", get_drop_down_list($tmp_priority, "priority", "", "Select Priority", 0, "", "onchange=\"document.getElementById('act').value='change_priority'; document.getElementById('form1').submit();\""));


$query = "SELECT DISTINCT g.id, g.name
            FROM " . T_USERS_GROUPS . " as ug, " . T_GROUPS . " as g
           WHERE ug.user_id=" . $_SESSION["uid"] . "
             AND ug.group_id=g.id
             AND g.id != " . $row["group_id"];
$rows = SQL_select($query);
$queues = array();
foreach($rows as $val) $queues[$val["id"]] = $val["name"];
$query = "SELECT name FROM " . T_GROUPS . " WHERE id=" . $row["group_id"];
$row1 = SQL_select($query, 0);
$xtpl->assign("QUEUE", StripSlashes(htmlspecialchars($row1["name"])));
$xtpl->assign("ANOTHER_QUEUES", get_drop_down_list($queues, "queue", "", "Select another queue", 0, "", "onchange=\"document.getElementById('act').value='change_queue'; document.getElementById('form1').submit();\""));
$xtpl->assign("CREATED", $row["created"]);
$xtpl->assign("TIC_WEBSITE", $row["website"]);
if(!$row["created_days_left"]) $xtpl->assign("CREATED_DAYS_AGO", "Today");
else $xtpl->assign("CREATED_DAYS_AGO", "<br>" . $row["created_days_left"] . " days ago");
$xtpl->assign("UPDATED", $row["updated"]);
if(!$row["updated_days_left"]) $xtpl->assign("UPDATED_DAYS_AGO", "Today");
else $xtpl->assign("UPDATED_DAYS_AGO", "<br>" . $row["updated_days_left"] . " days ago");
$golden = ($row["golden"]=='true' and (int)$row["cc_member_id"])?"&nbsp;<b>[GOLDEN]</b>":"";
$sites = array('ualadys', 'ruladys', 'chiladys', 'arladys');
foreach($sites as $k=>$v){
	if(strstr($row['website'], $v))
		$site = $v; 
}
if((int)$row["cc_member_id"]) $xtpl->assign("APPLICANT_ID", "<a href='https://suadmctrl8.{$site}.com/members.rpx?id={$row['cc_member_id']}' target='support'>".$row["cc_member_id"]."</a>".$golden);
else $xtpl->assign("APPLICANT_ID", "consumer doesn't exists");
$applicant_name = "";
if(trim($row["cc_fname"]) != "" || trim($row["cc_lname"]) != "")
{
    $applicant_name = ucwords(htmlspecialchars($row["cc_fname"])) . "&nbsp;" . ucwords(htmlspecialchars($row["cc_lname"]));
    $xtpl->assign("APPLICANT_NAME", $applicant_name);
}else $xtpl->assign("APPLICANT_NAME", "name doesn't exists");
if(trim($row["from_email"]) != "") $xtpl->assign("APPLICANT_EMAIL", htmlspecialchars($row["from_email"]));
else $xtpl->assign("APPLICANT_EMAIL", "e-mail doesn't exists");

$query = "";
if((int)$row["cc_member_id"] && trim($row["from_email"]) != "")
{
    $query = "SELECT id,
                     subject,
                     status,
                     priority,
                     created,
                     UNIX_TIMESTAMP(updated)-UNIX_TIMESTAMP(created) AS workTime,
                     complain,
                     rate
                FROM " . T_TICKETS . "
               WHERE (from_email='" . AddSlashes($row["from_email"]) . "'
                  OR cc_member_id=" . $row["cc_member_id"] . ")
                 " . get_groups() . "
            ORDER BY id DESC";
}elseif((int)$row["cc_member_id"])
{
    $query = "SELECT id,
                     subject,
                     status,
                     priority,
                     created,
                     UNIX_TIMESTAMP(updated)-UNIX_TIMESTAMP(created) AS workTime,
                     complain,
                     rate
                FROM " . T_TICKETS . "
               WHERE cc_member_id=" . $row["cc_member_id"] . "
                 " . get_groups() . "
            ORDER BY id DESC";
}elseif(trim($row["from_email"]) != "")
{
    $query = "SELECT id,
                     subject,
                     status,
                     priority,
                     created,
                     UNIX_TIMESTAMP(updated)-UNIX_TIMESTAMP(created) AS workTime,
                     complain,
                     rate
                FROM " . T_TICKETS . "
               WHERE from_email='" . AddSlashes($row["from_email"]) . "'
                 " . get_groups() . "
            ORDER BY id DESC";
}
if($query != "")
{
    $rows = SQL_select($query);
    $last_tickets = "";
    if($rows)
    {
        foreach($rows as $val)
        {
            list($seconds,$minutes,$hours,$days) = countTime($val["workTime"]);
            $seconds = ($seconds==0) ? "":"{$seconds}s";
            $minutes = ($minutes==0) ? "":"{$minutes}m";
            $hours = ($hours==0) ? "":"{$hours}h";
            $days = ($days==0) ? "":"{$days}d";
            $inWork = ":{$days}{$hours}{$minutes}";
/*            switch ($val["rate"]) {
                case "My problem (question) has been resolved completely and in short terms": $rate = "rate5"; break;
                case "My problem (question) has been resolved but it took too much time": $rate = "rate4"; break;
                case "I am not satisfied with the reply I received": $rate = "rate3"; break;
                case "I don't understand your reply": $rate = "rate2"; break;
                case "My problem (question) has not been resolved": $rate = "rate1"; break;
                default:$rate = ""; break;
            }*/
            $isCurrent  = ($ticket_id == $val["id"]) ? "highLight":"";
            $complained = ($val["complain"] != '0000-00-00 00:00:00') ? "complained":"";
            $xtpl->assign("APPLICANT_TICKETS", "<a href=\"show_ticket.php?ticket_id=" . $val["id"] . "\" class=\"$complained $isCurrent queue\" title='created:{$val['created']}'><span class='techInfo'>[" . $val["id"] . ":" . $val["status"] . $inWork. "]</span> " . StripSlashes(htmlspecialchars($val["subject"])) ."<br><span class='rateInfo'>{$val["rate"]}</span>". "</a>");
            $xtpl->parse("main.show_ticket.last_ten_tickets");
        }
    }else
    {
        $xtpl->assign("APPLICANT_TICKETS", "<div class=\"whitetext2\">not exists</div>");
        $xtpl->parse("main.show_ticket.last_ten_tickets");
    }
}else
{
    $xtpl->assign("APPLICANT_TICKETS", "<div class=\"whitetext2\">not exists</div>");
    $xtpl->parse("main.show_ticket.last_ten_tickets");
}
//ticket main message
$xtpl->assign("DATE", $row["created"]);
if((int)$row["cc_member_id"]) $comment = "Member id: <b>" . (int)$row["cc_member_id"] . " " . $applicant_name . "</b> - Created ticket";
else $comment = "<b>" . htmlspecialchars(trim($row["from_email"])) . "</b> - Created ticket";
$xtpl->assign("COMMENT", $comment);
$xtpl->assign("ACTIONS", "<a href=\"update.php?message_status=reply&ta_id=" . $ticket_id_assign . "\"><img src=\"" . MAIN_HOST . "images/reply.gif\" border=\"0\" alt=\"Reply\"></a>&nbsp;<a href=\"update.php?message_status=comment&ta_id=" . $ticket_id_assign . "\" class=\"table_header_text\"><img src=\"" . MAIN_HOST . "images/add_comment.gif\" border=\"0\" alt=\"Add comment\"></a>");
//selecting first message
$query = "SELECT body FROM " . T_BODIES . " WHERE id=" . $row["body_id"];
$row1 = SQL_select($query, 0);
if ($row['status']!="request")
	$xtpl->assign("MESSAGE_CLASS", "income");
		else $xtpl->assign("MESSAGE_CLASS","answer");
if(trim($row1["body"]) != "")
{
    $message_body = str_replace("$","&#36;", StripSlashes(nl2br(htmlspecialchars($row1["body"]))));
    $xtpl->assign("MESSAGE_BODY", $message_body);
    $xtpl->parse("main.show_ticket.history_list.body_exists");
}
$xtpl->parse("main.show_ticket.history_list");
//selecting all other history from tickets_actions
$query = "SELECT id,
                 message_type,
                 user_action,
                 body_id,
                 now_user_id,
                 prev_user_id,
                 now_ticket_status,
                 prev_ticket_status,
                 now_group_id,
                 prev_group_id,
                 now_ticket_priority,
                 prev_ticket_priority,
                 DATE_FORMAT(created, '%W %d %b %Y %h:%i') as created1
            FROM " . T_TICKETS_ACTIONS . "
           WHERE ticket_id=" . $ticket_id . "
        ORDER BY created";
$rows = SQL_select($query);
if($rows)
{
    foreach($rows as $val)
    {
        $xtpl->assign("DATE", $val["created1"]);
        $xtpl->assign("BGCOLOR", "AAAAAA");
        $comment = "";
        $actions = "";
        $temp_actions = "<a href=\"update.php?message_status=reply&ta_id=" . $val["id"] . "\"><img src=\"" . MAIN_HOST . "images/reply.gif\" border=\"0\" alt=\"Reply\"></a>&nbsp;<a href=\"update.php?message_status=comment&ta_id=" . $val["id"] . "\" class=\"table_header_text\"><img src=\"" . MAIN_HOST . "images/add_comment.gif\" border=\"0\" alt=\"Add comment\"></a>";
        if(trim($val["user_action"]) != "")
        {
        	
        	if($val["user_action"] == "change_priority")
            {
                $comment = "The user <b>" . get_user_login($val["now_user_id"]) . "</b> have changed ticket priority from <b>" . $val["prev_ticket_priority"] . "</b> to <b>" . $val["now_ticket_priority"] . "</b>";
            }
        
            if($val["user_action"] == "changed_status")
            {
                $comment = "The user <b>" . get_user_login($val["now_user_id"]) . "</b> have changed ticket status from <b>" . $val["prev_ticket_status"] . "</b> to <b>" . $val["now_ticket_status"] . "</b>";
            }elseif($val["user_action"] == "changed_group")
            {
                $comment = "The user <b>" . get_user_login($val["now_user_id"]) . "</b> have changed queue for ticket from <b>" . get_queue_name($val["prev_group_id"]) . "</b> to <b>" . get_queue_name($val["now_group_id"]) . "</b>";
            }elseif($val["user_action"] == "take_ticket")
            {
                $comment = "The user <b>" . get_user_login($val["now_user_id"]) . "</b> took away the ticket from user <b>" . get_user_login($val["prev_user_id"]) . "</b>";
            }elseif($val["user_action"] == "gave_ticket")
            {
                $comment = "The user <b>" . get_user_login($val["prev_user_id"]) . "</b> gave the ticket to user <b>" . get_user_login($val["now_user_id"]) . "</b>";
            }
        }else
        {
            if($val["message_type"] == "incoming")
            {
                if((int)$row["cc_member_id"]) $comment = "The consumer with ID: <b>" . (int)$row["cc_member_id"] . " " . $applicant_name . "</b> - sent new message";
                else $comment = "The person with email: <b>" . htmlspecialchars(trim($row["from_email"])) . "</b> - sent new message";
                $actions = $temp_actions;
                $xtpl->assign("MESSAGE_CLASS", "income");
            }
            
          
            elseif($val["message_type"] == "outcoming")
            {
                $comment = "The user <b>" . get_user_login($val["now_user_id"]) . "</b> sent the answer";
                $xtpl->assign("MESSAGE_CLASS", "answer");
                $xtpl->assign("BGCOLOR", "FFFFFF");
            }elseif($val["message_type"] == "comment")
            {
                $xtpl->assign("MESSAGE_CLASS", "comment");
                $comment = "The user <b>" . get_user_login($val["now_user_id"]) . "</b> added comments";
                $xtpl->assign("BGCOLOR", "EFEFEF");
                $actions = $temp_actions;
            }
        }
        $xtpl->assign("COMMENT", $comment);
        $xtpl->assign("ACTIONS", $actions);
        $query = "SELECT body FROM " . T_BODIES . " WHERE id=" . $val["body_id"];
        $row1 = SQL_select($query, 0);
        if($row1 && trim($row1["body"]) != "")
        {
            $message_body = str_replace("$","&#36;", StripSlashes(nl2br(htmlspecialchars($row1["body"]))));
            $xtpl->assign("MESSAGE_BODY", $message_body);
            $xtpl->parse("main.show_ticket.history_list.body_exists");
        }
        $xtpl->parse("main.show_ticket.history_list");
    }
}
get_queues_status($xtpl);
$xtpl->parse("main.show_ticket");
$xtpl->parse("main");
$xtpl->out("main");
?>