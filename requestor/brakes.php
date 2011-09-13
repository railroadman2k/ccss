<?


include "validator.php";
if(array_key_exists("selected_ticket_id", $_SESSION)) unset($_SESSION["selected_ticket_id"]);
 get_group_operations($xtpl, "#", "form1", (int)$_REQUEST["queue"]);
    $parameters = "";
if(array_key_exists("select", $_REQUEST))
{
    $_SESSION["selected_tickets"] = $_REQUEST["select"];
    $tmp = new group_operations($act);
}

if($_SESSION['permission'] != "admin")
{
    header("Location: index.php");
    exit;
}




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



$row=array();
$data=date("Y-m-d");
$z=1;


$xtpl->assign("SECTION_NAME", "Brakes");
 

$query = "SELECT *,(TO_DAYS(now()) - TO_DAYS(created)) as created_days_left,
			(TO_DAYS(now()) - TO_DAYS(updated)) as updated_days_left 
		  FROM " . T_TICKETS . " WHERE (status='new' OR status='opened')";

	$i=0;
	
    $result = mysql_query($query);
    $num=mysql_numrows($result);
    
  
 while($i<$num)
 {
 	
 	$out=mysql_fetch_object($result);
 	$id=$out->id;
 	$status=$out->status;
 	$body_id=$out->body_id;
 	$user_id=$out->user_id;
 	$created_days_left=$out->created_days_left;
 	$subject=StripSlashes(htmlspecialchars($out->subject));
 	$group_id=$out->group_id;
 	$cc_member_id=$out->cc_member_id;
 	$golden=$out->golden;
 	$from_email=$out->from_email;
 	$cc_fname=$out->cc_fname;
 	$cc_lname=$out->cc_lname;
 	$status=$out->status;
 	$updated_days_left=$out->updated_days_left;
 	$complain=$out->complain;
 	$rate=$out->rate;
 	$user_id=$out->user_id;
 
 	
 	
 		
 	$query2="SELECT *,(TO_DAYS(now()) - TO_DAYS(created)) as created_days_left
 			
 		FROM " . T_TICKETS_ACTIONS . " WHERE ticket_id=$id and message_type='outcoming' order by id DESC LIMIT 1";
 	$result2=mysql_query($query2) or die ("calll");
 	$out2=mysql_fetch_object($result2);
 	$num2=1;
 	$test=$out2->id;
 	
 		if (empty($out2) and $updated_days_left>5)
 		{
 			$z++;
 			
 			$xtpl->assign("NUM", $z);
            	
            $xtpl->assign("TICKET_ID", $id);
            $xtpl->assign("SELECT", "<input type=\"checkbox\" id=\"" . $i . "\" name=\"select[]\" value=\"" . $id . "\" onclick=\"change_tr_class(this)\">");
        	if(strlen(trim($subject)) > 0)
            	{
            	$subject = trim($subject);
            	}else $subject = "No subject";
            $xtpl->assign("SUBJECT", "<a href=\"show_ticket.php?ticket_id=" . $id . "\" class=\"ticket\">" . $subject . "</a>");
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
            						$xtpl->parse("main.tickets.list");
 		
 		 			
 		}

else
 	
	{	
			//$ticket_id=$id;
 			
 			$days_left=$out2->created_days_left;
 			$body_id=$out2->body_id;
 			$user_action=$out2->message_type;
 			$user_actions2=$out2->user_action;
 			$action_id=$out2->id;
 			
 			
 				if ($user_action=="outcoming" && $updated_days_left>5) 
 					 
 				{
 					$query="select * from ".T_TICKETS_ACTIONS." where ticket_id=".$id." 
 							   and message_type!='outcoming' 
 							   and body_id!=0 and id>".$action_id."";
 					
 					$res=mysql_query($query);
 					$num_kol=mysql_num_rows($res);
 					if ($num_kol==0)
 					{
 						$i++;
 						continue;
 					}
 					
 					 
            		$xtpl->assign("NUM", $z);
            	
            		$xtpl->assign("TICKET_ID", $id);
            		$xtpl->assign("SELECT", "<input type=\"checkbox\" id=\"" . $i . "\" name=\"select[]\" value=\"" . $id . "\" onclick=\"change_tr_class(this)\">");
        		    if(strlen(trim($subject)) > 0)
            		{
                		$subject = trim($subject);
            		}else $subject = "No subject";
            		$xtpl->assign("SUBJECT", "<a href=\"show_ticket.php?ticket_id=" . $id . "\" class=\"ticket\">" . $subject . "</a>");
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
            						$xtpl->parse("main.tickets.list");
            						$z++;
            					
        
   				 }
 					
 		
 					
 					
 	}
 	$i++;
 
 	
 }
    

$xtpl->parse("main.tickets");
//$xtpl->parse("main.tickets");
get_queues_status($xtpl);
//get_internal_queues_status($xtpl);

//get_queues_status($xtpl);
$xtpl->parse("main");
$xtpl->out("main");
    /*$_SESSION["searching_parameters"] = array(

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
                                               "complain" => -1,
                                               "rate" => "-",
                                               "cc_member_id" =>"",
                                               "older_than" => -1,
                                               "older_than_d" => 2
                                              );*/
    
//  print "<pre>";
//  print "<a href='tickets.php'>see search result</a>";
//  print_r($_SESSION["searching_parameters"]);
//  print "</pre>";
   // header("Location: tickets.php");
    exit;
?>