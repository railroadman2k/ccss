<?php
/**
 * tickets.php  - handle ticket search operation
 *
 * very similar to the account.php except search parameters in query
 * include validator.php
 */

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
                         "Priority"=>array("field"=>"priority","sort"=>"sort","def_sort_type"=>"desc","sort_order"=>"","url_name"=>"priority"),
                         "From"=>array("field"=>"","sort"=>"","def_sort_type"=>"desc","sort_order"=>"","url_name"=>""),
                         "Complain"=>array("field"=>"complain","sort"=>"sort","def_sort_type"=>"desc","sort_order"=>"","url_name"=>"complain"),
                         "Rate"=>array("field"=>"rate","sort"=>"sort","def_sort_type"=>"desc","sort_order"=>"","url_name"=>"rate"),
                         "Updated"=>array("field"=>"","sort"=>"","def_sort_type"=>"desc","sort_order"=>"","url_name"=>""),
                         "Created"=>array("field"=>"created","sort"=>"default","def_sort_type"=>"desc","sort_order"=>"0","url_name"=>"created"));

list($query, $queue, $ticket_status) = get_query_for_tickets();

if($query != "")
{
    get_group_operations($xtpl, "main.tickets", "form1", (int)$_REQUEST["queue"]);
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

    if($ticket_status != "")
    {
        $add_params["get"][] = "ticket_status=" . $ticket_status;
        $add_params["post"][] = "<input type=\"hidden\" name=\"ticket_status\" value=\"" . $ticket_status . "\">\n";
        $add_params["post1"]["ticket_status"] = $ticket_status;
    }
    $xtpl->assign("ADD_FIELDS", implode(" ", $add_params["post"]));
    $str_params = "";
    if(count($add_params["get"]) > 0) $str_params = "?" . implode("&", $add_params["get"]);
    $href = MAIN_HOST . "tickets.php" . $str_params;
    $href1 = MAIN_HOST . "tickets.php";
    $pg_sel = new Page_Selector($query, $href, $pages, $sort, $cfg_sort_fields);
    $xtpl->assign("SELECTOR_LINKS", $pg_sel->display_selector());
    $xtpl->assign("SELECTOR_NUMROWS", $pg_sel->display_selector_num_rows($add_params["post1"], $href1));
    $xtpl->assign("TABLE_HEADER", $pg_sel->get_table_header());
    if(count($pg_sel->data) > 0)
    {
        foreach($pg_sel->data as $i=>$row)
        {
		
		$xtpl->assign("SUBJECT", "<a href=\"show_ticket.php?ticket_id=" . $row["id"] . "\" class=\"ticket_urgent\">" . StripSlashes(htmlspecialchars($row["subject"])) . "</a>");
            $xtpl->assign("NUM", $i);
            $xtpl->assign("_ID", $i);
            $xtpl->assign("TICKET_ID", $row["id"]);
            $style=color_ticket($row["id"]);
            if ($style==1)
            {
            		$put_this="<span class='complain_on'>Answered</span>";
            		$xtpl->assign("ANSWERED",$put_this);
            }
            
            else 	
            {
	            	$put_this="";
            		$xtpl->assign("ANSWERED",$put_this);

            	
            }
            
            	if (isset ($row["website"]) && $row["website"]=="http://confidentialconnections.com/")
            	{
            		$row["website"]="Confident";
            	}
            if (isset ($row["website"]) && $row["website"]=="http://fianceeconnections.com/")
            	{
            		$row["website"]="Fiancee";
            	}
            	
           	if (isset ($row["website"]) && $row["website"]=="http://wifeconnections.com/")
            	{
            		$row["website"]="Wife";
            	}
            	
            	
            if (isset($row["website"]))
            {
            $xtpl->assign("SITE", $row["website"]);
            }
            $xtpl->assign("SELECT", "<input type=\"checkbox\" id=\"" . $i . "\" name=\"select[]\" value=\"" . $row["id"] . "\" onclick=\"change_tr_class(this)\">");
            if(strlen(trim($row["subject"])) > 0)
            {
                $subject = trim($row["subject"]);
            }else $subject = "No subject";
			
            if($row["priority"]=='urgent')
            {
            $xtpl->assign("SUBJECT", "<a href=\"show_ticket.php?ticket_id=" . $row["id"] . "\" class=\"ticket_urgent\">" . StripSlashes(htmlspecialchars($subject)) . "</a>");
            }
            if($row["priority"]=='normal') 
            {
            	$xtpl->assign("SUBJECT", "<a href=\"show_ticket.php?ticket_id=" . $row["id"] . "\" class=\"ticket\">" . StripSlashes(htmlspecialchars($subject)) . "</a>");
            }
			if($row["priority"]=='high')
			{
				$xtpl->assign("SUBJECT", "<a href=\"show_ticket.php?ticket_id=" . $row["id"] . "\" class=\"ticket_high\">" . StripSlashes(htmlspecialchars($subject)) . "</a>");
			}
			
            
            $query = "SELECT name FROM " . T_GROUPS . " WHERE id=" . $row["group_id"];
            $row1 = SQL_select($query, 0);
            $xtpl->assign("QUEUE", StripSlashes(htmlspecialchars($row1["name"])));
            $xtpl->assign("PRIORITY",$row['priority']);
            $query = "SELECT login FROM " . T_USERS . " WHERE id=" . $row["user_id"];
            $row1 = SQL_select($query, 0);
            $xtpl->assign("OWNER", $row1["login"]);
            if($row["cc_member_id"] > 0) $member = "<span class=\"grey\">member id:</span> <strong>" . $row["cc_member_id"]."</strong>";
            else $member = "<span class=\"grey\">member id:</span> <strong>none</strong>";
            $golden = ($row["golden"] == "true" and (int)$row["cc_member_id"]) ? "<br><span class='golden'>[GOLDEN]</span>":"";
            if(strlen($row["from_email"]) > 0) $from_email = "<span class=\"grey\">email:</span>&nbsp;<strong>" . $row["from_email"]."</strong>";
            else $from_email = "<span class=\"grey\">email:</span> <strong>none</strong>";

            $applicant_name = "";
            if(trim($row["cc_fname"]) != "" || trim($row["cc_lname"]) != "") $applicant_name = "<br><span class=\"grey\">name:</span>&nbsp;<strong>" . ucwords(htmlspecialchars($row["cc_fname"])) . "&nbsp;" . ucwords(htmlspecialchars($row["cc_lname"]))."</strong>";
            $xtpl->assign("FROM", $member . $applicant_name . "<br>" . $from_email . $golden);
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
                $xtpl->assign("COMPLAIN", "No");
                $xtpl->assign("COMPLAIN_STATUS", "complain_off");
            }
            if($row["rate"] != "") $xtpl->assign("RATE", $row["rate"]);
            else $xtpl->assign("RATE", "No");
            $xtpl->parse("main.tickets.list");
        }
    }
}else
{
    header("Location: index.php");
    exit;
}

if(array_key_exists("searching_parameters", $_SESSION)) $xtpl->assign("SECTION_NAME", "Search Results");
else $xtpl->assign("SECTION_NAME", "Tickets");
	if($myDB=='ccss')
	{
	$xtpl->parse("main.tickets.external");
	}
	else 
	{
		$managersinfo=get_manager_data();
		
		$xtpl->assign("INTERNAL_FORM",$managersinfo);
		$xtpl->parse("main.tickets.internal");
	}
$xtpl->parse("main.tickets");
get_queues_status($xtpl);
$xtpl->parse("main");
$xtpl->out("main");
?>