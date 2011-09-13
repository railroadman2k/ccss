<?php
/**
 * update.php - works up with single ticket or ticket group
 * you can reply or add comment to the ticket here
 * allow to use answer templates
 * also allows to change status, owner and queue (via group_operations)
 * include validator.php
 */
include "validator.php";
$uri=$_SERVER['REQUEST_URI'];
$xtpl->assign("URI",$uri);
$manager_info=get_manager_info($_SESSION["uid"]);

 

//getting ticket(s) info from Session and post
if(array_key_exists("selected_tickets", $_SESSION))
{
    $group_of_tickets = 1;
    $ticket_id = 0;
    $tmp = new group_operations($act, 1);
}
elseif(array_key_exists("selected_ticket_id", $_SESSION))
{
    $ticket_id = $_SESSION["selected_ticket_id"];
    $group_of_tickets = 0;
    $xtpl->assign("TICKET_ID", "<input type=\"hidden\" name=\"ta_id\" value=\"" . $ticket_id . "\">");
}
else $ticket_id = 0;

$xtpl->assign("SECTION_NAME", "Write the message");

//if no ticket choosed
if(!$ticket_id && !$group_of_tickets)
{
    header("Location: " . MAIN_HOST . "index.php");
    exit;
}


//if template request choosed
if(array_key_exists("template", $_REQUEST)) $template = (int)$_REQUEST["template"];
else $template = 0;


//if user send answer or comment
if($act == "add_message" && strlen(trim($_REQUEST["message"])) > 2)
{

    if($_REQUEST["message_status"] == "reply") $message_type = "outcoming";
    else $message_type = "comment";

    //if single ticket
    if(!$group_of_tickets)
    {
        //select ticket info from DB
        $query = "SELECT from_email,
                         subject,
                         body_id,
                         status,
                         user_id,
                         group_id,
                         website 
                    FROM " . T_TICKETS . "
                   WHERE id=" . $ticket_id;
        $row = SQL_select($query, 0);
         $act="update";
        if ($row["body_id"]==0)
		{
			
				$domen = substr($row["website"],7,-1);
				 $query="DELETE  FROM " .T_TICKETS_ACTIONS . "
				 		 WHERE ticket_id=$ticket_id LIMIT 10";
				 $delete=SQL_request($query);
				 $query = "INSERT
                        INTO " . T_TICKETS_ACTIONS . " (ticket_id,
                        								message_type,
                                                        user_action,
                                                        now_user_id,
                                                        prev_user_id,
                                                        now_ticket_status,
                                                        prev_ticket_status,
                                                        now_group_id,
                                                        prev_group_id,
                                                        created)
                        VALUES (" . $ticket_id . ",
                        		'outcoming',
                                'question',
                                " . $_SESSION["uid"] . ",
                                " . $row["user_id"] . ",
                                '" . $row["status"] . "',
                                '" . $row["status"] . "',
                                " . $row["group_id"] . ",
                                " . $row["group_id"] . ",
                                now())";
            $test=SQL_request($query);
          
            $subject=$_REQUEST["subject"];
       $query = "INSERT INTO " . T_BODIES . " (body) VALUES ('" . AddSlashes($_REQUEST["message"]) . "')";
       SQL_request($query);
       $body_id = mysql_insert_id();
            $query="UPDATE " .T_TICKETS . " SET body_id=$body_id, subject='$subject', status='request'  
            		WHERE id=$ticket_id LIMIT 1";
            $info=SQL_request($query);
            
            $subject = "QUESTION : ".$subject." /[CCSS:$ticket_id]";
			$query = "SELECT email FROM " . T_GROUPS . " WHERE id=" . $row["group_id"];
			$row1 = SQL_select($query, 0);
			$headers  = "MIME-Version: 1.0\n";
			$headers .= "Content-type: text/plain; charset=iso-8859-1\n";
			$headers .= "To: " . $row["from_email"] . "\n";
			$headers .= "From:\"".$manager_info['name'].":".$manager_info['ugroup']."\" <{$row1['email']}@$domen>\n";
			$message = "" . <<<sss


                    
Question sent by CC Manager : {$manager_info["name"]} 
You can send your answer to your question on our site.
    {$row["website"]}support/ticket/{$ticket_id}

    All you need to do for this is to log in on the site
    and add your answer to your question.
    Thank you for using our services,
    	CC Support Team,
		
		
		
sss;
 $message.=$_REQUEST["message"];
                    mail($row["from_email"], $subject, $message, $headers,"-f{$row1['email']}@iispp.com");
					
                    
            		header("Location: tickets.php");
            		exit;
            
           
			
		}

        //select user permissions from DB
        $query = "SELECT permissions FROM " . T_USERS . " WHERE id=" . $row["user_id"];
        $row1 = SQL_select($query, 0);

        //if user nobody and new ticket
        if($row1["permissions"] == "system" && $row["status"] == "new")
        {
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
                                'take_ticket',
                                " . $_SESSION["uid"] . ",
                                " . $row["user_id"] . ",
                                '" . $row["status"] . "',
                                '" . $row["status"] . "',
                                " . $row["group_id"] . ",
                                " . $row["group_id"] . ",
                                now())";
            SQL_request($query);
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
                                " . $_SESSION["uid"] . ",
                                'opened',
                                '" . $row["status"] . "',
                                " . $row["group_id"] . ",
                                " . $row["group_id"] . ",
                                now())";
            SQL_request($query);

            $query = "UPDATE " . T_TICKETS . " SET user_id=" . $_SESSION["uid"] . ", status='opened' WHERE id=" . $ticket_id;
            SQL_request($query);

        }

        $query = "INSERT INTO " . T_BODIES . " (body) VALUES ('" . AddSlashes($_REQUEST["message"]) . "')";
        SQL_request($query);
        $body_id = mysql_insert_id();

        if($body_id)
        {

            $query = "INSERT
                        INTO " . T_TICKETS_ACTIONS . " (ticket_id,
                                                        message_type,
                                                        now_user_id,
                                                        prev_user_id,
                                                        now_ticket_status,
                                                        prev_ticket_status,
                                                        now_group_id,
                                                        prev_group_id,
                                                        body_id,
                                                        created)
                                                VALUES (" . $ticket_id . ",
                                                        '" . $message_type . "',
                                                        " . $_SESSION["uid"] . ",
                                                        " . $row["user_id"] . ",
                                                        '" . $row["status"] . "',
                                                        '" . $row["status"] . "',
                                                        " . $row["group_id"] . ",
                                                        " . $row["group_id"] . ",
                                                        " . $body_id . ",
                                                        now())";
            SQL_request($query);
            update_ticket_time($ticket_id);
            if(__CFG_SEND_EMAIL && $_REQUEST["message_status"] == "reply")
            {
                //erase rate and complain
                $query = "UPDATE " . T_TICKETS . " SET rate=NULL, complain='0000-00-00 00:00:00' WHERE id=" . $ticket_id;
                SQL_request($query);
                //send email
                if(strlen(trim($_REQUEST["subject"])) > 2) $subject = trim($_REQUEST["subject"]);
                else $subject = $row["subject"];
                $domen = substr($row["website"],7,-1);
                $subject = "ANSWER: ".$subject." /[CCSS:$ticket_id]";
                $query = "SELECT email FROM " . T_GROUPS . " WHERE id=" . $row["group_id"];
                $row1 = SQL_select($query, 0);
				$headers  = "MIME-Version: 1.0\n";
				$headers .= "Content-type: text/plain; charset=iso-8859-1\n";
				$headers .= "To: " . $row["from_email"] . " <" . $row["from_email"] . ">\n";
				$headers .= "From:\"".$manager_info['name'].":".$manager_info['ugroup']."\" <{$row1['email']}@$domen>\n";
                $message = "" . <<<sss



 
Reply sent by {$manager_info["name"]} 
For any question use our website only, do not reply to this email.

Attention:
    We issue the answers to your questions only on our website.
    We also send a copy of our answer to your email address
    but we don't give the guarantee that it will reach you.
    That's why we strongly recommend to receive an answer to your question on our site.
    {$row["website"]}support/ticket/{$ticket_id}

    All you need to do for this is to log in on the site
    and our system will inform you if the answer to your question is available.
	
	
	
sss;
$message.=$_REQUEST["message"];
				
              mail($row["from_email"], $subject, $message, $headers, "-f{$row1['email']}@iispp.com");
   
         		  $act="update";
            }
           // header("Location: " . MAIN_HOST . "show_ticket.php");
           // exit;
        }
    }
    //if multiple ticket(ticket group)
    if($group_of_tickets)
    {
        foreach($_SESSION["selected_tickets"] as $ticket_id)
        {
            $query = "SELECT from_email,
                             subject,
                             status,
                             user_id,
                             group_id,
                             website 
                             
                        FROM " . T_TICKETS . "
                       WHERE id = " . $ticket_id;
            $row = SQL_select($query, 0);
            $query = "SELECT permissions FROM " . T_USERS . " WHERE id=" . $row["user_id"];
            $row1 = SQL_select($query, 0);

            if($row1["permissions"] == "system" && $row["status"] == "new")
            {
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
                                    'take_ticket',
                                    " . $_SESSION["uid"] . ",
                                    " . $row["user_id"] . ",
                                    '" . $row["status"] . "',
                                    '" . $row["status"] . "',
                                    " . $row["group_id"] . ",
                                    " . $row["group_id"] . ",
                                    now())";
                SQL_request($query);
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
                                    " . $_SESSION["uid"] . ",
                                    'opened',
                                    '" . $row["status"] . "',
                                    " . $row["group_id"] . ",
                                    " . $row["group_id"] . ",
                                    now())";
                SQL_request($query);
                $query = "UPDATE " . T_TICKETS . " SET user_id=" . $_SESSION["uid"] . ", status='opened' WHERE id=" . $ticket_id;
                SQL_request($query);
            }
            $query = "INSERT INTO " . T_BODIES . " (body) VALUES ('" . AddSlashes($_REQUEST["message"]) . "')";
            SQL_request($query);
            $body_id = mysql_insert_id();
            if($body_id)
            {
                $query = "INSERT
                            INTO " . T_TICKETS_ACTIONS . " (ticket_id,
                                                            message_type,
                                                            now_user_id,
                                                            prev_user_id,
                                                            now_ticket_status,
                                                            prev_ticket_status,
                                                            now_group_id,
                                                            prev_group_id,
                                                            body_id,
                                                            created)
                                                    VALUES (" . $ticket_id . ",
                                                            '" . $message_type . "',
                                                            " . $_SESSION["uid"] . ",
                                                            " . $row["user_id"] . ",
                                                            '" . $row["status"] . "',
                                                            '" . $row["status"] . "',
                                                            " . $row["group_id"] . ",
                                                            " . $row["group_id"] . ",
                                                            " . $body_id . ",
                                                            now())";
                SQL_request($query);
                update_ticket_time($ticket_id);
                if(__CFG_SEND_EMAIL && $_REQUEST["message_status"] == "reply")
                {
                	
                    //erase rate and complain
                    $query = "UPDATE " . T_TICKETS . " SET rate=NULL, complain='0000-00-00 00:00:00' WHERE id=" . $ticket_id;
                    SQL_request($query);
                    //send email
                    if(strlen(trim($_REQUEST["subject"])) > 2) $subject = trim($_REQUEST["subject"]);
                    else $subject = $row["subject"];
                    $domen = substr($row["website"],7,-1);
                    print ($domen);
                    $subject = "ANSWER: ".$subject." /[CCSS:$ticket_id]";
                    $query = "SELECT email FROM " . T_GROUPS . " WHERE id=" . $row["group_id"];
                    $row1 = SQL_select($query, 0);
                    $headers  = "MIME-Version: 1.0\n";
                    $headers .= "Content-type: text/plain; charset=iso-8859-1\n";
                    $headers .= "To: " . $row["from_email"] . " <" . $row["from_email"] . ">\n";
                    $headers .= "From:\"".$manager_info['name'].":".$manager_info['ugroup']."\" <{$row1['email']}@$domen>\n";
                    $message = "" . <<<sss



Reply sent by {$manager_info["name"]}
For any question use our website only, do not reply to this email.

Attention:
    We issue the answers to your questions only on our website.
    We also send a copy of our answer to your email address
    but we don't give the guarantee that it will reach you.
    That's why we strongly recommend to receive an answer to your question on our site.
    {$row["website"]}support/ticket/{$ticket_id}

    All you need to do for this is to log in on the site
    and our system will inform you if the answer to your question is available.
	
	
sss;
$message.=$_REQUEST["message"];
                    mail($row["from_email"], $subject, $message, $headers, "-f{$row1['email']}@iispp.com");
                }
            }
            ini_set("max_execution_time", 29);
            $act="update";
        }
      //  header("Location: " . MAIN_HOST . "show_ticket.php");
       // exit;
    }
}
if($act == "update")
{
    $_SESSION["selected_tickets"] = array();
    $_SESSION["selected_tickets"][]=(int)$_REQUEST["ta_id"];
    $tmp = new group_operations($act);
    
    
    header("Location: " . MAIN_HOST . "show_ticket.php");
        exit;
}

if(!$group_of_tickets)
{
	
    $query = "SELECT id,
                     cc_member_id,
                     cc_fname,
                     cc_lname,
                     from_email,
                     subject,
                     body_id,
                     status,
                     user_id,
                     group_id,
                     DATE_FORMAT(updated, '%W %b %d %h:%i %Y') as updated,
                     DATE_FORMAT(created, '%W %b %d %h:%i %Y') as created
                FROM " . T_TICKETS . "
               WHERE id=" . $ticket_id;
    
    $query2 ="SELECT body_id FROM " .T_TICKETS_ACTIONS . "
    		  WHERE id="  . $_REQUEST['ta_id'] ."";
    
    
    
      $row = SQL_select($query, 0);
      
      
    if ($_REQUEST['ta_id']!=$ticket_id)
    {
    	 $row_body=SQL_select($query2,0);
    	 $row['body_id']=$row_body['body_id'];
    }
    
  
    
   
    
   
   
    
    
   
    if((int)$row["cc_member_id"]) $applicant = "Consumer with ID: <b>" . (int)$row["cc_member_id"] . "</b>";
    else $applicant = "The person with email: <b>" . trim($row["from_email"]) . "</b>";
    $applicant_name = "";
    if(trim($row["cc_fname"]) != "" || trim($row["cc_lname"]) != "") $applicant_name = "&nbsp;<b>" . ucwords(htmlspecialchars($row["cc_fname"])) . "&nbsp;" . ucwords(htmlspecialchars($row["cc_lname"])) . "</b>";
    $xtpl->assign("APPLICANT", $applicant . $applicant_name);
    $xtpl->assign("TICKET_INFO", "Owner: <strong>" . get_user_login($row["user_id"]) . "</strong> Queue: <strong>" . get_queue_name($row["group_id"]) . "</strong> Status: <strong>" . $row["status"] . "</strong>");
    $xtpl->parse("main.update.ticket_info");
    get_group_operations($xtpl, "main.update", "form1", $row["group_id"], 0);
    $xtpl->assign("SUBJECT", StripSlashes(htmlspecialchars($row["subject"])));
    $type_message = array("comment"=>"Add comments", "reply"=>"Send answer");
    $xtpl->assign("TYPE_MESSAGE", get_drop_down_list($type_message, "message_status"));
    $xtpl->assign("MESSAGE_STATUS", $_REQUEST["message_status"]);
    if($template > 0) $xtpl->assign("MESSAGE", get_template($template, $ticket_id, $row["body_id"]));
    $query = "SELECT body FROM " . T_BODIES . " WHERE id=" . $row["body_id"];
    $row2 = SQL_select($query, 0);
    if($row2 && trim($row2["body"]) != "")
    {
        $message_body = str_replace("$","&#36;", StripSlashes(nl2br(htmlspecialchars($row2["body"]))));
        $xtpl->assign("REPLY_MESSAGE", $message_body);
        $xtpl->parse("main.update.reply_message");
    }
}elseif($group_of_tickets)
{
    $query = "SELECT id,
                     cc_member_id,
                     cc_fname,
                     cc_lname,
                     from_email,
                     subject,
                     body_id,
                     status,
                     user_id,
                     group_id,
                     DATE_FORMAT(updated, '%W %b %d %h:%i %Y') as updated,
                     DATE_FORMAT(created, '%W %b %d %h:%i %Y') as created
                FROM " . T_TICKETS . "
               WHERE id IN(" . implode(",", $_SESSION["selected_tickets"]) . ")";
    $rows = SQL_select($query);
    $i = 1;
    foreach($rows as $row)
    {
        if((int)$row["cc_member_id"]) $applicant = "Consumer ID: <b>" . (int)$row["cc_member_id"] . "</b>&nbsp;";
        if($row["from_email"] != "") $applicant .= "E-mail: <b>" . trim($row["from_email"]) . "</b>";
        $applicant_name = "";
        if(trim($row["cc_fname"]) != "" || trim($row["cc_lname"]) != "") $applicant_name = "&nbsp;<b>" . ucwords(htmlspecialchars($row["cc_fname"])) . "&nbsp;" . ucwords(htmlspecialchars($row["cc_lname"])) . "</b>";
        $xtpl->assign("APPLICANT", "#" . $i  . ": " . $applicant . $applicant_name . "</br>");
        $xtpl->parse("main.update.group_tickets.group_ticket_info");
        $xtpl->assign("TICKET_INFO", "#" . $i  . ": " . "Owner: <strong>" . get_user_login($row["user_id"]) . "</strong> Queue: <strong>" . get_queue_name($row["group_id"]) . "</strong> Status: <strong>" . $row["status"] . "</strong><br>");
        $i++;
        $xtpl->parse("main.update.group_tickets.group_ticket_info1");
    }
    $xtpl->parse("main.update.group_tickets");
    get_group_operations($xtpl, "main.update", "form1", 0, 0);
    $type_message = array("comment"=>"Add comments", "reply"=>"Send answer");
    $xtpl->assign("TYPE_MESSAGE", get_drop_down_list($type_message, "message_status"));
    $xtpl->assign("MESSAGE_STATUS", $_REQUEST["message_status"]);
   
    if($template > 0) $xtpl->assign("MESSAGE", get_template($template, 0, 0));

}

$query = "SELECT id, name FROM " . T_TEMPLATES . " 
				WHERE template_website='" . $_SESSION['website_url'] . "'";
$rows = SQL_select($query);

$templates_list = array();
if($rows) foreach($rows as $val) $templates_list[$val["id"]] = StripSlashes(htmlspecialchars($val["name"]));
$xtpl->assign("TEMPLATES_LIST", get_drop_down_list($templates_list, "template", "", "Select template", 0, "", " onchange=\"document.getElementById('form2').submit();\""));
get_queues_status($xtpl);
$sign_text=showsignature();
$sign_text2="\n\n\n\n\n".$sign_text;
 if ($template==0)
    $xtpl->assign("MESSAGE",$sign_text2);
 

$xtpl->parse("main.update");
$xtpl->parse("main");
$xtpl->out("main");
?>
