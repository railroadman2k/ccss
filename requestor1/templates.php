<?php
/**
 * templates.php - manage answer template essense: allow add/edit/delete template and view all templates list
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

    if(array_key_exists("selected_ticket_id", $_SESSION)) unset($_SESSION["selected_ticket_id"]);
    if(array_key_exists("searching_parameters", $_SESSION)) unset($_SESSION["searching_parameters"]);
    $xtpl->assign("SECTION_NAME", "Templates");

    if(array_key_exists("id", $_REQUEST)) $id = (int)$_REQUEST["id"];
    else $id = 0;

    //save updated template data
    if($act == "save")
    {
        $error = "";
        if(strlen(trim($_REQUEST["name"])) < 3) { $valid_info = 0; $error .= "You must enter the template name.<br>"; }
        if(strlen(trim($_REQUEST["message"])) < 20) { $valid_info = 0; $error .= "You must enter the template body.<br>"; }
        if($id == 0)
        {
            if($valid_info)
            {
                $query = "INSERT INTO " . T_TEMPLATES . " (name, message, template_website)
                               VALUES ('" . AddSlashes(trim($_REQUEST["name"])) . "',
                                       '" . AddSlashes(trim($_REQUEST["message"])) . "',
                                       '" . $_REQUEST["template_website"] . "')";
                SQL_request($query);
            }else
            {
                $xtpl->assign("TEMPLATE_NAME", trim($_REQUEST["name"]));
                $xtpl->assign("MESSAGE", trim($_REQUEST["message"]));
                $xtpl->assign("ERROR", $error);
                $xtpl->parse("main.templates.error");
                $act = "add";
            }
        }elseif($id > 0)
        {
            if($valid_info)
            {
                $query = "UPDATE " . T_TEMPLATES . "
                             SET name='" . AddSlashes(trim($_REQUEST["name"])) . "',
                                 message='" . AddSlashes(trim($_REQUEST["message"])) . "',
                                 template_website='" . $_REQUEST["template_website"] . "'
                           WHERE id=" . $id;
                SQL_request($query);
                $act = "edit";
            }else
            {
                $xtpl->assign("TEMPLATE_NAME", trim($_REQUEST["name"]));
                $xtpl->assign("MESSAGE", trim($_REQUEST["message"]));
                $xtpl->assign("ERROR", $error);
                $xtpl->parse("main.templates.error");
                $act = "edit";
            }
        }
    }
    //delete template
    elseif($act == "del")
    {
        if(array_key_exists("select", $_REQUEST))
        {
            $query = "DELETE FROM " . T_TEMPLATES . " WHERE id IN(" . implode(",", $_REQUEST["select"]) . ")";
            SQL_request($query);
        }
    }

    $query = "SELECT name FROM " . T_TEMPLATE_VARS;
    $rows = SQL_select($query);
    if($rows)
    {
        foreach($rows as $val) $tvars .= "<a href=\"\" onclick=\"return false;\" onmouseup=\"ie_insert_into_textarea('message', '{" . strtoupper($val["name"]) . "}')\" class=\"actions\">" . $val["name"] . "</a><br>";
        $xtpl->assign("TVARS_LIST", $tvars);
    }

    //add new template
    if($act == "add")
    {
        $xtpl->assign("TEMPLATE_WEBSITE", get_drop_down_list(array(), "template_website", T_TEMPLATES, "", 0, $_SESSION["website_url"]));
        $xtpl->parse("main.templates.action");
    }elseif(($act == "edit")&&($id > 0))
    {
        $query = "SELECT id, name, message, template_website FROM " . T_TEMPLATES . " WHERE id=" . $id;
        $row = SQL_select($query, 0);
        if($row)
        {
            $xtpl->assign("TEMPLATE_NAME", StripSlashes(htmlspecialchars($row["name"])));
            $xtpl->assign("ID", "<input type=\"hidden\" name=\"id\" value=\"" . $id . "\">");
            $xtpl->assign("MESSAGE", StripSlashes(htmlspecialchars($row["message"])));
            $xtpl->assign("TEMPLATE_WEBSITE", get_drop_down_list(array(), "template_website", T_TEMPLATES, "", 0, $row["template_website"]));
            $xtpl->parse("main.templates.action");
        }else $act = "view";
    }else $act = "view";

    //view templates list
    if($act == "view")
    {
        $xtpl->assign("ADD_TEMPLATE", "<a href=\"templates.php?act=add\">Add template</a>");
        $xtpl->assign("DELETE_TEMPLATE", "<a href=\"\" onclick=\"return false;\" onmouseup=\"if(window.confirm('Are you sure?')) document.getElementById('form1').submit()\">Delete selected</a>");
        $cfg_sort_fields = array("#"=>array("field"=>"","sort"=>"","def_sort_type"=>"","sort_order"=>"","url_name"=>""),
                                 "<a href=\"#\" onclick=\"return false\" onmouseup=\"select_all_checkboxs('select[]','form1')\" class=\"table_header_text\">Select</a>"=>array("field"=>"","sort"=>"","def_sort_type"=>"","sort_order"=>"","url_name"=>""),
                                 "Template name"=>array("field"=>"name","sort"=>"sort","def_sort_type"=>"","sort_order"=>"","url_name"=>"name"),
                                 "Web site"=>array("field"=>"template_website","sort"=>"sort","def_sort_type"=>"","sort_order"=>"","url_name"=>"t_website"),
                                 "Action"=>array("field"=>"","sort"=>"","def_sort_type"=>"","sort_order"=>"","url_name"=>""));
        $query = "SELECT id, name, template_website
                    FROM " . T_TEMPLATES;
        $href = MAIN_HOST . "templates.php";
        $pg_sel = new Page_Selector($query, $href, $pages, $sort, $cfg_sort_fields);
        $xtpl->assign("SELECTOR_LINKS", $pg_sel->display_selector());
        $xtpl->assign("SELECTOR_NUMROWS", $pg_sel->display_selector_num_rows(array(), $href));
        $xtpl->assign("TABLE_HEADER", $pg_sel->get_table_header());
        if(count($pg_sel->data) > 0)
        {
            foreach($pg_sel->data as $i=>$row)
            {
                $xtpl->assign("NUM", $i);
                $xtpl->assign("_ID", $i);
                $xtpl->assign("SELECT", "<input type=\"checkbox\" id=\"" . $i . "\" name=\"select[]\" value=\"" . $row["id"] . "\" onclick=\"change_tr_class(this)\">");
                $xtpl->assign("TEMPLATE_NAME", StripSlashes(htmlspecialchars($row["name"])));
                $xtpl->assign("TEMPLATE_WEBSITE", $row["template_website"]);
                $xtpl->assign("ACTION", "<a href=\"templates.php?act=edit&id=" . $row["id"] . "\" class=\"actions\">Edit</a>");
                $xtpl->parse("main.templates.view.list");
            }
        }
        $xtpl->parse("main.templates.view");
    }

    $xtpl->parse("main.templates");
    get_queues_status($xtpl);
    $xtpl->parse("main");
    $xtpl->out("main");
?>