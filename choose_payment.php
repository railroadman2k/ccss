<?
include_once'logincheck.php';
include_once("session.php");
include_once "myconnect.php";


include_once "includes/functions/general.php";
require 'includes/libs/Smarty.class.php';
$smarty = new Smarty;


$errcnt = 0;

if (!session_is_registered('new_level')){
  Header("Location: choose_membership.php");
  die();
}

if ( count($_POST)<>0 ){
  if ( !isset( $_REQUEST["payment"] ) || $_REQUEST["payment"]=="" ){
  	$errs[$errcnt]="Please select prefered payment.";
  	$errcnt++;
  }
  if ($errcnt == 0){

    $config=mysql_fetch_array(mysql_query("select * from b2b_config"));
    if ($new_level = 1){
      switch($_SESSION["b2b_memtype"])
      {
      case 2:
        $price = $config["sbfee_bg"];
      	break;
      case 3:
        $price = $config["sbfee_sg"];
      	break;
      case 4:
        $price = $config["sbfee_gg"];
      	break;
      }
    }
    if ($new_level = 2){
      switch($_SESSION["b2b_memtype"])
      {
      case 3:
        $price = $config["sbfee_bs"];
      	break;
      case 4:
        $price = $config["sbfee_gs"];
      	break;
      }
    }
    if ($new_level = 2){
      switch($_SESSION["b2b_memtype"])
      {
      case 4:
        $price = $config["sbfee_gb"];
      	break;
      }
    }

    if ($_REQUEST["payment"] == 'payflow'){
      if ($_REQUEST['payflowpro_cc_number'] == ''){
      	$errs[$errcnt]="Please input CC number.";
      	$errcnt++;
      }
      if ($_REQUEST['payflowpro_cc_csc'] == ''){
      	$errs[$errcnt]="Please input CVV number.";
      	$errcnt++;
      }
      if ($errcnt == 0){
        include('includes/functions/php_pfpro.php');
        pfpro_init();

        $member = mysql_fetch_array(mysql_query("select * from b2b_members where sb_id = " . $_SESSION['b2b_userid']));
        require_once('payment_config.php');
    		$transaction = array('USER'   => trim(MODULE_PAYMENT_PAYFLOWPRO_USER),
    		'VENDOR' => trim(MODULE_PAYMENT_PAYFLOWPRO_VENDOR),
    		'PARTNER' => trim(MODULE_PAYMENT_PAYFLOWPRO_PARTNER),
    		'PWD'        => trim(MODULE_PAYMENT_PAYFLOWPRO_PWD),
    		'TRXTYPE'    => trim(MODULE_PAYMENT_PAYFLOWPRO_TRXTYPE),
    		'TENDER'     => trim(MODULE_PAYMENT_PAYFLOWPRO_TENDER),
    		'AMT'        => number_format($price, 2, '.',''),
    		'ACCT'       => $_REQUEST['payflowpro_cc_number'],
    		'EXPDATE'    => $_REQUEST['payflowpro_Month'] . substr($_REQUEST['payflowpro_Year'],-2),
    		'FREIGHTAMT'    => 0,
    		'TAXAMT'    => 0,
    		'FIRSTNAME'    => $member['sb_firstname'],
    		'LASTNAME'    => $member['sb_lastname'],
    		'STREET'    => $member['sb_street'],
    		'CITY'    => $member['sb_city'],
    		'STATE'    => $member['sb_state'],
    		'ZIP'    => $member['sb_zip'],
    		'COUNTRY'    => $member['sb_country'],
    		'EMAIL'    => $member['sb_email'],
    		'CVV2'        => $_REQUEST['payflowpro_cc_csc']
    		);
        $response = pfpro_process($transaction);

    		if (!$response || $response['RESULT']!=0) {
         	$errs[$errcnt]='There has been an error processing you credit card, please try again. ' . $response['RESPMSG'];
        	$errcnt++;
    		}elseif (sizeof($response)>1 && (int)($response['RESULT'])==0) {

    		} else {
         	$errs[$errcnt]='There has been an error processing you credit card, please try again. ' . $response['RESPMSG'];
        	$errcnt++;
    		}
        if ($errcnt == 0){
          mysql_query("update b2b_members set sb_memtype = '" .$_SESSION['new_level']. "' sb_expiry_date = sb_expiry_date + 31536000 where sb_id = " . $_SESSION['b2b_userid']);
          session_unregister('new_level');
          Header("Location: gen_confirm_mem.php?errmsg=" . urlencode('You membership has been updated.'));
        }
      }
    }
    if ($_REQUEST["payment"] == "paypal"){
       $member = mysql_fetch_array(mysql_query("select * from b2b_members where sb_id = " . $_SESSION['b2b_userid']));
       require_once('payment_config.php');
       Header("Location: https://www.paypal.com/cgi-bin/webscr?cmd=_ext-enter&redirect_cmd=_xclick&business=".MODULE_PAYMENT_PAYPALIPN_ID."&item_name=".urlencode(STORE_NAME)."&cn=".$_SESSION['new_level']."&item_number=".$_SESSION['b2b_userid']."&currency_code=USD&amount=".$price."&shipping=0&tax=0&first_name=".urlencode($member['sb_firstname'])."&last_name=".urlencode($member['sb_lastname'])."&address1=".urlencode($member['sb_street'])."&city=".urlencode($member['sb_city'])."&state=".urlencode($member['sb_state'])."&zip=".urlencode($$member['sb_zip'])."&email=".$member['sb_email']."&bn=oscommerce-osmosis-0.981&return=".MODULE_PAYMENT_PAYPALIPN_OK_URL."&cancel_return=".MODULE_PAYMENT_PAYPALIPN_CANCEL_URL."&notify_url=".MODULE_PAYMENT_PAYPALIPN_NOTIFY_URL);

    }
    if ($_REQUEST["payment"] == "wt"){
      Header("Location: wt_payment.php");
    }
  }
}

$smarty->assign('errs', $errs);


general_template_config($smarty);

$config=mysql_fetch_array(mysql_query("select * from b2b_config"));

$smarty->assign('b2b_memtype', $_SESSION["b2b_memtype"]);

for ($i=1; $i < 13; $i++) {
	$expires_month[] = array('id' => sprintf('%02d', $i), 'text' => strftime('%m',mktime(0,0,0,$i,1,2000)));
}

$today = getdate();
for ($i=$today['year']; $i < $today['year']+10; $i++) {
	$expires_year[] = array('id' => strftime('%y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
}
$smarty->assign('expires_month', $expires_month);
$smarty->assign('expires_year', $expires_year);

$catpath = array(array('url' => 'choose_paymnet.php', 'name' => 'Choose Payment'), array('url' => 'choose_membership.php', 'name' => 'Choose Membership'), array('url' => 'userhome.php', 'name' => 'My Account'));
$smarty->assign('navigation', $catpath);

$smarty->assign('header_name', 'Choose Payment');

$smarty->assign('box_width', 231);

$smarty->assign('page_template', 'static.tpl');
$smarty->assign('content_box', 'choose_payment_box.tpl');
$smarty->display('common.tpl');

?>