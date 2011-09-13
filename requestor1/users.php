<?php
/**
 * users.php - manage user essense: allow add/edit/delete users and view all users list
 *
 * also allow to change user acces mode and view user Login history
 * include validator.php
 * available only for admin users
 */

include "validator.php";

//check user authorization for this file
if($_SESSION['permission'] != "admin")
{
    header("Location: index.php");
    exit;
}
if(array_key_exists("id", $_REQUEST)) $id = (int)$_REQUEST["id"];
else $id = 0;
if($act == "save")
{
    $error = "";
    $login = preg_replace('/[^0-9a-zA-Z\_\-]/','',StripSlashes(rawurldecode($_REQUEST['login'])));
    $password = preg_replace('/[^0-9a-zA-Z\_\-]/','',StripSlashes(rawurldecode($_REQUEST['password'])));
    $repassword = preg_replace('/[^0-9a-zA-Z\_\-]/','',StripSlashes(rawurldecode($_REQUEST['repassword'])));
    if(array_key_exists("queues", $_REQUEST)) $queues = (array)$_REQUEST["queues"];
    else $queues = array();
    if(strlen($login) < 3) { $valid_info = 0; $error .= " - Login must be greater than 3 characters.<br>"; $xtpl->assign("CLASS_LOGIN", "class=\"error_text\""); }
    if(!$id)
    {
        if(strlen($password) < 3) { $valid_info = 0; $error .= " - You must enter the password, he must be greater than 3 characters.<br>"; $xtpl->assign("CLASS_PASSWORD", "class=\"error_text\""); }
        if($password != $repassword) { $valid_info = 0; $error .= " - The passwords you entered do not match.  Please try again.<br>"; $xtpl->assign("CLASS_PASSWORD", "class=\"error_text\""); $xtpl->assign("CLASS_REPASSWORD", "class=\"error_text\""); }
    }elseif($id > 0)
    {
        if(($password != "")&&($repassword == "")) {
             $valid_info = 0;
             $error .= " - You must enter retype the password.<br>";
             $xtpl->assign("CLASS_REPASSWORD", "class=\"error_text\"");
        }elseif(($password == "")&&($repassword != "")) {
            $valid_info = 0;
            $error .= " - You must enter the password.<br>";
            $xtpl->assign("CLASS_PASSWORD", "class=\"error_text\"");
        }elseif(($password != "")&&($repassword != "")) {
            if(strlen($password) < 3) { $valid_info = 0; $error .= " - You must enter the password, he must be greater than 3 characters.<br>"; $xtpl->assign("CLASS_PASSWORD", "class=\"error_text\""); }
            if($password != $repassword) { $valid_info = 0; $error .= " - The passwords you entered do not match.  Please try again.<br>"; $xtpl->assign("CLASS_PASSWORD", "class=\"error_text\""); $xtpl->assign("CLASS_REPASSWORD", "class=\"error_text\""); }
        }
    }
    if(count($queues) == 0) { $valid_info = 0; $error .= " - You must select the queue.<br>"; $xtpl->assign("CLASS_QUEUES", "class=\"error_text\""); }
    if(!$id)
    {
        if(check_user_login($login)) { $valid_info = 0; $error .= " - This login already exists. Please, try enter another login.<br>"; $xtpl->assign("CLASS_LOGIN", "class=\"error_text\""); }
        if($valid_info)
        {
            $query = "INSERT
                        INTO " . T_USERS . " (login, pass, permissions, created, position, name)
                      VALUES ('" . $login . "', PASSWORD('" . $password . "'), '" . $_REQUEST["permission"] . "', now(), '" . AddSlashes($_REQUEST["position"]) . "', '" . AddSlashes($_REQUEST["fullname"]) . "')";
            SQL_request($query);
            $user_id = mysql_insert_id();
            $query = "INSERT INTO " . T_SIGNATURES . " (user_id, signature) VALUES (" . $user_id . ", '" . AddSlashes($_REQUEST["signature"]) . "')";
            SQL_request($query);
            foreach($queues as $val)
            {
                $query = "INSERT INTO " . T_USERS_GROUPS . " (user_id, group_id) VALUES (" . $user_id . ", " . $val . ")";
                SQL_request($query);
            }
        }else
        {
            $xtpl->assign("ERROR", $error);
            $xtpl->assign("LOGIN", $login);
            $xtpl->parse("main.users.error");
            $act = "add";
        }
    }elseif($id > 0)
    {
        $query = "SELECT login FROM " . T_USERS . " WHERE id=" . $id;
        $row = SQL_select($query, 0);
        if($row)
        {
            if($login != $row["login"])
                if(check_user_login($login)) { $valid_info = 0; $error .= " - This login already exists. Please, try enter another login.<br>"; $xtpl->assign("CLASS_LOGIN", "class=\"error_text\""); }
        }else $act = "edit";
        if($valid_info)
        {
            if($password != "") $query_password = "pass=PASSWORD('" . $password . "'), ";
            else $query_password = "";
            $query = "UPDATE " . T_USERS . "
                         SET login='" . $login . "', " . $query_password . "
                             permissions='" . $_REQUEST["permission"] . "',
                             position='" . AddSlashes($_REQUEST["position"]) . "',
                             name='" . AddSlashes($_REQUEST["fullname"]) . "'
                       WHERE id=" . $id;
            SQL_request($query);
            $query = "SELECT user_id FROM " . T_SIGNATURES . " WHERE user_id=" . $id;
            $row1 = SQL_select($query, 0);
            if($row1)
            {
                $query = "UPDATE " . T_SIGNATURES . " SET signature='" . AddSlashes($_REQUEST["signature"]) . "' WHERE user_id=" . $id;
                SQL_request($query);
            }else
            {
                $query = "INSERT INTO " . T_SIGNATURES . " (user_id, signature) VALUES (" . $id . ", '" . AddSlashes($_REQUEST["signature"]) . "')";
                SQL_request($query);
            }
            if($password != "" && $_SESSION["uid"] == $id)
            {
                $_SESSION['user_login'] = $login;
                $_SESSION['user_password'] = $password;
                $_SESSION['permission'] = $_REQUEST["permission"];
            }
            $query = "DELETE FROM " . T_USERS_GROUPS . " WHERE user_id=" . $id;
            SQL_request($query);
            foreach($queues as $val)
            {
                $query = "INSERT INTO " . T_USERS_GROUPS . " (user_id, group_id) VALUES (" . $id . ", " . $val . ")";
                SQL_request($query);
            }
        }else
        {
            $xtpl->assign("ERROR", $error);
            $xtpl->parse("main.users.error");
            $act = "edit";
        }
    }
}elseif(($act == "del") && ($id > 0))
{
    $query = "SELECT id, permissions FROM " . T_USERS . " WHERE id=" . $id;
    $row = SQL_select($query, 0);
    if($row && $row["permissions"] != "system")
    {
        $query = "DELETE FROM " . T_USERS_GROUPS . " WHERE user_id=" . $id;
        SQL_request($query);
        $query = "DELETE FROM " . T_SIGNATURES . " WHERE user_id=" . $id;
        SQL_request($query);
        $query = "DELETE FROM " . T_USERS . " WHERE id=" . $id;
        SQL_request($query);
        $query = "DELETE FROM " . T_LOGIN_HISTORY . " WHERE user_id=" . $id;
        SQL_request($query);
        $query = "SELECT id FROM " . T_USERS . " WHERE permissions='system' LIMIT 1";
        $row1 = SQL_select($query, 0);
        if($row1)
        {
            $query = "UPDATE " . T_TICKETS . " SET user_id=" . $row1["id"] . " WHERE user_id=" . $id;
            SQL_request($query);
            $query = "UPDATE " . T_TICKETS_ACTIONS . " SET now_user_id=" . $row1["id"] . " WHERE now_user_id=" . $id;
            SQL_request($query);
            $query = "UPDATE " . T_TICKETS_ACTIONS . " SET prev_user_id=" . $row1["id"] . " WHERE prev_user_id=" . $id;
            SQL_request($query);
        }
    }
}elseif(($act == "access") && ($id > 0))
{
    $query = "SELECT access FROM " . T_USERS . " WHERE id=" . $id;
    $row = SQL_select($query, 0);
    if($row)
    {
        if($row["access"]) $access = 0;
        else $access = 1;
        $query = "UPDATE " . T_USERS . " SET access=" . $access . " WHERE id=" . $id;
        SQL_request($query);
    }
}

$permission = array("member"=>"member", "admin"=>"admin");
$query = "SELECT id, name FROM " . T_GROUPS . " ORDER BY name";
$rows = SQL_select($query);
$queues = array();
if($rows) foreach($rows as $val) $queues[$val["id"]] = htmlspecialchars($val["name"]);

if($act == "add")
{
    $xtpl->assign("QUEUES", get_drop_down_list($queues, "queues", "", "", 0, "", "", 1));
    $xtpl->assign("PERMISSION", get_drop_down_list($permission, "permission", ""));
    $xtpl->parse("main.users.action");
}elseif(($act == "edit")&&($id > 0))
{
    $query = "SELECT login,
                     permissions,
                     position,
                     name
                FROM " . T_USERS . "
               WHERE id=" . $id;
    $row = SQL_select($query, 0);
    if($row)
    {
        $xtpl->assign("LOGIN", $row["login"]);
        $xtpl->assign("ID", "<input type=\"hidden\" name=\"id\" value=\"" . $id . "\">");
        $query = "SELECT group_id FROM " . T_USERS_GROUPS . " WHERE user_id=" . $id;
        $rows = SQL_select($query);
        $selected_queues = array();
        if($rows) foreach($rows as $val) $selected_queues[] = $val["group_id"];
        $xtpl->assign("QUEUES", get_drop_down_list($queues, "queues", "", "", 0, $selected_queues, "", 1));
        $xtpl->assign("PERMISSION", get_drop_down_list($permission, "permission", "", "", 0, $row["permissions"]));
        $xtpl->assign("POSITION", StripSlashes(htmlspecialchars($row["position"])));
        $xtpl->assign("FULLNAME", StripSlashes(htmlspecialchars($row["name"])));
        	
        
        $query = "SELECT signature FROM " . T_SIGNATURES . " WHERE user_id=" . $id;
        $row1 = SQL_select($query, 0);
        if($row1) $xtpl->assign("SIGNATURE", StripSlashes(htmlspecialchars($row1["signature"])));
        $xtpl->parse("main.users.action");
    }else $act = "view";
}else $act = "view";

$xtpl->assign("SECTION_NAME", "Users");
$cfg_sort_fields = array("#"=>array("field"=>"","sort"=>"","def_sort_type"=>"","sort_order"=>"","url_name"=>""),
                         "Active"=>array("field"=>"","sort"=>"","def_sort_type"=>"","sort_order"=>"","url_name"=>""),
                         "Login"=>array("field"=>"","sort"=>"","def_sort_type"=>"","sort_order"=>"","url_name"=>""),
                         "Name"=>array("field"=>"","sort"=>"","def_sort_type"=>"","sort_order"=>"","url_name"=>""),
                         "Position"=>array("field"=>"","sort"=>"","def_sort_type"=>"","sort_order"=>"","url_name"=>""),
                         "Permission"=>array("field"=>"","sort"=>"","def_sort_type"=>"","sort_order"=>"","url_name"=>""),
                         "Queues"=>array("field"=>"","sort"=>"","def_sort_type"=>"","sort_order"=>"","url_name"=>""),
                         "Access"=>array("field"=>"","sort"=>"","def_sort_type"=>"","sort_order"=>"","url_name"=>""),
                         "Action"=>array("field"=>"","sort"=>"","def_sort_type"=>"","sort_order"=>"","url_name"=>""),
                         "Last login"=>array("field"=>"","sort"=>"","def_sort_type"=>"","sort_order"=>"","url_name"=>""),
                         "Created"=>array("field"=>"","sort"=>"","def_sort_type"=>"","sort_order"=>"","url_name"=>""));
if($act == "view")
{
    $xtpl->assign("ADD_USER", "<a href=\"users.php?act=add\">Add user</a>");
    $query = "SELECT id,
                     login,
                     permissions,
                     access,
                     last_login,
                     created,
                     name,
                     position
                FROM " . T_USERS . "
               WHERE permissions IN('member','admin')";
    $href = MAIN_HOST . "users.php";
    $pg_sel = new Page_Selector($query, $href, $pages, $sort, $cfg_sort_fields);
    $xtpl->assign("SELECTOR_LINKS", $pg_sel->display_selector());
    $xtpl->assign("SELECTOR_NUMROWS", $pg_sel->display_selector_num_rows(array(), $href));
    $xtpl->assign("TABLE_HEADER", $pg_sel->get_table_header());
    if(count($pg_sel->data) > 0)
    {
        foreach($pg_sel->data as $i=>$row)
        {
            $xtpl->assign("NUM", $i);
            $xtpl->assign("LOGIN", htmlspecialchars($row["login"]));
            $xtpl->assign("POSITION", StripSlashes(htmlspecialchars($row["position"])));
            $xtpl->assign("FULLNAME", StripSlashes(htmlspecialchars($row["name"])));
            $xtpl->assign("PERMISSION", $row["permissions"]);
            $query = "SELECT g.name
                        FROM " . T_GROUPS . " as g,
                             " . T_USERS_GROUPS . " as ug
                       WHERE ug.user_id=" . $row["id"] . "
                         AND ug.group_id=g.id";
            $rows = SQL_select($query);
            $queues = "";
            foreach($rows as $val) $queues .= htmlspecialchars(trim($val["name"])) . "<br>";
            $xtpl->assign("QUEUES", $queues);
            if($row["access"]) $access = "<a href=\"users.php?act=access&id=" . $row["id"] . "\" class=\"actions\">enable</a>";
            else $access = "<a href=\"users.php?act=access&id=" . $row["id"] . "\" class=\"actions\">disable</a>";
            $xtpl->assign("ACCESS", $access);
            $xtpl->assign("ACTION", "<a href=\"users.php?act=edit&id=" . $row["id"] . "\" class=\"actions\">Edit</a>&nbsp;
                                     <a href=\"#\" onclick=\"return false\" onmouseup=\"if(window.confirm('Are you sure?')) window.location.href='users.php?act=del&id=" . $row["id"] . "'\" class=\"actions\">Delete</a>");
            $xtpl->assign("LAST_LOGIN", $row["last_login"]);
            $xtpl->assign("CREATED", $row["created"]);
            $query = "SELECT uid
                        FROM " . T_SESSION . "
                       WHERE uid=" . $row['id'];
            $now_status = SQL_select($query, 0);
            if($now_status["uid"] > 0) $now_status = "<img src=\"" . MAIN_HOST . "images/on-line1.gif\" alt=\"On-line\">";
            else $now_status = "<img src=\"" . MAIN_HOST . "images/off-line1.gif\" alt=\"Off-line\">";
            $xtpl->assign("ACTIVE", $now_status);
            $login_history = "";
            $query = "SELECT last_login FROM " . T_LOGIN_HISTORY . " WHERE user_id=" . $row["id"] . " ORDER BY last_login DESC LIMIT 30";
            $rows = SQL_select($query);
            if($rows) foreach($rows as $val) $login_history .= $val["last_login"] . "<br>";
            $xtpl->assign("LOGIN_HISTORY", $login_history);
            $xtpl->parse("main.users.view.list");
        }
    }
    $xtpl->parse("main.users.view");
}
get_queues_status($xtpl);
$xtpl->parse("main.users");
$xtpl->parse("main");
$xtpl->out("main");
?>