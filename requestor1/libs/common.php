<?php
/**
 * common.php  - contain all useful functions and classes definitions
 *
 */


//-----------------------------------------
//function made average time of first answer evaluation
//-----------------------------------------
function avg_answer_time($query_name,$pattern,$qreplace)
{
  $r=get_queries($query_name);
  $r=replace_queries($r,$pattern,$qreplace);
  $res=SQL_select($r[0]['replaced_query']);
  if (count($res)!=0)
  {
    $sum=0;
    foreach ($res as $k=>$v )
      $sum=$sum+$v['res'];
    $days=floor($sum/(count($res)*60*24));
    $hours=round((($sum/(count($res)*60))-$days*24),0);
  }
  else
  {
   $days=0;
   $hours=0;
  }
  $avg=array();
  $avg['days']=$days;
  $avg['hours']=$hours;
  return $avg;
}



function get_manager_info($id)
{
	$query="SELECT name,ugroup FROM " .T_USERS ." WHERE id=$id";
	return mysql_fetch_array(mysql_query($query));
}
//-----------------------------------------
//function made average lifetime evaluation
//-----------------------------------------
function avg_lifetime($query_name,$pattern,$qreplace)
{
  $r=get_queries($query_name);
  $r=replace_queries($r,$pattern,$qreplace);
  $res=SQL_select($r[0]['replaced_query']);
  $days=floor($res[0]['res']/(60*24));
  $hours=round(($res[0]['res']/60)-$days*24,0);
  $avg=array();
  $avg['days']=$days;
  $avg['hours']=$hours;
  return $avg;
}

//-----------------------------------------
//function select query from stat_queries
//-----------------------------------------
function get_queries()
{
    $s='';
    $arg_list = func_get_args();
    foreach ($arg_list as $ak=>$av)
       $s=$s."'".$av."',";
    $s=substr($s, 0, -1);
    $query="select  q_name,q_text,q_html_title,q_image,q_color from rt_stat_queries where q_name in (".$s.")";
    $q=SQL_select($query);
    return $q;
}
//-----------------------------------------
//function replace stubs in queries, uses in statistic block
//-----------------------------------------
function replace_queries($r,$pattern,$qreplace)
{
    foreach ($r as $ak=>$av)
    {
      $query = str_replace($pattern,$qreplace,$av['q_text']);
      $r[$ak]['replaced_query']=$query;
    }
    return $r;
}

//-----------------------------------------
//function execute queries, uses in statistic block
//-----------------------------------------
function do_queries_d($qu,$duration)
{
    $d=array();
    foreach(range(1, $duration) as $number)
       $d[$number]=array();
    foreach ($qu as $k=>$v)
    {
      $q=SQL_select($v['replaced_query']);
      foreach ($q as $qk=>$qv )
          $d[$qv['adate']][$v['q_html_title']]=$qv[$v['q_html_title']];
    }
    return $d;
}


//-----------------------------------------
//function SQL_request($query) {}
//return result of query
//-----------------------------------------
function SQL_request($query)
{
    $res = mysql_query($query);
    if($res) return $res;
    else print mysql_errno() . ": " . mysql_error(). "\n<br><b>" . $query . "</b><br>";
    return 0;
}
//-----------------------------------------
//function SQL_select($sql, $r=1, $type=MYSQL_BOTH) {}
//$r=1 - return two-dimensional array, $r=0 - return one-dimensional array
//-----------------------------------------
function SQL_select($sql, $r=1, $type=MYSQL_BOTH)
{
    $ret_arr = array();
    if($res = SQL_request($sql))
    {
        if($r)
        {
            $i=0;
            while ($res_arr = mysql_fetch_array($res, $type)) $ret_arr[$i++]=$res_arr;
            return $ret_arr;
        }else return mysql_fetch_array($res, $type);
    }
    return 0;
}

function get_manager_data()

{
	$query="SELECT id, name, ugroup, position 
			from ".T_USERS."
			 WHERE pass not LIKE '%--%' ORDER by ugroup";
	$list="<select id=manager name='managers' class=\"input1\">\n'";
	$res=mysql_query($query);
	$count=mysql_num_rows($res);
		for($i=0;$i<$count;$i++)
		{
		$row=mysql_fetch_array($res);
		$list .="<option value=\"" .$row['id'] ."\">" .$row['name'] . " | " 
		. $row['ugroup']. "  |  " .$row[position]."
		</option>\n";
		}
	$list .="</select>";
	return $list;
	//print_r($row);
	
}
//-----------------------------------------
//function check_user($email, $password) {}
//return user_id, permissions or return 0, 0
//-----------------------------------------
function check_user($login, $password)
{
    if(($login == "")||($password == "")) return array(0, 0);
    $query = "SELECT id, permissions
                FROM " . T_USERS . "
               WHERE login='" . $login . "'
                 AND pass=PASSWORD('" . $password . "')
                 AND permissions!='system'
                 AND access=1";
    $user = SQL_select($query, 0);
    if($user)
    {
        if($user["id"] > 0)
        {
            return array($user["id"], $user["permissions"]);
        }else{
				print("<h2>DB Error! database fail.1</h2>");
				print_r($user);
				die();
	}
    }else{
				print("<h2><font color = 'red'>Incorrect login or password. Please try again</font></h2>");
				print("login: " . $login . "<br>");
				print("password: " . $password . "<br>");
				print_r($user);
				die();
}
}

//-----------------------------------------
//function check_user_login($login) {}
//return user_id or return 0
//-----------------------------------------
function check_user_login($login)
{
    $query = "SELECT id
                FROM " . T_USERS . "
               WHERE login='" . $login . "'";
    $row = SQL_select($query, 0);
    if($row)
    {
        if($row["id"] > 0) return $row["id"];
        else return 0;
    }else return 0;
}
//-----------------------------------------
//function check_queue_email($email) {}
//return queue
//-----------------------------------------
function check_queue_email($email)
{
    if($email == "") return 0;
    $query = "SELECT id FROM " . T_GROUPS . " WHERE email='" . $email . "'";
    $row = SQL_select($query, 0);
    if($row) return $row["id"];
    else return 0;
}
//-----------------------------------------
//function check_queue_name($name) {}
//return queue
//-----------------------------------------
function check_queue_name($name)
{
    if($name == "") return 0;
    $query = "SELECT id FROM " . T_GROUPS . " WHERE name='" . $name . "'";
    $row = SQL_select($query, 0);
    if($row) return $row["id"];
    else return 0;
}
//------------------------------------------
//function get_enum($tbl, $col) {}
//return $enum;
//------------------------------------------
function get_enum($tbl, $col) {
$res = sql_request("SHOW COLUMNS FROM ".$tbl." LIKE '".$col."'");

$res_arr = mysql_fetch_row($res);
$enum = $res_arr[1];

//$enum = preg_replace("/set[(]([^\)])[)]/", "\\1", $enum);
$enum = ereg_replace("^set\((.*)\)$", "\\1", $enum);
$enum = ereg_replace("^enum\((.*)\)$", "\\1", $enum);

$enum = substr($enum, 1);
$enum = substr($enum, 0, -1);

$enum = explode("','", $enum);

$mas_size=count($enum);

	for ($i=0;$i<$mas_size;$i++)
	{
		if ($enum[$i]=='request')
			unset ($enum[$i]);	
	}

return $enum;
}
//-----------------------------------------
//function get_drop_down_list() {}
//return $list;
//-----------------------------------------
function get_drop_down_list($values, $field_name = "", $table = "", $foption_text = "", $foption_value = 0, $exist_value = "", $addition = "", $multiply = 0)
{
    if(count($values) == 0) $values_is_empty = 1;
    else $values_is_empty = 0;
    if($table != "") $values = get_enum($table, $field_name);
    if(!$multiply) $list = "<select id=\"" . $field_name . "\" name=\"" . $field_name . "\" class=\"input1\" " . $addition . ">\n";
    else $list = "<select id=\"" . $field_name . "[]\" name=\"" . $field_name . "[]\" class=\"input1\" multiple " . $addition . ">\n";
    if($foption_text != "") $list .= "<option value=\"" . $foption_value . "\">" . $foption_text . "</option>\n";
    foreach($values as $key=>$val)
    {
        if($values_is_empty) $k = $val;
        else $k = $key;
        $list .= "<option value=\"" . $k . "\"";
        if(!$multiply)
        {
            if(array_key_exists($field_name, $_REQUEST))
            {
                if($_REQUEST[$field_name] == $k) $list .= " selected";
            }elseif($exist_value == $k) $list .= " selected";
        }else
        {
            $field_name = preg_replace("/[\[\]]/","",$field_name);
            if(array_key_exists($field_name, $_REQUEST))
            {
                if(count($_REQUEST[$field_name]) > 0)
                {
                    foreach($_REQUEST[$field_name] as $v)
                    {
                        if($k == $v)
                        {
                            $list .= " selected";
                            break;
                        }
                    }
                }
            }elseif(is_array($exist_value))
            {
                if(count($exist_value) > 0)
                {
                    foreach($exist_value as $v)
                    {
                        if($k == $v)
                        {
                            $list .= " selected";
                            break;
                        }
                    }
                }
            }
        }
        $list .= ">" . $val . "</option>\n";
    }
    $list .= "</select>\n";
    return $list;
}
//-----------------------------------------
//function get_groups() {}
//return $groups;
//-----------------------------------------
function get_groups($prefix = "", $with_and = 1)
{
    $query = "SELECT DISTINCT group_id FROM " . T_USERS_GROUPS . " WHERE user_id=" . $_SESSION["uid"];
    $rows = SQL_select($query);
    $groups = " AND " . $prefix . "group_id=0 ";
    if($rows)
    {
        $groups = array();
        foreach($rows as $val) $groups[] = $val["group_id"];
        if($with_and) $groups = " AND " . $prefix . "group_id IN('" . implode("','", $groups) . "') ";
        else $groups = $prefix . "group_id IN('" . implode("','", $groups) . "') ";
    }
    return $groups;
}
//-----------------------------------------
//function get_queues_status(&$xtpl) {}
//-----------------------------------------


function color_ticket($ticket_id)
{
	$style=0;
	//�������� ������ �� �������
	
	$query="SELECT * FROM " . T_TICKETS_ACTIONS . " 
			WHERE ticket_id=$ticket_id and message_type='incoming' and body_id!=0 
 			 order by id DESC LIMIT 1";
	
		$result2=mysql_query($query) or die ("calll");
	 	$out2=mysql_fetch_object($result2);
	 	$size=mysql_num_rows($result2);
	 	$num2=1;
	 	
	 	
 		if ($size==0)
 		{
 			$query="select * from ".T_TICKETS_ACTIONS." where ticket_id=".$ticket_id." 
 							   and message_type='outcoming'"; 
 			
 			$out3=mysql_query($query);
 			
 			 if (mysql_num_rows($out3)!=0)
 			 	{
 			 		$style=1;
 			 	
 			 	}
 			 	else
 			 	{
 			 		$style=0;
 			 	}
 			return $style;
 		}
 		else	
 		
 		{
 			
 			
 			
 			$action_id=$out2->id;
 			
 			$query="select * from ".T_TICKETS_ACTIONS." where ticket_id=".$ticket_id." 
 							   and message_type='outcoming' 
 							   and body_id!=0 and id>".$action_id."";
 		 
 			$out3=mysql_query($query);
 			
 			 if (mysql_num_rows($out3)!=0)
 			 {
 			 
 			 	$style=1;
 			 }
 			 
 		}
 return $style;			
	
}

function get_queues_status(&$xtpl)
{
    $status = array("new", "opened");
    $query = "SELECT g.id, g.name
                FROM ccss." . T_GROUPS . " as g, ccss." . T_USERS_GROUPS . " as ug
               WHERE ug.user_id=" . $_SESSION["uid"] . "
                 AND ug.group_id=g.id";
    
    $rows = SQL_select($query);
    if($rows)
    {
    	
    	$query="SELECT count(*) as num_tickets
            				FROM ccss." .T_TICKETS . " 
            				WHERE status='new' AND
            				user_id=".$_SESSION['uid']."";
        $row = SQL_select($query, 0);
        $xtpl->assign("NEW_MY",$row['num_tickets']);
        
        $query="SELECT count(*) as num_tickets
            				FROM ccss." .T_TICKETS . " 
            				WHERE status='opened' AND
            				user_id=".$_SESSION['uid']."";
        $row = SQL_select($query, 0);
        $xtpl->assign("OPEN_MY",$row['num_tickets']);
        
        
          
           
   		    $res=get_all_request();
		  
		   $xtpl->assign("REQ",$res['request']);
		   $xtpl->assign("OPENED_REQ",$res['opened']);
		   $xtpl->assign("NEW_REQ",$res['new']);
		        
        foreach($rows as $val)
        {
            $xtpl->assign("TOTAL_QUEUE", "<a href=\"tickets.php?ext=true&queue=" . $val["id"] . "\" class=\"queue\">" . $val["name"] . "</a>");
            
            if ($_SESSION["website_url"]=="all")
            {
              foreach($status as $val1)
            {
                $query = "SELECT count(id) as num_tickets
                            FROM ccss." . T_TICKETS . "
                           WHERE group_id=" . $val["id"] . "
                             AND status='" . $val1 . "'
                            ";
                $row = SQL_select($query, 0);
                $xtpl->assign("TOTAL_STATUS", "<a href=\"tickets.php?ext=true&queue=" . $val["id"] . "&ticket_status=" . $val1 . "\">" . $row["num_tickets"] . "</a>");
                $xtpl->parse("main.queues_list.list.tickets_status");
            }
            }
            
            else {
            
            foreach($status as $val1)
            {
                $query = "SELECT count(id) as num_tickets
                            FROM ccss." . T_TICKETS . "
                           WHERE group_id=" . $val["id"] . "
                             AND status='" . $val1 . "'
                             AND website='" . $_SESSION["website_url"] . "'";
                $row = SQL_select($query, 0);
                $xtpl->assign("TOTAL_STATUS", "<a href=\"tickets.php?ext=true&queue=" . $val["id"] . "&ticket_status=" . $val1 . "\">" . $row["num_tickets"] . "</a>");
                $xtpl->parse("main.queues_list.list.tickets_status");
            }
            }
            $xtpl->parse("main.queues_list.list");
        }
        $xtpl->parse("main.queues_list");
    }
    
    

   

	
}
	
//-----------------------------------------
//function get_internal_queues_status(&$xtpl) {}
//-----------------------------------------

function get_manager_status(&$xtpl)
{
//print ($_SESSION['permission']);	
//find current page
// ����� �������� � ������� �����������, 

 $current=$_SERVER['PHP_SELF'];
    
    $current=substr($current,1);



	$query="SELECT id,login FROM " . T_USERS . "
			WHERE (ugroup='kharkov' or ugroup='noc')
 			AND access=1;";
	
 			
 	$rows = SQL_select($query);
 	 if($rows)
    	{
        	foreach($rows as $val)
        	{
        		$query2="SELECT count(*) as num FROM " .T_TICKETS . "
        				 WHERE status='new' AND 
        				 user_id= " .$val['id'] ."";
        		
        		
        		$query3="SELECT count(*) as num FROM " .T_TICKETS . "
        				 WHERE status='opened' AND 
        				 user_id= " .$val['id'] ."";
        		
        		
        		$res=mysql_query($query2);
        		$row_new=mysql_fetch_array($res);
        		
        		$res_open=mysql_query($query3);
        		$row_open=mysql_fetch_array($res_open);
        		
        		
        		if ($_SESSION["showmanagers"]=="true")      		
        		{
        		$xtpl->assign("MANAGER_NAME", "<a href=\"search_tickets.php?owner=
        					" .$val['id']."\">" .$val['login'] . "</a>");
        		if ($row_new or $row_open)
        			{
        			  
        				$xtpl->assign("TOTAL_STATUS", $row_new[0]["num"]);
        				$xtpl->parse("main.stat_list.manager_list.list.manager_tickets_status");
        				$xtpl->assign("TOTAL_STATUS", $row_open[0]["num"]);
        				$xtpl->parse("main.stat_list.manager_list.list.manager_tickets_status");
        				$xtpl->parse("main.stat_list.manager_list.list");
        			}
        		 }
        		}
        	
        }
        else {
        		print ("error");
        	  }
        	
        if ($_SESSION['showmanagers']=="true")		
        {
        	$xtpl->assign("TEXT","<a href=\"".$current."?showmanagers=false
        										\">Hide</a>");
        	
        }
        else 
        	{
        	
        		$xtpl->assign("TEXT","<a href=\"".$current."?showmanagers=true
        										\">Show</a>");
        		
        	}
        $xtpl->parse("main.stat_list.manager_list");
 			
	
}

function get_all_request()

{
	
//����� ��� ������������ � �� ������� ��� �� ������� �����
	$kolvo=0; //���-�� �� ���������� �������
	$open=0; //���-�� ������� �� ������� ��� ������� ����� � � �������� ��� �������� ���� ������
	$new=0;
	// ����� ��� ������� ������� ���� ������ 
	$query="SELECT * FROM 
		ccss." . T_TICKETS_ACTIONS . " WHERE user_action='question' 
									    AND now_user_id=$_SESSION[uid]";
	
	$result=mysql_query($query);
    $result = mysql_query($query);
    $num=mysql_numrows($result);
    
    
     for ($i=0;$i<$num;$i++)
 	{
 	
 		$out=mysql_fetch_object($result);
 		$ticket_id=$out->ticket_id;
 	
 	
 		$query2 = "SELECT *,(TO_DAYS(now()) - TO_DAYS(created)) as created_days_left,
			(TO_DAYS(now()) - TO_DAYS(updated)) as updated_days_left 
		  FROM ccss." . T_TICKETS . " WHERE id=$ticket_id AND user_id=$_SESSION[uid]";
 	
 		$result2 = mysql_query($query2);
 	
 		$out2=mysql_fetch_object($result2);
 	
 		$id=$out2->id;
 	
 	$status=$out2->status;
 	
 			if ($status=="new")
 			{
 				$new++;
 				continue;
 			}
 			if($status=="request")
 			{
 				$kolvo++;
 				continue;
 			}
 			if ($status=="opened")
 			{
 				$open++;
 				continue;
 			}
 	
 		
 		
}
$result=array();
$result['request']=$kolvo;
$result['opened']=$open;
$result['new']=$new;

return $result;
}

//-----------------------------------------
//function get_query_for_tickets() {}
//-----------------------------------------
function get_query_for_tickets()
{
    if(array_key_exists("queue", $_REQUEST))
    {
        if(!is_array($_REQUEST["queue"]))
            if(array_key_exists("searching_parameters", $_SESSION)) unset($_SESSION["searching_parameters"]);
    }
    if(!array_key_exists("searching_parameters", $_SESSION))
    {
        if(array_key_exists("queue", $_REQUEST)) $queue = (int)$_REQUEST["queue"];
        else $queue = 0;
        if(array_key_exists("ticket_status", $_REQUEST)) $ticket_status = $_REQUEST["ticket_status"];
        else $ticket_status = "";
        if($queue > 0)
        {
            $query = "SELECT group_id
                        FROM " . T_USERS_GROUPS . "
                       WHERE group_id=" . $queue . "
                         AND user_id=" . $_SESSION["uid"];
            $row = SQL_select($query, 0);
            if(!$row) $queue = 0;
        }
        if($queue == 0)
        {
            $query = "SELECT g.id
                        FROM " . T_GROUPS . " as g,
                             " . T_USERS_GROUPS . " as ug
                       WHERE ug.user_id=" . $_SESSION["uid"] . "
                         AND ug.group_id=g.id";
            $row = SQL_select($query, 0);
            if($row) $queue = $row["id"];
            else return array("", "", "");
        }
        $query_t_status = "";
        if(strlen($ticket_status) > 0)
        {
            $t_status = get_enum(T_TICKETS, "status");
            $status_exists = 0;
            foreach($t_status as $val)
            {
                if($val == $ticket_status)
                {
                    $status_exists = 1;
                    break;
                }
            }
            if($status_exists == 1) $query_t_status = " AND status='" . $ticket_status . "'";
            else $query_t_status = " AND status IN('new','opened') ";
        }else $query_t_status = " AND status IN('new','opened') ";
        
        	if ($_SESSION["website_url"]=="all")
        	{
        		$query = "SELECT id,
                         cc_member_id,
                         cc_fname,
                         cc_lname,
                         from_email,
                         subject,
                         status,
                         user_id,
                         group_id,
                         rate,
                         complain,
                         golden,
                         priority,
                         website,
                         DATE_FORMAT(updated, '%W %d %b %Y %h:%i') as updated1,
                         DATE_FORMAT(created, '%W %d %b %Y %h:%i') as created1,
                         (TO_DAYS(now()) - TO_DAYS(created)) as created_days_left,
                         (TO_DAYS(now()) - TO_DAYS(updated)) as updated_days_left
             FROM " . T_TICKETS . "
             WHERE  group_id=" . $queue . $query_t_status;
        		
        	}
        else {	
        $query = "SELECT id,
                         cc_member_id,
                         cc_fname,
                         cc_lname,
                         from_email,
                         subject,
                         status,
                         user_id,
                         group_id,
                         rate,
                         priority,
                         complain,
                         golden,
                         DATE_FORMAT(updated, '%W %d %b %Y %h:%i') as updated1,
                         DATE_FORMAT(created, '%W %d %b %Y %h:%i') as created1,
                         (TO_DAYS(now()) - TO_DAYS(created)) as created_days_left,
                         (TO_DAYS(now()) - TO_DAYS(updated)) as updated_days_left
                    FROM " . T_TICKETS . "
                   WHERE website='" . $_SESSION["website_url"] . "' AND group_id=" . $queue . $query_t_status;
        }
        return array($query, $queue, $ticket_status);
    }else
    {
        $conditions = array();
        $search_params = $_SESSION["searching_parameters"];
        if((int)$search_params["owner"])
        {
            if($search_params["owner_cond"] == "appear") $conditions[] = "user_id=" . (int)$search_params["owner"];
            else $conditions[] = "user_id!=" . (int)$search_params["owner"];
        }
 //    if(preg_match("/[_a-zA-z0-9\-]+(\.[_a-zA-z0-9\-]+)*\@[_a-zA-z0-9\-]+(\.[a-zA-z]{2,3})+/",$search_params["email"]))
       // {
            if($search_params["email_cond"] == "contain") $conditions[] = "from_email LIKE '%" . $search_params["email"] . "%'";
            elseif($search_params["email_cond"] == "not contain") $conditions[] = "from_email NOT LIKE '%" . $search_params["email"] . "%'";
            elseif($search_params["email_cond"] == "appear") $conditions[] = "from_email = '" . $search_params["email"] . "'";
            elseif($search_params["email_cond"] == "not appear") $conditions[] = "from_email != '" . $search_params["email"] . "'";
       // }
        if($search_params["cc_member_id"]!='')
        {
            $conditions[] = "cc_member_id like '{$search_params["cc_member_id"]}'";
        }
        if(strlen(trim($search_params["subject"])) > 0)
        {
            if($search_params["subj_cond"] == "contain") $conditions[] = "subject LIKE '%" . $search_params["subject"] . "%'";
            elseif($search_params["subj_cond"] == "not contain") $conditions[] = "subject NOT LIKE '%" . $search_params["subject"] . "%'";
            elseif($search_params["subj_cond"] == "appear") $conditions[] = "subject = '" . $search_params["subject"] . "'";
            elseif($search_params["subj_cond"] == "not appear") $conditions[] = "subject != '" . $search_params["subject"] . "'";
        }
        if(count($search_params["queue"]) > 0)
        {
            if($search_params["queue_cond"] == "appear")
            {
                $selected_groups = array();
                foreach($search_params["queue"] as $val) $selected_groups[] = "group_id=" . (int)$val;
                $conditions[] = "(" . implode(" OR ", $selected_groups) . ")";
            }else
            {
                $selected_groups = array();
                foreach($search_params["queue"] as $val) $selected_groups[] = "group_id!=" . (int)$val;
                $conditions[] = "(" . implode(" OR ", $selected_groups) . ")";
            }
        }else $conditions[] = get_groups("", 0);
        if(count($search_params["status"]))
        {
            if($search_params["status_cond"] == "appear")
            {
                $selected_groups = array();
                foreach($search_params["status"] as $val) $selected_groups[] = "status='" . $val . "'";
                $conditions[] = "(" . implode(" OR ", $selected_groups) . ")";
            }else
            {
                $selected_groups = array();
                foreach($search_params["status"] as $val) $selected_groups[] = "status!='" . $val . "'";
                $conditions[] = "(" . implode(" OR ", $selected_groups) . ")";
            }
        }
        if($search_params["complain"]!='-1')
        {
            $complain     = "1";
            $complain     = ($search_params["complain"] == '1') ? "complain != '0000-00-00 00:00:00'":$complain;
            $complain     = ($search_params["complain"] == '0') ? "complain = '0000-00-00 00:00:00'":$complain;
            $conditions[] = "($complain)";
        }
        if ($search_params["older_than_d"]!='') {
            $now          = time();
            $past         = 24*60*60*$search_params["older_than_d"];
            $older_than   = date("Y-m-d",$now-$past);
            $sign         = ($search_params["older_than"]=='1') ? ">=":"<=";
            $conditions[] = "(created $sign '$older_than')";
        }
        if ($search_params["rate"]!='-') {
            $rate         = "%";
            switch ($search_params["rate"]) {
                case '5':$rate = 'My problem (question) has been resolved completely and in short terms';break;
                case '4':$rate = 'My problem (question) has been resolved but it took too much time';break;
                case '3':$rate = 'I am not satisfied with the reply I received';break;
                case '2':$rate = "I don%t understand your reply";break;
                case '1':$rate = 'My problem (question) has not been resolved';break;
            }
            $conditions[] = "(rate like '$rate')";
        }
        
        
        if ($_SESSION["website_url"]=="all")
        {
        	$query = "SELECT id,
                         cc_member_id,
                         from_email,
                         subject,
                         status,
                         user_id,
                         group_id,
                         rate,
                         complain,
                         golden,
                         DATE_FORMAT(updated, '%W %d %b %Y %h:%i') as updated1,
                         DATE_FORMAT(created, '%W %d %b %Y %h:%i') as created1,
                         (TO_DAYS(now()) - TO_DAYS(created)) as created_days_left,
                         (TO_DAYS(now()) - TO_DAYS(updated)) as updated_days_left
                    FROM " . T_TICKETS . " WHERE 1=1 
                    ";
        if(count($conditions) > 0) $query .= " AND " . implode(" AND ", $conditions);
//        print $query;

        	
        	
        }
        
else 	{        
        $query = "SELECT id,
                         cc_member_id,
                         from_email,
                         subject,
                         status,
                         user_id,
                         group_id,
                         rate,
                         complain,
                         golden,
                         DATE_FORMAT(updated, '%W %d %b %Y %h:%i') as updated1,
                         DATE_FORMAT(created, '%W %d %b %Y %h:%i') as created1,
                         (TO_DAYS(now()) - TO_DAYS(created)) as created_days_left,
                         (TO_DAYS(now()) - TO_DAYS(updated)) as updated_days_left
                    FROM " . T_TICKETS . "
                   WHERE website='" . $_SESSION["website_url"] . "' ";
			}
        if(count($conditions) > 0) $query .= " AND " . implode(" AND ", $conditions);
//        print $query;
        return array($query, 0, "");
    }
}
//-----------------------------------------
//function show_ticket_menu($showticket_menu = array()) {}
// return $menu;
//-----------------------------------------
function show_ticket_menu($showticket_menu = array())
{   $menu = "";
    foreach($showticket_menu as $key=>$val) $menu .= "<a href=\"" . $val . "\" class=\"topmenu\">" . $key . "</a>&nbsp;";
    return $menu;
}
//-----------------------------------------
//function get_queue_name($queue_id) {}
// return $queue_name;
//-----------------------------------------
function get_queue_name($queue_id)
{
    $query = "SELECT name FROM " . T_GROUPS . " WHERE id=" . $queue_id;
    $row = SQL_select($query, 0);
    if($row) return $row["name"];
    else return "";
}
//-----------------------------------------
//function get_user_login($user_id) {}
// return $login;
//-----------------------------------------
function get_user_login($user_id)
{
    if(!$user_id) return "";
    $query = "SELECT login FROM " . T_USERS . " WHERE id=" . $user_id;
    $row = SQL_select($query, 0);
    if($row) return $row["login"];
    else return "";
}
//-----------------------------------------
//function update_ticket_time($ticket_id) {}
//-----------------------------------------
function update_ticket_time($ticket_id)
{
    $query = "UPDATE " . T_TICKETS . " SET updated=now() WHERE id=" . $ticket_id;
    SQL_request($query);
}
//-----------------------------------------
//function get_parameters_string($arr) {}
//-----------------------------------------
function get_parameters_string($arr)
{
    $parameters = "";
    if(count($arr) > 0)
    {
        $temp = array();
        foreach($arr as $key=>$val)
        {
            if(!is_array($val)) $temp[] = $key . "=" . rawurlencode($val);
            else foreach($val as $v) $temp[] = $key . "[]=" . rawurlencode($v);
        }
        if(count($temp) > 0) $parameters .= implode("&", $temp);
    }
    return $parameters;
}
//-----------------------------------------
//function get_group_operations(&$xtpl, $path, $form_id, $group_id) {}
//-----------------------------------------
function get_group_operations(&$xtpl, $path, $form_id, $group_id = 0, $need_reply = 1)
{
    $query = "SELECT id, login FROM " . T_USERS . " WHERE access=1 ORDER BY login";
    $rows = SQL_select($query);
    $users = array();
    foreach($rows as $val) $users[$val["id"]] = $val["login"];
    $xtpl->assign("NEW_OWNERS", get_drop_down_list($users, "owner", "", "owner"));
    $status = get_enum(T_TICKETS, "status");
    $tmp_status = array();
    foreach($status as $val) if($val != $row["status"]) $tmp_status[$val] = $val;
    $xtpl->assign("NEW_STATUS", get_drop_down_list($tmp_status, "status", "", "status", ""));
    $query = "SELECT DISTINCT g.id, g.name
                FROM " . T_USERS_GROUPS . " as ug, " . T_GROUPS . " as g
               WHERE ug.user_id=" . $_SESSION["uid"] . "
                 AND ug.group_id=g.id
                 AND g.id != " . $group_id;
    $rows = SQL_select($query);
    $queues = array();
    foreach($rows as $val) $queues[$val["id"]] = $val["name"];
    $xtpl->assign("NEW_QUEUES", get_drop_down_list($queues, "new_queue", "", "queue", 0));
    $xtpl->assign("UPDATE", "document.getElementById('act').value='update'; document.getElementById('" . $form_id . "').submit();");
    if($need_reply)
    {
        $xtpl->assign("GA_TITLE", "Group Actions");
        $xtpl->assign("REPLY", "document.getElementById('act').value='reply'; document.getElementById('" . $form_id . "').submit();");
        $xtpl->assign("ADD_COMMENTS", "document.getElementById('act').value='add_comment'; document.getElementById('" . $form_id . "').submit();");
        $xtpl->parse($path . ".group_operations.write_message");
    }else $xtpl->assign("GA_TITLE", "Actions");
    $xtpl->parse($path . ".group_operations");
}
//-----------------------------------------
//class group_operations() {}
//-----------------------------------------
class group_operations
{
    var $action;

    function group_operations($action, $del_sel = 0)
    {
        $this->action = $action;
        if($this->action == "update")
        {
        	//print_r($_SESSION['selected_tickets']);
        	//print_r($_REQUEST);
            $this->change_owner();
            $this->change_status();
            $this->change_queue();
            if(!$del_sel) unset($_SESSION["selected_tickets"]);
        }elseif($this->action == "reply" || $this->action == "add_comment") $this->update();
    }

    function change_owner()
    {
    	
        if((int)$_REQUEST["owner"] > 0)
        {
            $query = "SELECT id FROM " . T_USERS . " WHERE id=" . (int)$_REQUEST["owner"];
            $row = SQL_select($query, 0);
            if($row)
            {
                foreach($_SESSION["selected_tickets"] as $ticket_id)
                {
                    $query = "SELECT status, user_id, group_id FROM " . T_TICKETS . " WHERE id=" . $ticket_id;
                    $row1 = SQL_select($query, 0);
                    if((int)$_REQUEST["owner"] == $_SESSION["uid"])
                    {
                        $user_action = "take_ticket";
                        $prev_user_id = $row1["user_id"];
                    }else
                    {
                        $user_action = "gave_ticket";
                        $prev_user_id = $_SESSION["uid"];
                    }
                    if($row1["user_id"] != (int)$_REQUEST["owner"])
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
                                            '" . $user_action . "',
                                            " . (int)$_REQUEST["owner"] . ",
                                            " . $prev_user_id . ",
                                            '" . $row1["status"] . "',
                                            '" . $row1["status"] . "',
                                            " . $row1["group_id"] . ",
                                            " . $row1["group_id"] . ",
                                            now())";
                        SQL_request($query);
                        $query = "UPDATE " . T_TICKETS . " SET user_id=" . (int)$_REQUEST["owner"] . ", updated=now() WHERE id=" . $ticket_id;
                        SQL_request($query);
                    }
                    ini_set("max_execution_time", 29);
                }
            }
        }
    }

    function change_status()
    {
        if($_REQUEST["status"] != "")
        {
            foreach($_SESSION["selected_tickets"] as $ticket_id)
            {
                $query = "SELECT status, user_id, group_id FROM " . T_TICKETS . " WHERE id=" . $ticket_id;
                $row1 = SQL_select($query, 0);
                if($row1["status"] != $_REQUEST["status"])
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
                ini_set("max_execution_time", 29);
            }
        }
    }

    function change_queue()
    {
        if((int)$_REQUEST["new_queue"] > 0)
        {
            $query = "SELECT group_id
                        FROM " . T_USERS_GROUPS . "
                       WHERE user_id=" . $_SESSION["uid"] . "
                         AND group_id=" . $_REQUEST["new_queue"];
            $row = SQL_select($query, 0);
            if($row)
            {
                foreach($_SESSION["selected_tickets"] as $ticket_id)
                {
                    $query = "SELECT status, user_id, group_id FROM " . T_TICKETS . " WHERE id=" . $ticket_id;
                    $row1 = SQL_select($query, 0);
                    if($row1["group_id"] != (int)$_REQUEST["new_queue"])
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
                                            'changed_group',
                                            " . $_SESSION["uid"] . ",
                                            " . $row1["user_id"] . ",
                                            '" . $row1["status"] . "',
                                            '" . $row1["status"] . "',
                                            " . (int)$_REQUEST["new_queue"] . ",
                                            " . $row1["group_id"] . ",
                                            now())";
                        SQL_request($query);
                        $query = "UPDATE " . T_TICKETS . " SET group_id=" . (int)$_REQUEST["new_queue"] . ", updated=now() WHERE id=" . $ticket_id;
                        SQL_request($query);
                    }
                    ini_set("max_execution_time", 29);
                }
            }
        }
    }

    function update()
    {
        header("Location: " . MAIN_HOST . "update.php?message_status=" . $this->action);
        exit;
    }
}
//-----------------------------------------
//function get_check_boxs($field_name,$separator = "&nbsp;",$predef_value = "") {}
//return $list_of_boxs;
//-----------------------------------------
function get_check_boxs($values, $field_name, $table, $cols)
{
    $enum = '';
    if($table != "") $values = get_enum($table, $field_name);

    $i=0;
    $enum .= '<TABLE width="60%" border="0">';
    $enum .= '<TR>';

    foreach($values as $key=>$val)
    {
        if($table != "") $k = $val;
        else $k = $key;
        $enum .= '<TD WIDTH="' . ((int) 100/$cols) . '%" class="small2"><INPUT type="checkbox" name="' . $field_name . '[]" value="' . $k . '"';
        if(array_key_exists($field_name, $_REQUEST))
        {
            if(is_array($_REQUEST[$field_name]))
            {
                if(count($_REQUEST[$field_name]) > 0)
                {
                    foreach($_REQUEST[$field_name] as $v1)
                    {
                        if($k == $v1)
                        {
                            $enum .= " checked";
                            break;
                        }
                    }
                }
            }
        }
        $enum .= '>' . $val . '</TD>';
        if (!(++$i % $cols)) $enum .= '</TR><TR>';
    }

    if ($i++ % $cols) $enum .= "<TD colspan=".(($cols - 1) - (++$i % $cols)).">&nbsp;</TD>";

    $enum .= '</TR></TABLE>';

    return $enum;
}
//-----------------------------------------
//function get_template($id) {}
//return $template_body;
//-----------------------------------------
function get_template($id, $t_id = 0, $b_id = 0)
{
    $t_message = "";
    $query = "SELECT message FROM " . T_TEMPLATES . " WHERE id=" . $id;
    $row = SQL_select($query, 0);
    if($row)
    {
        $t_message = StripSlashes($row["message"]);
        $query = "SELECT id, name, field_name, table_name, from_session FROM " . T_TEMPLATE_VARS;
        $rows = SQL_select($query);
        if($rows)
        {
            foreach($rows as $val)
            {
                if(!$val["from_session"])
                {
                    if ($t_id == 0)
                        $query = "SELECT default_value as " . $val["field_name"] . " FROM " . T_TEMPLATE_VARS . " WHERE id=" . $val['id'];
                    else

                        if($val["table_name"] = T_TICKETS) $query = "SELECT " . $val["field_name"] . " FROM " . $val["table_name"] . " WHERE id=" . $t_id;
                        elseif($val["table_name"] = T_BODIES) $query = "SELECT " . $val["field_name"] . " FROM " . $val["table_name"] . " WHERE id=" . $b_id;

                    $row1 = SQL_select($query, 0);
                    $t_message = preg_replace("/\{" . strtoupper($val["name"]) . "\}/",$row1[$val["field_name"]],$t_message);
                }else $t_message = preg_replace("/\{" . strtoupper($val["name"]) . "\}/",$_SESSION[$val["name"]],$t_message);
            }
        }
    }
    $query = "SELECT signature FROM " . T_SIGNATURES . " WHERE user_id=" . $_SESSION["uid"];
    $row = SQL_select($query, 0);
    $t_message .= "\n\n" . $row["signature"];
    return StripSlashes($t_message);
}

//-----------------------------------------
//function count time of ticket lifetime
//-----------------------------------------
function countTime($seconds='0')
{
    $secondsZ= 1;
    $minutsZ = 60*$secondsZ;
    $hoursZ  = 60*$minutsZ;
    $daysZ   = 24*$hoursZ;

    $days    = round($seconds/$daysZ-.5);
    $seconds-= $days*$daysZ;

    $hours   = round($seconds/$hoursZ-.5);
    $seconds-= $hours*$hoursZ;

    $minuts  = round($seconds/$minutsZ-.5);
    $seconds-= $minuts*$minutsZ;
    $result  = array($seconds,$minuts,$hours,$days);
    return $result;
}


// Work with signature of users //

function showsignature()
{
	$query = "SELECT signature FROM " . T_SIGNATURES . " WHERE user_id=" . $_SESSION["uid"];
    $row = SQL_select($query, 0);
    $t_message = $row["signature"];
    return StripSlashes($row["signature"]);
}

function savesignature($signature)
{

	$query = "SELECT signature FROM " . T_SIGNATURES . " WHERE user_id=" . $_SESSION["uid"];
    $row = SQL_select($query, 0);
    if (empty($row))
    	{
    		$query="INSERT INTO " .T_SIGNATURES ." 
    		VALUES ('$_SESSION[uid]','$signature') WHERE user_id=" .$_SESSION["uid"];
    		
    	
    	}
    	
    else 
    	{
    		$query="UPDATE " .T_SIGNATURES . " SET signature='$signature' 
    									WHERE user_id=" .$_SESSION["uid"];
    		
    	}
    $row=SQL_request($query);
   	
	
}

?>
