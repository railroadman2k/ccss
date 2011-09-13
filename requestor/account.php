<?php
/**
 * account.php  - this is "index" file, the first page displayed after user login,
 * show the list of new and opened tickets belongs to current user
 *  include validator.php also
 */

    include "validator.php";

    //unset session variables
    if(array_key_exists("selected_ticket_id", $_SESSION)) unset($_SESSION["selected_ticket_id"]);
    if(array_key_exists("searching_parameters", $_SESSION)) unset($_SESSION["searching_parameters"]);
    $xtpl->assign("SECTION_NAME", "My Tickets");
    //getting ticket info
    if(array_key_exists("select", $_REQUEST))
    {
        $_SESSION["selected_tickets"] = $_REQUEST["select"];
        $tmp = new group_operations($act);
    }
    if(array_key_exists("selected_tickets", $_SESSION)) unset($_SESSION["selected_tickets"]);

    get_group_operations($xtpl, "main.account", "form1");

    //sort array
    $cfg_sort_fields = array("#"=>array("field"=>"","sort"=>"","def_sort_type"=>"","sort_order"=>"","url_name"=>""),
                             "<a href=\"#\" onclick=\"return false\" onmouseup=\"select_all_checkboxs('select[]','form1')\" class=\"table_header_text\">Select</a>"=>array("field"=>"","sort"=>"","def_sort_type"=>"","sort_order"=>"","url_name"=>""),
                             "Subject"=>array("field"=>"subject","sort"=>"sort","def_sort_type"=>"","sort_order"=>"","url_name"=>"subj"),
                             "Queues"=>array("field"=>"group_id","sort"=>"sort","def_sort_type"=>"","sort_order"=>"","url_name"=>"queue"),
                             "Status"=>array("field"=>"status","sort"=>"sort","def_sort_type"=>"","sort_order"=>"","url_name"=>"status"),
							 "Priority"=>array("field"=>"priority","sort"=>"sort","def_sort_type"=>"desc","sort_order"=>"","url_name"=>"priority"),
                             "From"=>array("field"=>"","sort"=>"","def_sort_type"=>"","sort_order"=>"","url_name"=>""),
                             "Complain"=>array("field"=>"complain","sort"=>"sort","def_sort_type"=>"","sort_order"=>"","url_name"=>"complain"),
                             "Rate"=>array("field"=>"rate","sort"=>"sort","def_sort_type"=>"","sort_order"=>"","url_name"=>"rate"),
                             "Updated"=>array("field"=>"","sort"=>"","def_sort_type"=>"","sort_order"=>"","url_name"=>""),
                             "Created"=>array("field"=>"created","sort"=>"default","def_sort_type"=>"desc","sort_order"=>"0","url_name"=>"created")
                            
                             );

    //select all new and opened user tickets
    
    if ($_SESSION["website_url"]=='all')
    {
    	$query = "SELECT id, cc_member_id, cc_fname, cc_lname, from_email, subject, body_id, status, user_id, group_id, complain, rate, golden,website,priority,
    				DATE_FORMAT(updated, '%W %d %b %Y %h:%i') as updated1,
                     DATE_FORMAT(created, '%W %d %b %Y %h:%i') as created1,
                     (TO_DAYS(now()) - TO_DAYS(created)) as created_days_left,
                     (TO_DAYS(now()) - TO_DAYS(updated)) as updated_days_left
                FROM " . T_TICKETS . "
               WHERE user_id=" . $_SESSION["uid"] . " AND status IN('new','opened') " . get_groups();
    	
    	
    }
    else 
    {
    $query = "SELECT id, cc_member_id, cc_fname, cc_lname, from_email, subject, body_id, status, user_id, group_id, complain, rate, golden,website,priority,
    				DATE_FORMAT(updated, '%W %d %b %Y %h:%i') as updated1,
                     DATE_FORMAT(created, '%W %d %b %Y %h:%i') as created1,
                     (TO_DAYS(now()) - TO_DAYS(created)) as created_days_left,
                     (TO_DAYS(now()) - TO_DAYS(updated)) as updated_days_left
                FROM " . T_TICKETS . "
               WHERE user_id=" . $_SESSION["uid"] . " AND status IN('new','opened') AND website='" . $_SESSION["website_url"] . "' " . get_groups();
    }

    $href = MAIN_HOST . "account.php";

    //executing query with paging
    $pg_sel = new Page_Selector($query, $href, $pages, $sort, $cfg_sort_fields);

    //assigning general variables
    $xtpl->assign("SELECTOR_LINKS", $pg_sel->display_selector());
    $xtpl->assign("SELECTOR_NUMROWS", $pg_sel->display_selector_num_rows(array(), $href));
    $xtpl->assign("TABLE_HEADER", $pg_sel->get_table_header());

    if(count($pg_sel->data) > 0)
    {
        foreach($pg_sel->data as $i=>$row)
        {
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
            //assigning info for every ticket
            $xtpl->assign("NUM", $i);
            $xtpl->assign("_ID", $i);
            $xtpl->assign("TICKET_ID", $row["id"]);
            $subject=$row["subject"];
			if (empty($subject))
				$subject="No Subject";
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
            
            $xtpl->assign("SELECT", "<input type=\"checkbox\" id=\"" . $i . "\" name=\"select[]\" value=\"" . $row["id"] . "\" onclick=\"change_tr_class(this)\">");
     		if($row["priority"]=='urgent')
            	{
            		$xtpl->assign("SUBJECT", "<a href=\"show_ticket.php?ticket_id=" . $row["id"] . "\" class=\"ticket_urgent\">" . StripSlashes(htmlspecialchars($subject)) . "</a>");
            }
               if($row["priority"]=='normal') 
            {
	           	$xtpl->assign("SUBJECT", "<a href=\"show_ticket.php?ticket_id=" . $row["id"] . "\" 			class=\"ticket\">" . StripSlashes(htmlspecialchars($subject)) . "</a>");
            }
			if($row["priority"]=='high')
			{
				$xtpl->assign("SUBJECT", "<a href=\"show_ticket.php?ticket_id=" . $row["id"] . "\" class=\"ticket_high\">" . StripSlashes(htmlspecialchars($subject)) . "</a>");
			}     
			$query = "SELECT name FROM " . T_GROUPS . " WHERE id=" . $row["group_id"];
            $row1 = SQL_select($query, 0);
            $xtpl->assign("QUEUE", StripSlashes(htmlspecialchars($row1["name"])));
			 $xtpl->assign("PRIORITY",$row['priority']);
            $xtpl->assign("SITE",$row["website"]);
            if($row["cc_member_id"] > 0) $member = "<span class=\"grey\">member id: </span><b> " . $row["cc_member_id"]."</b>";
            else $member = "<span class=\"grey\">member id:<b> none </b>";
            $golden = ($row["golden"] == "true" and $row["cc_member_id"] > 0) ? "<br><span class='golden'>[GOLDEN]</span>":"";
            if(strlen($row["from_email"]) > 0) $from_email = "<b>&nbsp;" . htmlspecialchars($row["from_email"])."</b>";
            else $from_email = "<span class=\"grey\">email:</span><b> no email</b>";
            $applicant_name = "";
            if(trim($row["cc_fname"]) != "" || trim($row["cc_lname"]) != "") $applicant_name = "<b>&nbsp;" . ucwords(htmlspecialchars($row["cc_fname"])) . "&nbsp;" . ucwords(htmlspecialchars($row["cc_lname"]))."</b>";
            $xtpl->assign("FROM", $member ."<br><span class=\"grey\">name:</span>". $applicant_name . "<br> <span class=\"grey\">email:</span>" . $from_email . $golden);
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
            $xtpl->parse("main.account.list");
        }
    }
    $xtpl->parse("main.account");
    get_queues_status($xtpl);
    $xtpl->parse("main");
    $xtpl->out("main");
?>