<?php
require("../inc/main.php");
require_once("/usr/share/phplot/phplot.php");

extract($_GET);
if (!isset($width)) { $width = 500; }
if (!isset($height)) { $height = 400; }
// if (!isset($nb_months)) { $nb_months = 24; }

/*
 * return positive if $start_date < $end_date
 */
function diff_date( $start_date,$end_date){

  $tmp=explode("-",$start_date);
  $date2 = mktime(0, 0, 0, $tmp[1], $tmp[2], $tmp[0]);

  $tmp=explode("-",$end_date);
  $date = mktime(0, 0, 0, $tmp[1], $tmp[2], $tmp[0]);

  return floor(($date - $date2) / (3600 * 24));
}

function cmp_data($array1, $array2){
  $sum1=array_sum($array1);
  $sum2=array_sum($array2);
  if ($sum1==$sum2)
    return 0;
  if($sum1<0 AND $sum2<0)
    return ($sum1 < $sum2) ? -1 : 1;
  else
    return ($sum1 < $sum2) ? 1 : -1;
}

function getRoundMax($max){
  if (abs($max) > 0) {
    $tmp_max = abs($max);
    $exp = 0;
    while ($tmp_max > 10) {
      $tmp_max /= 10;
      $exp++;
    }
    $tmp_max = ceil($tmp_max);
    while ($exp > 0){
      $tmp_max *= 10;
      $exp--;
    }
    return $max/abs($max) * $tmp_max;
  } else {
    return 0;
  }
}

function getRoundMin($min){
  if (abs($min) > 0) {
    $tmp_min = abs($min);
    $exp = 0;
    while ($tmp_min > 10) {
      $tmp_min /= 10;
      $exp++;
    }
    $tmp_min = ceil($tmp_min);
    while ($exp > 0) {
      $tmp_min *= 10;
      $exp--;
    }
    return $min/abs($min) * $tmp_min;
  } else {
    return 0;
  }
}

if(isset($_GET['type']) AND isset($_GET['account']) AND !empty($_GET['type']) AND isset($_GET['start_date']) AND isset($_GET['end_date']) ){
  //graph 1
  if($_GET['type']=="expense" ){

    $query_account="";
    $text="";
    if(!empty($_GET['account']))
      $query_account=" AND id_account=".$_GET['account'];

    $end_date=$_GET['end_date'];
    $start_date=$_GET['start_date'];

    $start_tmp=explode("-",$start_date);
    $end_tmp=explode("-",$end_date);

    $start_year=$start_tmp[0];
    $start_month=$start_tmp[1];
    $start_day=$start_tmp[2];

    $end_year=$end_tmp[0];
    $end_month=$end_tmp[1];
    $end_day=$end_tmp[2];

    $year=$start_tmp[0];
    $month=$start_tmp[1];

    $ts = mktime(1,1,1,$start_tmp[1],1,$start_tmp[0]);

    $nb_day = ( mktime(0,0,0,$end_month,$end_day,$end_year) - mktime(0,0,0,$start_month,$start_day,$start_year)) / 86400;

    $query_date_last_real=mysql_query("select UNIX_TIMESTAMP(max(date)) from webfinance_transactions where type='real' ". $query_account)
      or wf_mysqldie();

    $date_last_real=mysql_result($query_date_last_real, 0);

    //Define the object
    $graph2=& new PHPlot($width,$height);

    $data=array();

    if( $nb_day> date('t',$ts)){

      $max=0;

      for ($day = 0; $day < $nb_day ; $day++) {

	$tmp_date=date("Y-m-d",mktime(0,0,0,$start_month,$start_day+$day,$start_year));
	$tmp_date_ex=explode("-",$tmp_date);

	if($tmp_date_ex[2]==1 )
	  $tmp=array(date("Y.m",mktime(0,0,0,$start_month,$start_day+$day,$start_year)));
	else
	  $tmp=array('');
	//outgo
	$query_sum_negative=mysql_query("SELECT SUM(amount) as sum ".
					"FROM webfinance_transactions ".
					"WHERE amount<0 AND date='".$tmp_date_ex[0]."-".$tmp_date_ex[1]."-".$tmp_date_ex[2]."' ".$query_account )
	  or wf_mysqldie();
	$res=mysql_fetch_array($query_sum_negative);
	if(empty($res['sum']))
	  $res['sum']=0;
	$tmp[]=$res['sum']*1;

	//income
	$query_sum_positive=mysql_query("SELECT SUM(amount) as sum ".
					"FROM webfinance_transactions ".
					"WHERE amount>0 AND date='".$tmp_date_ex[0]."-".$tmp_date_ex[1]."-".$tmp_date_ex[2]."' ".$query_account )
	  or wf_mysqldie();
	$res=mysql_fetch_array($query_sum_positive);
	if(empty($res['sum']))
	  $res['sum']=0;
	$tmp[]=$res['sum'];
	$max=max($max,$res['sum']);

	$data[]=$tmp;

      }
      $graph2->SetXLabelAngle(90);
      //Set titles
      $title=sprintf(_("Outgo & Income from %s to %s"), $start_date, $end_date);
      $graph2->SetYTickIncrement( round($max/10,-3) );

    }else{

      $data=array();
      $min=0;
      $max=0;

      for ($day = 1; $day <= $nb_day ; $day++) {
	$tmp=array();
	$tmp[]=$day;
	//outgo
	$query_sum_negative=mysql_query("SELECT SUM(amount) as sum ".
					"FROM webfinance_transactions ".
					"WHERE amount<0 AND date='$year-$month-$day' ".$query_account )
	  or wf_mysqldie();
	$res=mysql_fetch_array($query_sum_negative);
	if(empty($res['sum']))
	  $res['sum']=0;
	$tmp[]=$res['sum']*1;
	$min=min($min,$res['sum']);

	//income
	$query_sum_positive=mysql_query("SELECT SUM(amount) as sum ".
					"FROM webfinance_transactions ".
					"WHERE amount>0 AND date='$year-$month-$day' ".$query_account )
	  or wf_mysqldie();
	$res=mysql_fetch_array($query_sum_positive);
	if(empty($res['sum']))
	  $res['sum']=0;
	$tmp[]=$res['sum'];
	$max=max($max,$res['sum']);

	//			$query_sold=mysql_query("SELECT SUM(amount) as sum FROM webfinance_transactions WHERE date<='$year-$month-$day' ".$query_account )
	//			or wf_mysqldie();
	//			$res=mysql_fetch_array($query_sold);
	//			$tmp[]=$res['sum'];

	$data[]=$tmp;
      }

      //Set titles
      $title=sprintf(utf8_decode(_("Outgo & Income : %s-%s")), $year, $month);
      $graph2->SetDrawXGrid(true);
    }

    // First verticaly
    $range = $max + abs($min);
    $graph2->SetYTickIncrement( round($range/10,-3) );

    $graph2->SetTitle($title);
    $graph2->SetXTitle(_('Day'));
    $graph2->SetYTitle(_('Amount'));

# Make a legend for the 2 functions:
    $graph2->SetLegend(array(utf8_decode(_('Outgo')), utf8_decode(_('Income'))));

    $graph2->SetDataColors(array('#ff7f7f', '#7fff7f'));

    $graph2->SetDataType("text-data");

    $graph2->SetDataValues($data);


    $graph2->SetPlotAreaWorld(null, null, null, null);
    $graph2->plot_min_y = getRoundMin($min);
    //$graph2->plot_max_y = $tmp_max;

    $graph2->SetPlotType("area");

    //Draw it
    $graph2->DrawGraph();
  }

  if($_GET['type']=="expense_amount" ){

    $query_account="";
    $text="";
    if(!empty($_GET['account']))
      $query_account=" AND id_account=".$_GET['account'];

    $end_date=$_GET['end_date'];
    $start_date=$_GET['start_date'];

    $start_tmp=explode("-",$start_date);
    $end_tmp=explode("-",$end_date);

    $start_year=$start_tmp[0];
    $start_month=$start_tmp[1];
    $start_day=$start_tmp[2];

    $end_year=$end_tmp[0];
    $end_month=$end_tmp[1];
    $end_day=$end_tmp[2];

    $year=$start_tmp[0];
    $month=$start_tmp[1];

    $ts = mktime(1,1,1,$start_tmp[1],1,$start_tmp[0]);

    $nb_day = ( mktime(0,0,0,$end_month,$end_day,$end_year) - mktime(0,0,0,$start_month,$start_day,$start_year)) / 86400;

    $query_date_last_real=mysql_query("select UNIX_TIMESTAMP(max(date)) from webfinance_transactions where type='real' ". $query_account)
      or wf_mysqldie();

    $date_last_real=mysql_result($query_date_last_real, 0);

    //Define the object
    $graph2=& new PHPlot($width,$height);

    $data=array();

    if( $nb_day> date('t',$ts)){

      $max=0;

      for ($day = 0; $day < $nb_day ; $day++) {


	$tmp_date=date("Y-m-d",mktime(0,0,0,$start_month,$start_day+$day,$start_year));
	$tmp_date_ex=explode("-",$tmp_date);

	if($tmp_date_ex[2]==1 )
	  $tmp=array(date("Y.m",mktime(0,0,0,$start_month,$start_day+$day,$start_year)));
	else
	  $tmp=array('');

	// prevs
	$query_sold=mysql_query("SELECT SUM(amount) as sum ".
				"FROM webfinance_transactions ".
				"WHERE date<='$tmp_date' ".$query_account )
	  or wf_mysqldie();
	$tmp_val=mysql_result($query_sold, 0);
	if(empty($tmp_val))
	  $tmp_val=0;
	$tmp[]=$tmp_val;
	$max=max($max,mysql_result($query_sold, 0));

	// real
	if( mktime(0,0,0,$start_month,$start_day,$start_year) <= $date_last_real) {
	  $query_sold=mysql_query("SELECT SUM(amount) as sum ".
				  "FROM webfinance_transactions ".
				  "WHERE date<='$tmp_date' AND type='real' ".$query_account )
	    or wf_mysqldie();
	  $tmp_val=mysql_result($query_sold, 0);
	  if(empty($tmp_val))
	    $tmp_val=0;
	  $tmp[]=$tmp_val;
	  $max=max($max,mysql_result($query_sold, 0));
	}else
	  $tmp[]='';

	$data[]=$tmp;
      }
      $graph2->SetXLabelAngle(90);
      //Set titles
      $title=sprintf(_("Cash flow from %s to %s"),$start_date, $end_date);


    }else{

      $nb_day = date('t',$ts);

      $today=date("d");

      $max=0;
      $min=0;
      for ($day = 1; $day <= $nb_day ; $day++) {
	$tmp=array($day);

	// prevs
	$query_sold=mysql_query("SELECT SUM(amount) as sum ".
				"FROM webfinance_transactions ".
				"WHERE date<='$year-$month-$day' ".$query_account )
	  or wf_mysqldie();
	$tmp_val=mysql_result($query_sold, 0);
	if(empty($tmp_val))
	  $tmp_val=0;
	$tmp[]=$tmp_val;
	$max=max($max,mysql_result($query_sold, 0));
	$min=min($min,mysql_result($query_sold, 0));
	// real
	if(mktime(0, 0, 0, $month, $day, $year) <= $date_last_real) {
	  $query_sold=mysql_query("SELECT SUM(amount) as sum ".
				  "FROM webfinance_transactions ".
				  "WHERE date<='$year-$month-$day' AND type='real' ".$query_account )
	    or wf_mysqldie();
	  $tmp_val=mysql_result($query_sold, 0);
	  if(empty($tmp_val))
	    $tmp_val=0;
	  $tmp[]=$tmp_val;
	  $max=max($max,mysql_result($query_sold, 0));
	  $min=min($min,mysql_result($query_sold, 0));
	}else
	  $tmp[]='';

	$data[]=$tmp;
      }
      //Set titles
      $title=sprintf(utf8_decode(_("Cash flow : %s-%s")),$year, $month);
      $graph2->SetXTitle(_('Day'));

    }

    //	  echo "<pre/>";
    //print_r($data);


    //$graph2->SetImageBorderType('plain');

    $graph2->SetTitle($title);

    $graph2->SetYTitle(_('Amount'));

    $range = $max + abs($min);
    $graph2->SetYTickIncrement(round($range/10,-3));
    //$graph2->

    $graph2->SetLineStyles(array('solid','solid'));
    //		$graph2->SetLineWidths(array('3','2'));

# Make a legend for the 2 functions:
    $graph2->SetLegend(array(utf8_decode(_('prev')), utf8_decode(_('real')) ));

    $graph2->SetDataColors(array('green', 'red'));

    $graph2->SetDataType("text-data");

    $graph2->SetDataValues($data);

    $graph2->SetPlotAreaWorld(null, null, null, null);
    $graph2->plot_min_y = getRoundMin($min);
    //$graph2->plot_max_y = getRoundMax($max);

    $graph2->SetPlotType("lines");

    //Draw it
    $graph2->DrawGraph();
  }


  //graph 2

  if($_GET['type']=="category"){

    if(!empty($_GET['end_date']) AND !empty($_GET['start_date']) ){
      $end_date=$_GET['end_date'];
      $start_date=$_GET['start_date'];

      $query_account="";
      $name="";
      if(!empty($_GET['account'])){
	$query=mysql_query("SELECT id, account_name FROM  webfinance_accounts WHERE id=".$_GET['account'])
	  or wf_mysqldie();
	if(mysql_num_rows($query)==1){
	  $query_account=" AND id_account=".$_GET['account'];
	  $account=mysql_fetch_assoc($query);
	  $name.=$account['account_name'];
	}
      }else
	$name.=_("All accounts");

      $query_categories=mysql_query("SELECT id FROM webfinance_categories") or wf_mysqldie();

      $data_positive=array();
      $data_negative=array();

      $nb_categories=mysql_num_rows($query_categories);

      $tmp_categ=array();

      $query_sum_category=mysql_query("SELECT SUM(amount) as sum ".
				      "FROM webfinance_transactions ".
				      "WHERE  id_category<1 ".
				      "AND amount>0 ".
				      "AND date BETWEEN '$start_date' AND '$end_date' ".$query_account)
	or wf_mysqldie();
      $res=mysql_fetch_assoc($query_sum_category);
      if($res['sum']!=0)
	$tmp_categ[1][]=array(_('unknown'),$res['sum'],'peru');

      $query_sum_category=mysql_query("SELECT SUM(amount) as sum ".
				      "FROM webfinance_transactions ".
				      "WHERE  id_category<1 ".
				      "AND amount<0 ".
				      "AND date BETWEEN '$start_date' AND '$end_date' ".$query_account)
	or wf_mysqldie();
      $res=mysql_fetch_assoc($query_sum_category);
      if($res['sum']!=0)
	$tmp_categ[0][]=array(_('unknown'),$res['sum'],'peru');

      while($category=mysql_fetch_assoc($query_categories)){
	$query_sum_category=mysql_query("SELECT SUM(amount) as sum , webfinance_categories.name as name, webfinance_categories.color as color ".
					"FROM webfinance_categories ".
					"LEFT JOIN webfinance_transactions ON webfinance_categories.id=webfinance_transactions.id_category ".
					"WHERE id_category=".$category['id']." ".
					"AND date BETWEEN '$start_date' ".
					"AND '$end_date' ".$query_account." ".
					"GROUP BY webfinance_transactions.id_category")
	  or wf_mysqldie();
	$res=mysql_fetch_assoc($query_sum_category);
	if(!empty($res['sum']) AND $res['sum']<0){
	  $tmp_categ[0][]=array(utf8_decode($res['name']),$res['sum'],$res['color']);
	}else if($res['sum']>0){
	  $tmp_categ[1][]=array(utf8_decode($res['name']),$res['sum'],$res['color']);
	}
      }
      //			echo "<pre/>";
      //			print_r($tmp_categ);

      $colors=array();
      $legends=array();

      $plot =& new PHPlot($width,$height);
      //$plot->SetImageBorderType('plain');
      $plot->SetDataType('text-data');
      $plot->SetPlotType('pie');


      if(count($tmp_categ)>1){

	$plot->SetLabelScalePosition(0.37);

	usort($tmp_categ[0],"cmp_data");
	usort($tmp_categ[1],"cmp_data");

	//			echo "<hr/>";
	//			print_r($tmp_categ);

	//positive values
	$nb_categ=count($tmp_categ[1]);
	$pos=1;
	$sum_positive=0;
	foreach($tmp_categ[1] as $row){
	  $tmp=array($row[0]);
	  for($i=1;$i<=$nb_categ;$i++)
	    $tmp[]=0;
	  $tmp[$pos]=$row[1];
	  $data_positive[]=$tmp;
	  $pos++;
	  $sum_positive=$row[1]+$sum_positive;
	}

	//negative values
	$nb_categ=count($tmp_categ[0]);
	$pos=1;
	$sum_negative=0;
	foreach($tmp_categ[0] as $row){
	  $tmp=array($row[0]);
	  for($i=1;$i<=$nb_categ;$i++)
	    $tmp[]=0;
	  $tmp[$pos]=$row[1];
	  $data_negative[]=$tmp;
	  $pos++;
	  $sum_negative=$row[1]+$sum_negative;
	}

	//$start_date_ex=explode("-",$start_date);
	//$end_date_ex=explode("-",$end_date);

	if(isset($_GET['sign']) AND $_GET['sign']=="negative"){
	  $plot->SetDataValues($data_negative);
	  $plot->SetTitle(utf8_decode(sprintf(_("Outgo by category\n%s\nfrom %s to %s"), $name, $start_date, $end_date)));
	  foreach ($data_negative as $row){
	    $sum=array_sum($row);
	    $legends[]=$row[0]." : ".sprintf("%01.2f", $sum);
	  }
	  //color
	  foreach($tmp_categ[0] as $row)
	    $colors[]=$row[2];
	}else{
	  $plot->SetDataValues($data_positive);
	  $plot->SetTitle(utf8_decode(sprintf(_("Income by category\n%s\nfrom %s to %s"),$name, $start_date, $end_date)));

	  foreach ($data_positive as $row){
	    $sum=array_sum($row);
	    $legends[]=$row[0]." : ".sprintf("%01.2f", $sum);
	  }
	  //color
	  foreach($tmp_categ[1] as $row)
	    $colors[]=$row[2];
	}

	$plot->SetDataColors($colors);
	$plot->SetLegend	($legends);

      }else{
	$data=array(array('',100));
	$plot->SetDataValues($data);
	$plot->SetDataColors(array('white'));
	$plot->SetLegend	(array(_('Nothing')));
      }

      $plot->DrawGraph();

    }else{

      $query_categories=mysql_query("SELECT id FROM webfinance_categories ORDER BY name")
	or wf_mysqldie();

      $data_positive=array();
      $data_negative=array();

      $nb_categories=mysql_num_rows($query_categories);

      $tmp_categ=array();

      $query_sum_category=mysql_query("SELECT SUM(amount) as sum FROM webfinance_transactions WHERE  id_category<1 AND amount>0 ")
	or wf_mysqldie();
      $res=mysql_fetch_assoc($query_sum_category);
      if($res['sum']!=0)
	$tmp_categ[1][]=array('unknown',$res['sum']);

      $query_sum_category=mysql_query("SELECT SUM(amount) as sum FROM webfinance_transactions WHERE  id_category<1 AND amount<0 ")
	or wf_mysqldie();
      $res=mysql_fetch_assoc($query_sum_category);
      if($res['sum']!=0)
	$tmp_categ[0][]=array('unknown',$res['sum']);

      while($category=mysql_fetch_assoc($query_categories)){
	$query_sum_category=mysql_query("SELECT SUM(amount) as sum , webfinance_categories.name as name ".
					"FROM webfinance_categories LEFT JOIN webfinance_transactions ON webfinance_categories.id=webfinance_transactions.id_category ".
					"WHERE id_category=".$category['id']." ".
					"GROUP BY webfinance_transactions.id_category ")
	  or wf_mysqldie();
	$res=mysql_fetch_assoc($query_sum_category);
	if(!empty($res['sum']) AND $res['sum']<0){
	  $tmp_categ[0][]=array($res['name'],$res['sum']);
	}else if($res['sum']>0){
	  $tmp_categ[1][]=array($res['name'],$res['sum']);
	}else{
	  $tmp_categ[2][]=array($res['name'],$res['sum']);
	}
      }

      usort($tmp_categ[0],"cmp_data");
      usort($tmp_categ[1],"cmp_data");

      //positive values
      $nb_categ=count($tmp_categ[1]);
      $pos=1;
      $sum_positive=0;
      foreach($tmp_categ[1] as $row){
	$tmp=array($row[0]);
	for($i=1;$i<=$nb_categ;$i++)
	  $tmp[]=0;
	$tmp[$pos]=$row[1];
	$data_positive[]=$tmp;
	$pos++;
	$sum_positive=$row[1]+$sum_positive;
      }

      //negative values
      $nb_categ=count($tmp_categ[0]);
      $pos=1;
      $sum_negative=0;
      foreach($tmp_categ[0] as $row){
	$tmp=array($row[0]);
	for($i=1;$i<=$nb_categ;$i++)
	  $tmp[]=0;
	$tmp[$pos]=$row[1];
	$data_negative[]=$tmp;
	$pos++;
	$sum_negative=$row[1]+$sum_negative;
      }

      $plot =& new PHPlot($width,$height);
      //$plot->SetImageBorderType('plain');
      //$plot->SetShading(0);

      $plot->SetDataType('text-data');
      $plot->SetPlotType('pie');

      $plot->SetDataColors(
			   array(	'red', 	'blue', 'yellow', 'cyan',
					'magenta', 'brown', 'lavender', 'pink',
					'gray',	'orange', 'green')
			   );

      $plot->SetLabelScalePosition(0.3);

      if(isset($_GET['sign']) AND $_GET['sign']=="negative"){
	$plot->SetDataValues($data_negative);
	$plot->SetTitle(utf8_decode(_("Outgo by category / all history")));
	$legend=array();
	foreach ($data_negative as $row){
	  $sum=array_sum($row);
	  $legend[]=$row[0]." : ".sprintf("%01.2f", $sum);
	}
      }else{
	$plot->SetDataValues($data_positive);
	$plot->SetTitle("Income by category / all history");
	$legend=array();
	foreach ($data_positive as $row){
	  $sum=array_sum($row);
	  $legend[]=$row[0]." : ".sprintf("%01.2f", $sum);
	}
      }
      $plot->SetLegend	($legend);
      $plot->DrawGraph();
    }
  }
 }else if(isset($_GET['type'])=="amount"){

  $query_account="";
  $text="";
  if(!empty($_GET['account'])){
    $query_account=" AND id_account=".$_GET['account'];
    $query=mysql_query("SELECT MIN(date) as min , MAX(date) as max ".
		       "FROM webfinance_transactions ".
		       "WHERE id_account=".$_GET['account'] )
      or wf_mysqldie();
  }else
    $query=mysql_query("SELECT MIN(date) as min , MAX(date) as max FROM webfinance_transactions ") or wf_mysqldie();

  $res=mysql_fetch_assoc($query);
  if(!empty($res['min']) AND !empty($res['max'])){
    $end_date=$res['max'];
    $start_date=$res['min'];
  }else{
    $start_date=date("Y-m-d");
    $end_date=date("Y-m-d" , mktime(0, 0, 0, date("m")+1, date("d"), date("Y")) );
  }

  if(isset($_GET['end_date']) AND !empty($_GET['end_date'])){
    if(diff_date($start_date,$_GET['end_date'])>0)
      $end_date=$_GET['end_date'];
  }

  $data=array();
  $max=1;
  $min=-1;
  $nb_day=diff_date($start_date,$end_date);

  if($nb_day>0){

    $var=explode("-",$start_date);

    $nb_day = $nb_day+30;
    $query_date_last_real=mysql_query("select UNIX_TIMESTAMP(max(date)) from webfinance_transactions where type='real' ". $query_account)
      or wf_mysqldie();

    $date_last_real=mysql_result($query_date_last_real, 0);

    for($step = 0; $step < $nb_day ; $step++) {

      $current_date=date("Y-m-d" , mktime(0, 0, 0, $var[1],1+$step, $var[0]) );

      $date_ex=explode("-",$current_date);

      $tmp=array();

      if($date_ex[2]==1)
	$tmp[]=date("M y", mktime(0, 0, 0, $var[1],1+$step, $var[0]) );
      else
	$tmp[]='';

      // prevs
      $query_sold=mysql_query("SELECT SUM(amount) as sum FROM webfinance_transactions WHERE date<='$current_date' ".$query_account )
	or wf_mysqldie();
      $res=mysql_result($query_sold, 0);
      if(empty($res))
	$res=0;
      $tmp[]=$res;
      $max=max($max,mysql_result($query_sold, 0));

      // real
      if(mktime(0, 0, 0, $var[1],1+$step, $var[0]) <= $date_last_real) {
	$query_sold=mysql_query("SELECT SUM(amount) as sum FROM webfinance_transactions WHERE date<='$current_date' AND type='real' ".$query_account )
	  or wf_mysqldie();
	$res=mysql_result($query_sold, 0);
	if(empty($res))
	  $res=0;
	$tmp[]=$res;
	$max=max($max,mysql_result($query_sold, 0));
      }

      $data[]=$tmp;
    }
  }else{
    $data=array( array('','',''));
  }

  //		echo "<pre/>";
  //		print_r($data);

  //Define the object
  $graph2=& new PHPlot($width,$height);

  //Set titles
  $title=utf8_decode(_("Cash flow / all history"));

  $graph2->SetTitle($title);
  $graph2->SetXTitle('');
  //$graph2->SetNumXTicks($nb_day/40);
  $graph2->SetYTitle(_('Amount (Euro)'));

  if($max>10000)
    $graph2->SetYTickIncrement( round($max/10,-3) );

  //		$graph2->SetDrawXGrid(true);
  //		$graph2->SetXTickIncrement(30);

  $graph2->SetXLabelAngle(90);

# Make a legend for the 2 functions:

  //		$graph2->SetLineWidths(array('1','1'));

  $graph2->SetLineStyles(array('solid','solid'));

  $graph2->SetLegend(array(utf8_decode(_('prev')), utf8_decode(_('real')) ));

  $graph2->SetDataColors(array( 'green' ,'red'));

  $graph2->SetDataType("text-data");

  $graph2->SetDataValues($data);

  $graph2->SetPlotType("lines");


  //Draw it
  $graph2->DrawGraph();


 }else{
  $query_account="";
  $text="";
  if(!empty($_GET['account'])){
    $query_account=" AND id_account=".$_GET['account'];
    $query=mysql_query("SELECT MIN(date) as min , UNIX_TIMESTAMP(MIN(date)) , MAX(date) as max , UNIX_TIMESTAMP(MAX(date)) FROM webfinance_transactions WHERE id_account=".$_GET['account'] )
      or wf_mysqldie();
  }else{
    $query=mysql_query("SELECT MIN(date) as min , UNIX_TIMESTAMP(MIN(date)) , MAX(date) as max , UNIX_TIMESTAMP(MAX(date)) FROM webfinance_transactions ")
      or wf_mysqldie();
  }

  list($date_min, $date_min_ts, $date_max, $date_max_ts)=mysql_fetch_array($query);

  if(!empty($date_min) AND !empty($date_max)){
    $start_date = $date_min;
    $start_date_ts = $date_min_ts;

    $end_date = $date_max;
    $end_date_ts = $date_max_ts;
  }else{
    $start_date = date("Y-m-d");
    $start_date_ts = mktime();

    $end_date=date("Y-m-d" ,  mktime(0, 0, 0, date("m")+1, date("d"), date("Y")) );
    $end_date_ts =  mktime(0, 0, 0, date("m")+1, date("d"), date("Y"));
  }

  if(isset($_GET['end_date']) AND !empty($_GET['end_date'])){
    if(diff_date($start_date,$_GET['end_date'])>0)
      $end_date=$_GET['end_date'];
  }

  $data=array();
  $max=1;
  $min=-1;
  $nb_day=diff_date($start_date,$end_date);

  if($nb_day>0){

    $var=explode("-",$start_date);

    $nb_month = ($nb_day/30)+1;

    $begin_date=date("Y-m-d" , mktime(0, 0, 0, $var[1],1, $var[0]));
    $begin_date_ts = mktime(0, 0, 0, $var[1],1, $var[0]);

    $q_trs_neg = mysql_query("SELECT amount , UNIX_TIMESTAMP(date) as ts_date FROM webfinance_transactions WHERE amount<0 ")
      or die(mysql_error());
    while($row = mysql_fetch_assoc($q_trs_neg))
      $trs_neg[] = $row;
    mysql_free_result($q_trs_neg);

    $q_trs_pos = mysql_query("SELECT amount , UNIX_TIMESTAMP(date) as ts_date FROM webfinance_transactions WHERE amount>0 ")
      or die(mysql_error());
    while($row = mysql_fetch_assoc($q_trs_pos))
      $trs_pos[] = $row;
    mysql_free_result($q_trs_pos);

    for($step = 0; $step < $nb_month ; $step++ ){

      $end_date=date("Y-m-d" , mktime(0, 0, 0, $var[1]+$step,0, $var[0]) );
      $end_date_ts = mktime(0, 0, 0, $var[1]+$step,0, $var[0]);

      $begin=explode("-",$begin_date);

      $tmp[0]=date("M y", mktime(0, 0, 0, $begin[1], 1, $begin[0]) );

      //outgo
      $sum = 0;
      foreach($trs_neg as $tr){
	if($tr['ts_date'] <= $end_date_ts AND $tr['ts_date'] >= $begin_date_ts )
	  $sum  = $sum + $tr['amount'];
      }
      $tmp[1]= $sum*-1 ;
      $max = max($max,$tmp[1]);
      $min = min($min,$sum);

      //income
      $sum = 0;
      foreach($trs_pos as $tr){
	if($tr['ts_date'] <= $end_date_ts AND $tr['ts_date'] >= $begin_date_ts )
	  $sum  = $sum + $tr['amount'];
      }
      $tmp[2]=$sum*1;
      $max = max($max,$sum);
      $min = min($min,$sum);

      $data[] = $tmp;

      $end=explode("-",$end_date);
      $begin_date=date("Y-m-d" , mktime(0, 0, 0, $end[1]+1, 1 , $end[0]) );
      $begin_date_ts=mktime(0, 0, 0, $end[1]+1, 1 , $end[0]);

    }

  }else{
    $data=array(array('','',''));
  }

  //		echo "<pre/>";


  //Define the object
  $graph2=& new PHPlot($width,$height);

  //Set titles
  $title=utf8_decode(_("Income & Outgo / all history"));

  $graph2->SetTitle($title);
  $graph2->SetXTitle('');
  $graph2->SetNumXTicks($nb_day);

  $graph2->SetYTitle(_('Amount (Euro)'));

  if($max>10000)
    $graph2->SetYTickIncrement( round($max/10,-3) );
  //$graph2->SetXAxisPosition(0);
  $graph2->SetXLabelAngle(90);

# Make a legend for the 2 functions:
  $graph2->SetLegend(array(utf8_decode(_('Outgo')), utf8_decode(_('Income'))));

  $graph2->SetDataColors(array('orange', 'green'));

  $graph2->SetDataType("text-data");

  $graph2->SetDataValues($data);

  $graph2->SetPlotType("bars");

  //$graph2->SetDrawXGrid(true);

  $graph2->SetXTickIncrement(0.5);

  //$plot->SetYTickIncrement(5);

  //Draw it
  $graph2->DrawGraph();


 }

// vim: sw=6

?>
