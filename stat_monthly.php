<?php
/**
 * stat_monthly.php  - monthly statistic page, show statistic for selected year(current by default)
 *
 * very similar to the stat.php except search parameters in query
 * include validator.php
 */

include "validator.php";

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

$today=getdate();
//year set
if ( (isset($_POST['year'])) and ($_POST['year'])!=0 )
    $year=$years[$_POST['year']];
else
    $year=$today['year'];

$xtpl->assign("SECTION_NAME", "Statistics: Summary for ".$year." year");

//main part: get from db, replace and execute queries
$r=get_queries('answers_y','closed_y','new_y','complains_y','comments_y');
$r=replace_queries($r,array("#year#"),array($year));
$d=do_queries_d($r,12);

//drawing diagrams
foreach ($d as $dn=>$dv)
{
    $s='';
    foreach ($r as $rk=>$rv )
    {
        if (isset($dv[$rv['q_html_title']]))
        {
            $title=$dv[$rv['q_html_title']];
            $height=$title/6;
            $xtpl->assign("TD_CONTENT",$dv[$rv['q_html_title']]);
        }
        else
        {
                $height='1';
                $title='0';
                $d[$dn][$rv['q_html_title']]='1';
                $xtpl->assign("TD_CONTENT",0);
        }
        $s=$s.'<img border="0" align="bottom" src="./images/l_'.$rv['q_image'].'.png" width="6" height="'.$height.'" alt="'.$rv['q_html_title'].': '.$title.'" title="'.$rv['q_html_title'].': '.$title.'">';


        $xtpl->parse("main.stat.stat_body.list_tr.list_td");

    }

    //average time of first answer evaluation
    $avg=avg_answer_time('avg_answer_time',array("#year#","#month#"),array($year,$dn));
    $xtpl->assign('TD_CONTENT',$avg['days']." days ".$avg['hours']." hours");
    $xtpl->parse("main.stat.stat_body.list_tr.list_td");

    //average lifetime evaluation
    $avg=avg_lifetime('avg_lifetime',array("#year#","#month#"),array($year,$dn));
    $xtpl->assign('TD_CONTENT',$avg['days']." days ".$avg['hours']." hours");
    $xtpl->parse("main.stat.stat_body.list_tr.list_td");


    $xtpl->assign("IMAGES",$s);
    $xtpl->parse("main.stat.graf_td");

    $xtpl->assign("CAPTION",$dn."<br>".substr($monthes[$dn-1]['month_name'],0,3));
    $xtpl->parse("main.stat.graf_td_caption");


    $xtpl->assign("DAY",$dn);
    $xtpl->parse("main.stat.stat_body.list_tr");

}

$xtpl->assign("HEAD_NAME",'Monthes');


foreach ($r as $rk=>$rv )
{
    $xtpl->assign("HEAD_LIST_ITEM",$rv['q_html_title']);
    $xtpl->assign("HEAD_LIST_COLOR",$rv['q_color']);
    $xtpl->parse("main.stat.stat_body.head_list");
}

    $xtpl->assign("HEAD_LIST_ITEM",'avg_answer_time');
    $xtpl->assign("HEAD_LIST_COLOR",'');
    $xtpl->parse("main.stat.stat_body.head_list");

    $xtpl->assign("HEAD_LIST_ITEM",' avg_life_time ');
    $xtpl->assign("HEAD_LIST_COLOR",'');
    $xtpl->parse("main.stat.stat_body.head_list");



$xtpl->assign("ACTION",'stat_monthly.php');


$xtpl->parse("main.stat.stat_body");

$xtpl->parse("main.stat_list");
$xtpl->parse("main.stat.selects");
$xtpl->parse("main.stat");
$xtpl->parse("main");
$xtpl->out("main");
?>