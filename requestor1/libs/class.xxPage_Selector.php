<?

class Page_Selector {
	var $show_rows = array("50", "100", "500");
	var $default_num_rows = DEFAULT_NUM_ROWS;
	var $visible_pages = 4;
	var $leaf_over_pages = 2;
	var $data=array();
	var $page_selector="";
	var $num_rows_selector="";
	var $cfg_sort = array();
	var $fsort = array();
	var $tmp_href = "";
	var $tmp_pages = 0;
	var $tmp_num_of_rows = DEFAULT_NUM_ROWS;
	var $sel_class = "page_selector";
	var $num_rows_class = "whitetext1";
	var	$table_header_class = "table_header_text";
	var $sort_image_up = "<img src=\"images/sort_up.gif\" width=\"9\" height=\"6\" border=\"0\">";
	var $sort_image_down = "<img src=\"images/sort_down.gif\" width=\"9\" height=\"6\" border=\"0\">";

	function Page_Selector($query="", $href="", $pages=0, $sort=array(), $cfg_sort_fields = array(), $num_rows = 0)
	{
		$this->cfg_sort = $cfg_sort_fields;
		$this->fsort = $sort;
		$this->tmp_href = $href;
		$this->tmp_pages = $pages;
		$this->sort_image_up = "<img src=\"" . MAIN_HOST . "images/sort_up.gif\" width=\"9\" height=\"6\" border=\"0\">";
		$this->sort_image_down = "<img src=\"" . MAIN_HOST . "images/sort_down.gif\" width=\"9\" height=\"6\" border=\"0\">";
		if(array_key_exists("num_rows", $_REQUEST)) $num_of_rows = (int)$_REQUEST['num_rows'];
		elseif($num_rows != 0) $num_of_rows = $num_rows;
		else $num_of_rows = $this->default_num_rows;
		$this->tmp_num_of_rows = $num_of_rows;
		if(empty($query)) return false;
		else
		{
			$sort_to_query = $this->get_sort_fields();
			
			if($sort_to_query != "") $query = $query . " ORDER BY " . $sort_to_query;
			$res = mysql_query($query);
			$total = mysql_num_rows($res);
			if($pages > (floor($total/$num_of_rows))) $pages=0;
			if(!empty($pages)) mysql_data_seek($res, $pages*$num_of_rows);
			for($i = $pages*$num_of_rows+1; $i<($pages+1)*$num_of_rows+1; $i++)
			{
				if(!($row = mysql_fetch_array($res, MYSQL_ASSOC)))  break;
				foreach($row as $k=>$v) $this->data[$i][$k]=$v;
			}
			mysql_free_result($res);
			if($total < $num_of_rows)
			{
				$this->page_selector="";
			}else
			{
				if($pages != 0)
				{
					if(preg_match("/.*\?.*&$/",$href))
						$this->page_selector .= "<a href='".$href."pages=".($pages-1)."&num_rows=" . $num_of_rows . "' class='" . $this->sel_class . "'>&laquo;prev</a>&nbsp;|\n";
					elseif(preg_match("/.*\?.*/",$href))
						$this->page_selector .= "<a href='".$href."&pages=".($pages-1)."&num_rows=" . $num_of_rows . "' class='" . $this->sel_class . "'>&laquo;prev</a>&nbsp;|\n";
					else $this->page_selector .= "<a href='".$href."?pages=".($pages-1)."&num_rows=" . $num_of_rows . "' class='" . $this->sel_class . "'>&laquo;prev</a>&nbsp;|\n";
				}
				$pages_total = floor($total/$num_of_rows);
				$pages_even1 = 0;
				$pages_even = $total%$num_of_rows;
				if($pages_even == 0) $pages_even1 = 1;
				$pages_visible = min($pages_total, $this->visible_pages);
				$first_page_visible = max(0, min(($pages - $this->leaf_over_pages), floor($pages_total - $this->visible_pages)));
				if($first_page_visible > 0) $this->page_selector .= "|";
				for($i = $first_page_visible; $i <= $first_page_visible+$pages_visible-$pages_even1; $i++)
				{
					if($i != $pages)
					{
						if(preg_match("/.*\?.*&$/",$href))
							$this->page_selector .= "<a href='".$href."pages=".($i)."&num_rows=" . $num_of_rows . "' class='" . $this->sel_class . "'>".($i+1)."</a>\n";
						elseif(preg_match("/.*\?.*/",$href))
							$this->page_selector .= "<a href='".$href."&pages=".($i)."&num_rows=" . $num_of_rows . "' class='" . $this->sel_class . "'>".($i+1)."</a>\n";
						else $this->page_selector .= "<a href='".$href."?pages=".($i)."&num_rows=" . $num_of_rows . "' class='" . $this->sel_class . "'>".($i+1)."</a>\n";
					}else
					{
						$this->page_selector .= ($i+1);
					}
					if($i != floor($total/$num_of_rows)) $this->page_selector.="|&nbsp;";
				}
				if($pages != (floor($total/$num_of_rows)-$pages_even1))
				{
					if(preg_match("/.*\?.*&$/", $href))
						$this->page_selector .= "|&nbsp;<a href='".$href."pages=".($pages+1)."&num_rows=" . $num_of_rows . "' class='" . $this->sel_class . "'>next&raquo;</a>&nbsp;\n";
					elseif(preg_match("/.*\?.*/",$href))
						$this->page_selector .= "|&nbsp;<a href='".$href."&pages=".($pages+1)."&num_rows=" . $num_of_rows . "' class='" . $this->sel_class . "'>next&raquo;</a>&nbsp;\n";
					else $this->page_selector .= "|&nbsp;<a href='".$href."?pages=".($pages+1)."&num_rows=" . $num_of_rows . "' class='" . $this->sel_class . "'>next&raquo;</a>&nbsp;\n";
				}
				$this->page_selector .= "</div>\n";
				$this->page_selector = "<div class='" . $this->sel_class . "'>Rows&nbsp;from&nbsp;<b>" . ($pages*$num_of_rows+1) . "</b> to <b>" . (($pages!=(floor($total/$num_of_rows)-$pages_even1)) ?($pages+1)*$num_of_rows:$total) . "</b>&nbsp;(all&nbsp;&nbsp;<b>" . $total . "</b>&nbsp;):&nbsp;" . $this->page_selector;
			}
		}
	}
    
    function display_selector()
	{
      return $this->page_selector;
    }
	
	function display_selector_num_rows($conf = array(), $action_url = "", $additional = "")
	{
		$show = "";
		for($i=0; $i<count($this->show_rows); $i++)
		{
			$show .= "<option value=\"" . $this->show_rows[$i] . "\"";
			if(array_key_exists("num_rows", $_REQUEST))
			{
				if((int)$_REQUEST['num_rows'] == $this->show_rows[$i]) $show .= " selected";
			}
			$show .= ">" . $this->show_rows[$i] . "</option>\n";
		}
		$this->num_rows_selector .= "\n<table width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n<tr>\n";
		$this->num_rows_selector .= "<form name=\"form_num_rows\" action=\"" . $action_url . "\" method=\"post\">\n";
		if($additional != "") $this->num_rows_selector .= $additional;
		if(count($conf) > 0)
			foreach($conf as $key=>$value)
			{
				$this->num_rows_selector .= "<input type=\"hidden\" name=\"" . $key . "\" value=\"" . $value . "\">\n";
			}
		if(count($this->fsort) > 0)
			foreach($this->fsort as $key=>$value)
				$this->num_rows_selector .= "<input type=\"hidden\" name=\"sort[" . $key . "]\" value=\"1\">\n";
		else
			foreach($this->cfg_sort as $val)
			{
				if($val["sort"] == "default")
				{
					if($val["def_sort_type"] == "desc")
						$this->num_rows_selector .= "<input type=\"hidden\" name=\"sort[" . $val["url_name"] . "_down]\" value=\"1\">\n";
					else
						$this->num_rows_selector .= "<input type=\"hidden\" name=\"sort[" . $val["url_name"] . "_up]\" value=\"1\">\n";
				}
			}
		$this->num_rows_selector .= "<td width=\"4%\" class=\"" . $this->num_rows_class . "\">Show&nbsp;</td><td width=\"4%\">\n<select name=\"num_rows\" onchange=\"document.form_num_rows.submit();\" class=\"input1\">\n";
		$this->num_rows_selector .= $show;
		$this->num_rows_selector .= "</select>\n</td>\n<td width=\"92%\" class=\"" . $this->num_rows_class . "\">&nbsp;records on the page </td>\n</tr>\n</form>\n</table>\n";
		return $this->num_rows_selector;
	}
	function get_sort_fields()
	{
		$selected_fields = array();
		if(count($this->fsort) > 0)
		{
			foreach($this->fsort as $value=>$v)
			{
				if(preg_match("/[a-z\.]+\_(up|down|nosort)/", $value)) $sort_el = split("\_", $value);
				else $sort_el = array("","");
				foreach($this->cfg_sort as $val)
				{
					if(($val["sort"] != "")&&($sort_el[0] == $val["url_name"]))
					{
						if($sort_el[1] == "down") $selected_fields[] = $val["field"] . " DESC";
						elseif($sort_el[1] == "up") $selected_fields[] = $val["field"] . " ASC";
						break;
					}
				}
			}
		}else
		{
			foreach($this->cfg_sort as $val)
			{
				if($val["sort"] == "default")
				{
					if($val["def_sort_type"] == "desc") $selected_fields[$val["sort_order"]] = $val["field"] . " DESC";
					else $selected_fields[$val["sort_order"]] = $val["field"] . " ASC";
				}
			}
		}
		$order_fields = "";
		ksort($selected_fields);
		if(count($selected_fields) > 0) $order_fields = " " . implode(", ", $selected_fields);
		return $order_fields;
	}
	function get_table_header()
	{
		$sort_fields = array();
		if(count($this->fsort) == 0)
		{
			foreach($this->cfg_sort as $val)
			{
				if($val["sort"] == "default")
				{
					if($val["def_sort_type"] == "desc")
					{
						$this->fsort[$val["url_name"] . "_down"] = 1;
						$sort_fields[$val["url_name"] . "_down"] = "sort[" . $val["url_name"] . "_down]=1";
					}else
					{
						$this->fsort[$val["url_name"] . "_up"] = 1;
						$sort_fields[$val["url_name"] . "_up"] = "sort[" . $val["url_name"] . "_up]=1";
					}
				}
			}
		}else
			foreach($this->fsort as $key=>$val) $sort_fields[$key] = "sort[" . $key . "]=1";
		if(preg_match("/.*\?.*&$/",$this->tmp_href))
			$this->tmp_href .= "";
		elseif(preg_match("/.*\?.*/",$this->tmp_href))
			$this->tmp_href .= "&";
		else $this->tmp_href .= "?";
		$table_header = "";
		foreach($this->cfg_sort as $td_text=>$val)
		{
			$tmp_sort_fields = $sort_fields;
			$sort_up = $val["url_name"] . "_up";
			$sort_down = $val["url_name"] . "_down";
			$nosort = $val["url_name"] . "_nosort";
			if($val["sort"] == "")
			{
				$table_header .= "<td class=\"" . $this->table_header_class . "\" align=\"center\">";
				$table_header .= $td_text . "";
				$table_header .= "</td>";
			}else
			{
				$td_context = "";
				if(array_key_exists($sort_up, $this->fsort))
				{
					$sort_fields_url = "";
					if(count($tmp_sort_fields) > 0)
					{
						if(array_key_exists($sort_up, $tmp_sort_fields)) unset($tmp_sort_fields[$sort_up]);
						$sort_fields_url = "&" . implode("&", $tmp_sort_fields);
					}
					$td_context .= "<a href=\"" . $this->tmp_href . "pages=". $this->tmp_pages ."&num_rows=" . $this->tmp_num_of_rows . "&sort[" . $sort_down . "]=1" . $sort_fields_url . "\" class=\"" . $this->table_header_class . "\">" . $td_text . "</a>";
					$td_context .= "&nbsp;&nbsp;";
					$td_context .= "<a href=\"" . $this->tmp_href . "pages=". $this->tmp_pages ."&num_rows=" . $this->tmp_num_of_rows . "&sort[" . $sort_down . "]=1" . $sort_fields_url . "\" class=\"" . $this->table_header_class . "\">" . $this->sort_image_down . "</a>";
				}elseif(array_key_exists($sort_down, $this->fsort)) {
					$sort_fields_url = "";
					if(count($tmp_sort_fields) > 0)
					{
						if(array_key_exists($sort_down, $tmp_sort_fields)) unset($tmp_sort_fields[$sort_down]);
						$sort_fields_url = "&" . implode("&", $tmp_sort_fields);
					}
					$td_context .= "<a href=\"" . $this->tmp_href . "pages=". $this->tmp_pages ."&num_rows=" . $this->tmp_num_of_rows . "&sort[" . $nosort . "]=1" . $sort_fields_url . "\" class=\"" . $this->table_header_class . "\">" . $td_text . "</a>";
					$td_context .= "&nbsp;&nbsp;";
					$td_context .= "<a href=\"" . $this->tmp_href . "pages=". $this->tmp_pages ."&num_rows=" . $this->tmp_num_of_rows . "&sort[" . $nosort . "]=1" . $sort_fields_url . "\" class=\"" . $this->table_header_class . "\">" . $this->sort_image_up . "</a>";
				}elseif(array_key_exists($nosort, $this->fsort)) {
					$sort_fields_url = "";
					if(count($tmp_sort_fields) > 0)
					{
						if(array_key_exists($nosort, $tmp_sort_fields)) unset($tmp_sort_fields[$nosort]);
						$sort_fields_url = "&" . implode("&", $tmp_sort_fields);
					}
					$td_context .= "<a href=\"" . $this->tmp_href . "pages=". $this->tmp_pages ."&num_rows=" . $this->tmp_num_of_rows . "&sort[" . $sort_up . "]=1" . $sort_fields_url . "\" class=\"" . $this->table_header_class . "\">" . $td_text . "</a>";
				}else {
					$sort_fields_url = "";
					if(array_key_exists($nosort, $tmp_sort_fields)) unset($tmp_sort_fields[$nosort]);
					elseif(array_key_exists($sort_up, $tmp_sort_fields)) unset($tmp_sort_fields[$sort_up]);
					elseif(array_key_exists($sort_down, $tmp_sort_fields)) unset($tmp_sort_fields[$sort_down]);
					if(count($tmp_sort_fields) > 0) $sort_fields_url = "&" . implode("&", $tmp_sort_fields);
					$td_context .= "<a href=\"" . $this->tmp_href . "pages=". $this->tmp_pages ."&num_rows=" . $this->tmp_num_of_rows . "&sort[" . $sort_up . "]=1" . $sort_fields_url . "\" class=\"" . $this->table_header_class . "\">" . $td_text . "</a>";
				}
				$table_header .= "<td align=\"center\">";
				$table_header .= $td_context . "";
				$table_header .= "</td>";
			}
		}
		return $table_header;
	}
}
?>