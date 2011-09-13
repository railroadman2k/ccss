<?php
/**
 * stat.php  - first statistic page, show daily statistic for current month by default
 *
 * include validator.php
 */

include "validator.php";

get_manager_status(&$xtpl);

//from dizel
if(array_key_exists("searching_parameters", $_SESSION)) unset($_SESSION["searching_parameters"]);
$cond1 = array("appear"=>"appear", "not appear"=>"not appear");
$cond2 = array("contain"=>"contain","not contain"=>"not contain","appear"=>"appear", "not appear"=>"not appear");

//upper selects filling

//years
$years=array("1"=>"2003","2"=>"2004","3"=>"2005","4"=>"2006","5"=>"2007","6"=>"2008","7"=>"2008","9"=>"2010");
$xtpl->assign("YEARS",get_drop_down_list($years,"year","","Select year"));

//monthes
$mquery="SELECT * FROM monthes";
$monthes=SQL_select($mquery);

$mdroplist=array();
foreach ($monthes as $k=>$v)  $mdroplist[$v['month_id']]=$v['month_name'];
$xtpl->assign("MONTHES", get_drop_down_list($mdroplist, "month", "", "last 30 days"));

//year set
$today=getdate();
if ( (isset($_POST['year'])) and ($_POST['year'])!=0 )
    $year=$years[$_POST['year']];
else
    $year=$today['year'];

//month set
if ( (isset($_POST['month'])) and ($_POST['month'])!=0 )
    $month=$_POST['month'];
else
    $month=$today['mon'];
//days set
$days=$monthes[$month-1]['month_duration'];

$xtpl->assign("SECTION_NAME", "Statistics: Summary for ".$monthes[$month-1]['month_name']." ".$year);

//main part: get from db, replace and execute queries
$r=get_queries('answers_d','closed_d','new_d','complains_d','comments_d');
$r=replace_queries($r,array("#year#","#month#"),array($year,$month));

$d=do_queries_d($r,$days);

//drawing diagrams
foreach ($d as $dn=>$dv)
{
    $s='';
    foreach ($r as $rk=>$rv )
    {
        if (isset($dv[$rv['q_html_title']]))
        {
            $height=$dv[$rv['q_html_title']];
            $title=$height;
            $xtpl->assign("TD_CONTENT",'<a href="stat_tickets.php?day='.$dn.'&month='.$month.'&year='.$year.'&type='.$rv['q_html_title'].'">'.$dv[$rv['q_html_title']].'</a>');
        }
        else
        {
                $height='1';
                $title='0';
                $d[$dn][$rv['q_html_title']]='1';
                $xtpl->assign("TD_CONTENT",0);
        }
        $s=$s.'<a href="stat_tickets.php?day='.$dn.'&month='.$month.'&year='.$year.'&type='.$rv['q_html_title'].'"><img border="0" align="bottom" src="./images/l_'.$rv['q_image'].'.png" width="4" height="'.$height.'" alt="'.$rv['q_html_title'].': '.$height.'" title="'.$rv['q_html_title'].': '.$title.'"></a>';


        $xtpl->parse("main.stat.stat_body.list_tr.list_td");

    }
    $xtpl->assign("IMAGES",$s);
    $xtpl->parse("main.stat.graf_td");

    $xtpl->assign("CAPTION",$dn."<br>".substr($monthes[$month-1]['month_name'],0,2));
    $xtpl->parse("main.stat.graf_td_caption");

    $xtpl->assign("DAY",$dn);
    $xtpl->parse("main.stat.stat_body.list_tr");

}

$xtpl->assign("HEAD_NAME",'Days');


foreach ($r as $rk=>$rv )
{
    $xtpl->assign("HEAD_LIST_ITEM",$rv['q_html_title']);
    $xtpl->assign("HEAD_LIST_COLOR",$rv['q_color']);
    $xtpl->parse("main.stat.stat_body.head_list");
}


//average time of first answer evaluation
$avg=avg_answer_time('avg_answer_time',array("#year#","#month#"),array($year,$month));
$xtpl->assign('AVG_ANSWER',$avg['days']." days ".$avg['hours']." hours");

//average lifetime evaluation
$avg=avg_lifetime('avg_lifetime',array("#year#","#month#"),array($year,$month));
$xtpl->assign('AVG_LIFETIME',$avg['days']." days ".$avg['hours']." hours");

$xtpl->parse("main.stat_list.avg_answer");
$xtpl->parse("main.stat_list.avg_lifetime");

$xtpl->assign("ACTION",'stat.php');

$xtpl->parse("main.stat.stat_body");

$xtpl->parse("main.stat_list");
$xtpl->parse("main.stat.selects");
$xtpl->parse("main.stat");
$xtpl->parse("main");
$xtpl->out("main");
?>