<?
include "validator.php";

if($myDB=="ccss")
{
	if(!is_numeric($_REQUEST['id']))
	{
		header("Location: tickets.php");
	}
}
	if (isset ($_REQUEST['queues']) and is_numeric($_REQUEST['queues']))
	{

	$group_id=$_REQUEST['queues'];
	
	}
	else {
			$group_id=3;
		}

//echo "$body_id";

if($myDB=="ccss")
{
$sql="SELECT name,cname,email,id 
		from confident.ap_members WHERE id=".$_REQUEST['id']." LIMIT 1";

}
else 
{
	$sql="SELECT id , name, ugroup as cname,email  
			from ".T_USERS."
			 WHERE id=".$_REQUEST['managers']." LIMIT 1";
	//echo $sql;
}

$qu=mysql_query($sql);
$res=mysql_fetch_array($qu);

if (empty($res['email']))
{
	
	
	header("Location: tickets.php");
	die("hello");
	
}

if (!isset ($_REQUEST['site']) or empty($_REQUEST['site']))
	$_REQUEST['site']="http://confidentialconnections.com/";
	
	$sql="INSERT INTO " .T_TICKETS." 
					(cc_member_id,cc_fname,cc_lname,from_email,subject,body_id,website,user_id,
				     group_id,created)
						  VALUES('".$res['id']."','".$res['name']."','".$res['cname']."',
						  '".$res['email']."','','','". $_REQUEST['site']."','" .$_SESSION['uid']."','$group_id',NOW())";


$result=mysql_query($sql) or die ("Can not paste");
$sql="SELECT LAST_INSERT_ID() as last FROM " .T_TICKETS. "";
$res2=mysql_fetch_array(mysql_query($sql));
$ticket_id=$res2["last"];

header("Location: show_ticket.php?ticket_id=".$ticket_id."");




						  

?>