<?php
//lib for work with sessions
$sess_db = mysql_connect($myServer, $myUser, $myPass) or die("Can't connect to MySQL");
mysql_select_db($myDB,$sess_db) or die("Can't select DB");
mysql_query("SET NAMES cp1251");
function sess_open ($save_path, $session_name)
{
	sess_gc(MAXLIFETIME);
	return(true);
}

function sess_close()
{
	return(true);
}

function sess_read ($id)
{
	global $sess_db;
	$res = mysql_query("SELECT data FROM " . T_SESSION . " WHERE id='".$id."'", $sess_db);
	if(!mysql_num_rows($res)) return 0;
	$sess_data = mysql_fetch_row($res);
	mysql_query("UPDATE " . T_SESSION . " SET stamp=NOW() WHERE id='".$id."'", $sess_db);
	return($sess_data[0]);
}

function sess_write ($id, $sess_data)
{
	global $sess_db;
	$res = mysql_query("SELECT stamp FROM " . T_SESSION . " WHERE id='".$id."'", $sess_db);
	if (!mysql_num_rows($res))
	{
		if(($ip = $_SERVER['REMOTE_ADDR'])=="") $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		$sql = "INSERT INTO " . T_SESSION . " SET id='".$id."', data='".$sess_data."', ip='".$ip."', uid=" . ((int)$_SESSION['uid']) . ", status='" . ($_SESSION['permission']) . "', stamp=now()";
	}else $sql = "UPDATE " . T_SESSION . " SET data='".$sess_data."', uid=".((int)$_SESSION['uid']).", status='" . ($_SESSION['permission']) . "' WHERE id='".$id."'";
	return  mysql_query($sql, $sess_db);
}

function sess_destroy ($id)
{
	global $sess_db;
	return(mysql_query("DELETE FROM " . T_SESSION . " WHERE id='".$id."'", $sess_db));
}

function sess_gc ($maxlifetime)
{
	global $sess_db;
	mysql_query("DELETE FROM " . T_SESSION . " WHERE stamp<DATE_SUB(NOW(), INTERVAL " . MAXLIFETIME . " SECOND )", $sess_db);
	return true;
}

session_set_save_handler ("sess_open", "sess_close", "sess_read", "sess_write", "sess_destroy", "sess_gc");
?>