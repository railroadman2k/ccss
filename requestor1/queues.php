<?php
/**
 * queues.php - manage queue essense: allow add/edit/delete queues and view all queues list
 *
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

//saving
if($act == "save")
{
    $name = preg_replace('/[^a-zA-Z0-9\ \-\_]/','',StripSlashes(trim($_REQUEST['name'])));
    if(strlen($name) < 4) { $valid_info = 0; $error .= " - You must enter the name of queue.<br>"; $xtpl->assign("CLASS_NAME", "class=\"error_text\""); }
    if(!preg_match("/[_a-zA-z0-9\-]+(\.[_a-zA-z0-9\-]+)*\@[_a-zA-z0-9\-]+(\.[a-zA-z]{2,3})+/", $_REQUEST['email'])) { $valid_info = 0; $error .= " - You must enter the correct email.<br>"; $xtpl->assign("CLASS_EMAIL", "class=\"error_text\""); }
    if($id == 0)
    {
        if($valid_info)
        {
            if(check_queue_email($_REQUEST["email"])) { $valid_info = 0; $error .= " - You must enter another email because this email already exists.<br>"; $xtpl->assign("CLASS_EMAIL", "class=\"error_text\""); }
            if(check_queue_name($name)) { $valid_info = 0; $error .= " - You must enter another name because this name already exists.<br>"; $xtpl->assign("CLASS_NAME", "class=\"error_text\""); }
        }
        if($valid_info)
        {
            $query = "INSERT INTO " . T_GROUPS . " (name, email) VALUES ('" . $name . "', '" . $_REQUEST["email"] . "')";
            SQL_request($query);
        }else
        {
            $xtpl->assign("ERROR", $error);
            $xtpl->assign("NAME", $name);
            $xtpl->assign("EMAIL", htmlspecialchars(trim($_REQUEST["email"])));
            $xtpl->parse("main.queues.error");
            $act = "add";
        }
    }elseif($id > 0)
    {
        $query = "SELECT name, email FROM " . T_GROUPS . " WHERE id=" . $id;
        $row = SQL_select($query, 0);
        if($row && $valid_info)
        {
            if($row["email"] != $_REQUEST["email"])
                if(check_queue_email($_REQUEST["email"])) { $valid_info = 0; $error .= " - You must enter another email because this email already exists.<br>"; $xtpl->assign("CLASS_EMAIL", "class=\"error_text\""); }
            if($row["name"] != $name)
                if(check_queue_name($name)) { $valid_info = 0; $error .= " - You must enter another name because this name already exists.<br>"; $xtpl->assign("CLASS_NAME", "class=\"error_text\""); }
        }
        if($valid_info)
        {
            $query = "UPDATE " . T_GROUPS . " SET name='" . $name . "', email='" . $_REQUEST["email"] . "' WHERE id=" . $id;
            SQL_request($query);
        }else
        {
            $xtpl->assign("ERROR", $error);
            $xtpl->parse("main.queues.error");
            $act = "edit";
        }
    }
}
elseif(($act == "del") && ($id > 0))
{
    $query = "DELETE FROM " . T_USERS_GROUPS . " WHERE group_id=" . $id;
    SQL_request($query);
    $query = "SELECT id FROM " . T_TICKETS . " WHERE group_id=" . $id;
    $rows = SQL_select($query, 0);
    if($rows)
    {
        foreach($rows as $val)
        {
            $query = "DELETE FROM " . T_TICKETS_ACTIONS . " WHERE ticket_id=" . $val["id"];
            SQL_request($query);
            ini_set("max_execution_time", 29);
        }
    }
    $query = "DELETE FROM " . T_TICKETS . " WHERE group_id=" . $id;
    SQL_request($query);
    $query = "DELETE FROM " . T_GROUPS . " WHERE id=" . $id;
    SQL_request($query);
}

if($act == "add")
{
    $xtpl->parse("main.queues.action");
}
elseif(($act == "edit")&&($id > 0))
{
    $query = "SELECT id, name, email FROM " . T_GROUPS . " WHERE id=" . $id;
    $row = SQL_select($query, 0);
    if($row)
    {
        $xtpl->assign("ID", "<input type=\"hidden\" name=\"id\" value=\"" . $id . "\">");
        $xtpl->assign("NAME", htmlspecialchars(trim($row["name"])));
        $xtpl->assign("EMAIL", htmlspecialchars(trim($row["email"])));
        $xtpl->parse("main.queues.action");
    }else $act = "view";
}
else $act = "view";

    $xtpl->assign("SECTION_NAME", "Queues");

if($act == "view")
{
    $xtpl->assign("ADD_QUEUE", "<a href=\"queues.php?act=add\">Add queue</a>");
    $cfg_sort_fields = array("#"=>array("field"=>"","sort"=>"","def_sort_type"=>"","sort_order"=>"","url_name"=>""),
                             "Name"=>array("field"=>"","sort"=>"","def_sort_type"=>"","sort_order"=>"","url_name"=>""),
                             "Email"=>array("field"=>"","sort"=>"","def_sort_type"=>"","sort_order"=>"","url_name"=>""),
                             "Action"=>array("field"=>"","sort"=>"","def_sort_type"=>"","sort_order"=>"","url_name"=>""));

    $query = "SELECT id,
                     name,
                     email
                FROM " . T_GROUPS;
    $href = MAIN_HOST . "queues.php";
    $pg_sel = new Page_Selector($query, $href, $pages, $sort, $cfg_sort_fields);
    $xtpl->assign("SELECTOR_LINKS", $pg_sel->display_selector());
    $xtpl->assign("SELECTOR_NUMROWS", $pg_sel->display_selector_num_rows(array(), $href));
    $xtpl->assign("TABLE_HEADER", $pg_sel->get_table_header());
    if(count($pg_sel->data) > 0)
    {
        foreach($pg_sel->data as $i=>$row)
        {
            $xtpl->assign("NUM", $i);
            $xtpl->assign("NAME", htmlspecialchars(trim($row["name"])));
            $xtpl->assign("EMAIL", htmlspecialchars(trim($row["email"])));
            $xtpl->assign("ACTION", "<a href=\"queues.php?act=edit&id=" . $row["id"] . "\" class=\"actions\">Edit</a>&nbsp;
                                     <a href=\"#\" onclick=\"return false\" onmouseup=\"if(window.confirm('Are you sure?')) window.location.href='queues.php?act=del&id=" . $row["id"] . "'\" class=\"actions\">Delete</a>");
            $xtpl->parse("main.queues.view.list");
        }
    }
    $xtpl->parse("main.queues.view");
}
    get_queues_status($xtpl);
    $xtpl->parse("main.queues");
    $xtpl->parse("main");
    $xtpl->out("main");
?>