<?
include "validator.php";

if(array_key_exists("selected_ticket_id", $_SESSION)) unset($_SESSION["selected_ticket_id"]);

if(array_key_exists("select", $_REQUEST))
{
    $_SESSION["selected_tickets"] = $_REQUEST["select"];
    $tmp = new group_operations($act);
}
if(array_key_exists("selected_tickets", $_SESSION)) unset($_SESSION["selected_tickets"]);

$cfg_sort_fields = array("#"=>array("field"=>"","sort"=>"","def_sort_type"=>"","sort_order"=>"","url_name"=>""),
                         "<a href=\"#\" onclick=\"return false\" onmouseup=\"select_all_checkboxs('select[]','form1')\" class=\"table_header_text\">Select</a>"=>array("field"=>"","sort"=>"","def_sort_type"=>"","sort_order"=>"","url_name"=>""),
                         "Subject"=>array("field"=>"subject","sort"=>"sort","def_sort_type"=>"desc","sort_order"=>"","url_name"=>"subj"),
                         "Queues"=>array("field"=>"group_id","sort"=>"sort","def_sort_type"=>"desc","sort_order"=>"","url_name"=>"queue"),
                         "Status"=>array("field"=>"status","sort"=>"sort","def_sort_type"=>"desc","sort_order"=>"","url_name"=>"status"),
                         "Owner"=>array("field"=>"user_id","sort"=>"sort","def_sort_type"=>"desc","sort_order"=>"","url_name"=>"owner"),
                         "From"=>array("field"=>"","sort"=>"","def_sort_type"=>"desc","sort_order"=>"","url_name"=>""),
                         "Complain"=>array("field"=>"complain","sort"=>"sort","def_sort_type"=>"desc","sort_order"=>"","url_name"=>"complain"),
                         "Rate"=>array("field"=>"rate","sort"=>"sort","def_sort_type"=>"desc","sort_order"=>"","url_name"=>"rate"),
                         "Updated"=>array("field"=>"","sort"=>"","def_sort_type"=>"desc","sort_order"=>"","url_name"=>""),
                         "Created"=>array("field"=>"created","sort"=>"default","def_sort_type"=>"desc","sort_order"=>"0","url_name"=>"created"));

list($query, $queue, $ticket_status) = get_query_for_tickets();

if($query != "")
{
    get_group_operations($xtpl, "main.request", "form1", (int)$_REQUEST["queue"]);
    $parameters = "";
    if(array_key_exists("searching_parameters", $_SESSION))
    {
        $parameters = get_parameters_string($_SESSION["searching_parameters"]);
        if($parameters != "") $parameters = "<a href=\"#\" onclick=\"return false;\" onmouseup=\"window.external.AddFavorite('" . MAIN_HOST . "search_tickets.php?act=search&" . $parameters . "', 'Search results of tickets')\" class=\"topmenu\">Add search results to favorites</a>";
    }
    $xtpl->assign("SEARCH_LINK", $parameters);

    $add_params = array("get"=>array(), "post"=>array(), "post1"=>array());
    if($queue > 0)
    {
        $add_params["get"][] = "queue=" . $queue;
        $add_params["post"][] = "<input type=\"hidden\" name=\"queue\" value=\"" . $queue . "\">\n";
        $add_params["post1"]["queue"] = $queue;
        $xtpl->assign("QUEUES",$queue);
    }




list($query, $queue, $ticket_status) = get_query_for_tickets();
$row=array();
$data=date("Y-m-d");
$z=1;

 $query = "SELECT id, login FROM " . T_USERS . " WHERE access=1 ORDER BY login";
    $rows = SQL_select($query);
    $users = array();
    foreach($rows as $val) $users[$val["id"]] = $val["login"];
    $xtpl->assign("SEL_OWNERS", get_drop_down_list($users, "owners", "", "owners"));

    $xtpl->assign("SECTION_NAME", "Brakes");
    
    $view_only="request";
    $view_owners=$_SESSION['uid'];
    if (isset($_REQUEST['status']))
    {
    	$view_only=$_REQUEST['status'];
    }
    if (isset($_REQUEST['owners']))
    {
    	$view_owners=$_REQUEST['owners'];
    }
 
$query="SELECT * FROM 
		" . T_TICKETS_ACTIONS . " WHERE user_action='question'";



/*$query = "SELECT *,(TO_DAYS(now()) - TO_DAYS(created)) as created_days_left,
			(TO_DAYS(now()) - TO_DAYS(updated)) as updated_days_left 
		  FROM " . T_TICKETS . " WHERE status='request' ";*/

	
	
    $result = mysql_query($query);
    $num=mysql_numrows($result);
    
    
  
 for ($i=0;$i<$num;$i++)
 {
 	
 	$out=mysql_fetch_object($result);
 	$ticket_id=$out->ticket_id;
 	
 	
 	$query2 = "SELECT *,(TO_DAYS(now()) - TO_DAYS(created)) as created_days_left,
			(TO_DAYS(now()) - TO_DAYS(updated)) as updated_days_left 
		  FROM " . T_TICKETS . " WHERE id=$ticket_id ";
 	
 	$result2 = mysql_query($query2);
 	
 	$out2=mysql_fetch_object($result2);
 	
 	$id=$out2->id;
 	
 	$status=$out2->status;
 	if (!empty($view_only))
 		{
 			if($status!=$view_only)
 				continue;
 		}
 	$body_id=$out2->body_id;
 	$user_id=$out2->user_id;
 		if(!empty($view_owners))
 		{
 			if($user_id!=$view_owners)
 			 continue;
 		}
 	$created_days_left=$out2->created_days_left;
 	$subject=StripSlashes(htmlspecialchars($out2->subject));
 	$group_id=$out2->group_id;
 	
 	$cc_member_id=$out2->cc_member_id;
 	$golden=$out2->golden;
 	$from_email=$out2->from_email;
 	$cc_fname=$out2->cc_fname;
 	$cc_lname=$out2->cc_lname;
 	$status=$out2->status;
 	$updated_days_left=$out2->updated_days_left;
 	$complain=$out2->complain;
 	$rate=$out2->rate;
 	$user_id=$out2->user_id;
 	
 	
 		$xtpl->assign("NUM", $z);
            	
            		$xtpl->assign("TICKET_ID", $id);
            		$xtpl->assign("SELECT", "<input type=\"checkbox\" id=\"" . $i . "\" name=\"select[]\" value=\"" . $id . "\" onclick=\"change_tr_class(this)\">");
        		    if(strlen(trim($subject)) > 0)
            		{
                		$subject = trim($subject);
            		}else $subject = "No subject";
            		
            		
            		
            		$query3="SELECT *,(TO_DAYS(now()) - TO_DAYS(created)) as created_days_left
 				            FROM " . T_TICKETS_ACTIONS . " WHERE 
 				            ticket_id=$ticket_id order by id DESC LIMIT 1";
            		
            		$result3=mysql_query($query3) or die ("call_last");
            		$out3=mysql_fetch_object($result3);
 					$days_left=$out3->created_days_left;
 					$user_action=$out3->user_action;
 					$now_ticket_status=$out3->now_ticket_status;
 					$message_type=$out3->message_type;
 					
 					
            		
 					$query4="SELECT *,(TO_DAYS(now()) - TO_DAYS(created)) as created_days_left
 				            FROM " . T_TICKETS_ACTIONS . " WHERE 
 				            ticket_id=$ticket_id and user_action='' and message_type='incoming'
 				             and body_id!=0 ORDER by id desc LIMIT 1";
 					
 					$res4=mysql_fetch_array(mysql_query($query4));
 					if (isset($res4['id']) and !empty($res4['id']))
            		{
            			$xtpl->assign("SUBJECT", "<a href=\"show_ticket.php?ticket_id=" . $id . "\" class=\"ticket\">RE::" . $subject . "</a>");
            		}	
            		else {
            		$xtpl->assign("SUBJECT", "<a href=\"show_ticket.php?ticket_id=" . $id . "\" class=\"ticket\">" . $subject . "</a>");
            		}
            		
            		
            		
            		
            		$query = "SELECT name FROM " . T_GROUPS . " WHERE id=" . $group_id . "";
            		$row1 = SQL_select($query, 0);
            		
            		$query = "SELECT login FROM " . T_USERS . " WHERE id=" . $user_id . "";
            		$rowx = SQL_select($query, 0);
            		
            		$xtpl->assign("QUEUE", StripSlashes(htmlspecialchars($row1["name"])));
            		
            		$xtpl->assign("OWNER", $rowx["login"]);
            		if($cc_member_id > 0) $member = "<span class=\"grey\">member id:</span> <strong>" . $cc_member_id ."</strong>";
            		else $member = "<span class=\"grey\">member id:</span> <strong>none</strong>";
            		$golden = ($golden == "true" and (int)$cc_member_id) ? "<br><span class='golden'>[GOLDEN]</span>":"";
            		if(strlen($from_email) > 0) $from_email = "<span class=\"grey\">email:</span>&nbsp;<strong>" . $from_email."</strong>";
            		else $from_email = "<span class=\"grey\">email:</span> <strong>none</strong>";

            		$applicant_name = "";
            		if(trim($cc_fname) != "" || trim($cc_lname != "")) $applicant_name = "<br><span class=\"grey\">name:</span>&nbsp;<strong>" . ucwords(htmlspecialchars($cc_fname)) . "&nbsp;" . ucwords(htmlspecialchars($cc_lname))."</strong>";
            		$xtpl->assign("FROM", $member . $applicant_name . "<br>" . $from_email . $golden);
            		$xtpl->assign("STATUS_CLASS", $status);
            		$xtpl->assign("TICKET_STATUS", $status);
            		if(!$updated_days_left) $updated_days_left = "<br><strong>Today</strong>";
            		else $updated_days_left = "<br><strong>" . $updated_days_left . " days ago</strong>";
            		$xtpl->assign("UPDATED", $updated_days_left);
            		if(!$created_days_left) $created_days_left = "<br><strong>Today</strong>";
            		else $created_days_left = "<br><strong>" . $created_days_left . " days ago</strong>";
            			$xtpl->assign("CREATED",  $created_days_left);
            			if($complain!='0000-00-00 00:00:00')
            				{
                				$xtpl->assign("COMPLAIN", "Complain");
                				$xtpl->assign("COMPLAIN_STATUS", "complain_on");
            				}else
            						{
                					$xtpl->assign("COMPLAIN", "No");
                					$xtpl->assign("COMPLAIN_STATUS", "complain_off");
            						}
            					if($rate != "") $xtpl->assign("RATE", $rate);
            					else $xtpl->assign("RATE", "No");
            						$xtpl->parse("main.request.list");
            						$z++;
            					
 }
 $xtpl->parse("main.request");
 //$xtpl->parse("main.tickets");
//$xtpl->parse("main.tickets");
get_queues_status($xtpl);
//get_internal_queues_status($xtpl);

//get_queues_status($xtpl);
$xtpl->parse("main");
$xtpl->out("main");
}
?>