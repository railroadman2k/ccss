<?

include "validator.php";
 $query = "SELECT * FROM " . T_USERS . " WHERE id=" . $_SESSION["uid"];
 $row = SQL_select($query, 0);
        
 if($act=="save")
 {
 	$query = "UPDATE " . T_USERS . "
                         SET position='" . AddSlashes($_REQUEST["position"]) . "',
                             name='" . AddSlashes($_REQUEST["fullname"]) . "'
                       		 WHERE id=" . $_SESSION['uid'];
            SQL_request($query);
  	        $query = "SELECT user_id FROM " . T_SIGNATURES . " 
  	        				WHERE user_id=" . $_SESSION['uid'];
            $row1 = SQL_select($query, 0);
            if($row1)
            {
              $query = "UPDATE " . T_SIGNATURES . " SET signature='" 
              . AddSlashes($_REQUEST["signature"]) . "' 
              	WHERE user_id=" . $_SESSION['uid'];
                SQL_request($query);
            }
          	else
            {
            $query = "INSERT INTO " . T_SIGNATURES . " (user_id, signature) 
            VALUES (" . $_SESSION['uid'] . ", 
            '" . AddSlashes($_REQUEST["signature"]) . "')";
            SQL_request($query);
            }
  header("Location: account.php");           
 }
 
$xtpl->assign("LOGIN", $row["login"]);
$xtpl->assign("ID", "<input type=\"hidden\" name=\"id\" value=\"" . $id . "\">");
$xtpl->assign("POSITION", StripSlashes(htmlspecialchars($row["position"])));
$xtpl->assign("FULLNAME", StripSlashes(htmlspecialchars($row["name"])));
$query = "SELECT signature FROM " . T_SIGNATURES . " WHERE user_id=" . $_SESSION['uid'];
$row1 = SQL_select($query, 0);
        if($row1) $xtpl->assign("SIGNATURE", StripSlashes(htmlspecialchars($row1["signature"])));

get_queues_status($xtpl);
$xtpl->parse("main.users.profile"); 
$xtpl->parse("main.users");
$xtpl->parse("main");
$xtpl->out("main");
?>