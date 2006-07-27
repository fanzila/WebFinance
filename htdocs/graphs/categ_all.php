<?php
require("../inc/main.php");
//require_once("/usr/share/phplot/phplot.php");
require_once("phplot_cvs.php"); //<- this is the newest phplot than the debian package (stackedbars support)

must_login();

$ttf_dir = "/usr/share/fonts/truetype/freefont";

if (!isset($width))
  $width = 900;
if (!isset($height))
  $height = 400;

/*
 * functions
 *
 */
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


/*
 * Evolution de chaque categorie depuis le début
 */
if( isset($_GET['sign']) AND isset($_GET['plot']) ){

  if($_GET['plot']=="bars"){

    /*
     * calculer les dates min et max des opÃ©rations
     */

    if($_GET['sign']=="positive"){
      //positive
      $query_positive=mysql_query("SELECT ".
				  "UNIX_TIMESTAMP(MIN(date)) as min, ".
				  "UNIX_TIMESTAMP(MAX(date)) as max ".
				  "FROM webfinance_transactions ".
				  "WHERE amount>0 ")
	or die(mysql_error());
      list($min,$max) = mysql_fetch_array($query_positive);

    }else if($_GET['sign']=="negative"){
      //negative
      $query_negative=mysql_query("SELECT ".
				  "UNIX_TIMESTAMP(MIN(date)) as min, ".
				  "UNIX_TIMESTAMP(MAX(date)) as max ".
				  "FROM webfinance_transactions ".
				  "WHERE amount<0 ")
	or die(mysql_error());
      list($min,$max) = mysql_fetch_array($query_negative);
    }

    //Calculer le nom de jours
    if(!empty($min) AND !empty($max)){
      if($max==$min ){
	$start_date=date("Y-m-d",$min);
	$max = mktime(23,59,59,date("m",$min),date("d",$min),date("Y",$min));
	$end_date=date("Y-m-d", $max);
      }else{
	$end_date= date("Y-m-d",$max);
	$start_date=date("Y-m-d",$min);
      }
    }else{
      $start_date=date("Y-m-d");
      $end_date=date("Y-m-d" , mktime(0, 0, 0, date("m")+1, date("d"), date("Y")) );

      $max=mktime();
      $min=mktime(0, 0, 0, date("m")+1, date("d"), date("Y"));
    }

    /*
     * identifier les catÃ©gories
     * - catÃ©gories de dÃ©penses
     * - catÃ©gories de revenus
     */
    $query_categories= mysql_query("SELECT ".
				   "webfinance_categories.name as name , ".
				   "webfinance_categories.color as color,  ".
				   "id_category as id , ".
				   "max(amount) as max, ".
				   "min(amount) as min ".
				   "FROM webfinance_categories LEFT JOIN webfinance_transactions ON webfinance_categories.id=webfinance_transactions.id_category ".
				   "WHERE id_category>0 ".
				   "GROUP BY id_category ".
				   "ORDER BY max DESC")
      or die(mysql_error());

    $categ_outgo=array();
    $categ_income=array();

    while($categ=mysql_fetch_assoc($query_categories)){
      if($categ['max']>0)
	$categ_income[]=array('id'=>$categ['id'], 'name'=>$categ['name'], 'color'=>$categ['color']);
      if($categ['min']<0)
	$categ_outgo[]=array('id'=>$categ['id'], 'name'=>$categ['name'], 'color'=>$categ['color']);
    }

    //initialisation
    $data=array(array('','',''));
    $data_income=array();
    $data_outgo=array();

    $min_outgo=0;
    $max_income=0;

    if($max>=$min){

      $start_ex=explode("-",$start_date);
      $end_ex=explode("-",$end_date);

      $begin_date=date("Y-m-d" , mktime(0, 0, 0, $start_ex[1],1, $start_ex[0]));
      $end_date=date("Y-m-d" , mktime(0, 0, 0, $end_ex[1],0, $end_ex[0]));

      $nb_month = date("Y",$max)*12 + date("m",$max) - date("Y",$min)*12 - date("m", $min) + 1;

      for($step = 0; $step < $nb_month ; $step++) {

	$current_date=date("Y-m-d" , mktime(0, 0, 0, $start_ex[1]+$step,1, $start_ex[0]) );
	$current_date_end=date("Y-m-d" , mktime(0, 0, 0, $start_ex[1]+$step+1,0, $start_ex[0]) );

	//echo $current_date." to ".$current_date_end."<br/>";

	$date_ex=explode("-",$current_date);

	$tmp_outgo=array();
	$tmp_income=array();

	$tmp_outgo[]=date("M y", mktime(0, 0, 0, $start_ex[1]+$step, 1 , $start_ex[0]) );
	$tmp_income[]=date("M y", mktime(0, 0, 0, $start_ex[1]+$step, 1 , $start_ex[0]) );


	if($_GET['sign']=="negative"){

	  // -- outgo --
	  $sum=0;
	  foreach($categ_outgo as $category){
	    $query_categ=mysql_query("SELECT SUM(amount) as sum ".
				     "FROM webfinance_transactions ".
				     "WHERE id_category=".$category['id']." ".
				     "AND type='real' ".
				     "AND date BETWEEN '$current_date' AND '$current_date_end' ")
	      or die(mysql_error());
	    $tmp=mysql_result($query_categ,0);
	    if( empty($tmp) or $tmp>0)
	      $tmp=0;
	    $tmp_outgo[]=abs($tmp);
	    $sum=$sum+$tmp;
	  }
	  //prev , asap
	  $query_categ=mysql_query("SELECT SUM(amount) as sum ".
				   "FROM webfinance_transactions ".
				   "WHERE amount<0 ".
				   "AND type<>'real' ".
				   "AND date BETWEEN '$current_date' AND '$current_date_end' ")
	    or die(mysql_error());
	  $tmp=mysql_result($query_categ,0);
	  if( empty($tmp) or $tmp>0)
	    $tmp=0;
	  $tmp_outgo[]=abs($tmp);
	  $sum=$sum+$tmp;

	  //unknow real
	  $query_categ=mysql_query("SELECT SUM(amount) as sum ".
				   "FROM webfinance_transactions ".
				   "WHERE amount<0 ".
				   "AND id_category<1 ".
				   "AND type='real' ".
				   "AND date BETWEEN '$current_date' AND '$current_date_end' ")
	    or die(mysql_error());
	  $tmp=mysql_result($query_categ,0);
	  if( empty($tmp) or $tmp>0)
	    $tmp=0;
	  $tmp_outgo[]=abs($tmp);
	  $sum=$sum+$tmp;

	  $min_outgo=min($min_outgo,$sum);

	  //data
	  $data_outgo[]=$tmp_outgo;

	}else if($_GET['sign']=="positive"){

	  // -- income --
	  $sum=0;
	  foreach($categ_income as $category){
	    $query_categ=mysql_query("SELECT SUM(amount) as sum ".
				     "FROM webfinance_transactions ".
				     "WHERE id_category=".$category['id']." ".
				     "AND type='real' ".
				     "AND date BETWEEN '$current_date' AND '$current_date_end' ")
	      or die(mysql_error());
	    $tmp=mysql_result($query_categ,0);
	    if( empty($tmp) or $tmp<0)
	      $tmp=0;
	    $tmp_income[]=$tmp;
	    $sum=$sum+$tmp;
	  }
	  //prev and asap
	  $query_categ=mysql_query("SELECT SUM(amount) as sum ".
				   "FROM webfinance_transactions ".
				   "WHERE amount>0 ".
				   "AND type<>'real' ".
				   "AND date BETWEEN '$current_date' AND '$current_date_end' ")
	    or die(mysql_error());
	  $tmp=mysql_result($query_categ,0);
	  if( empty($tmp) or $tmp<0)
	    $tmp=0;
	  $tmp_income[]=$tmp;
	  $sum=$sum+$tmp;

	  //unkown real
	  $query_categ=mysql_query("SELECT SUM(amount) as sum ".
				   "FROM webfinance_transactions ".
				   "WHERE amount>0 ".
				   "AND id_category<1 ".
				   "AND type='real' ".
				   "AND date BETWEEN '$current_date' AND '$current_date_end' ")
	    or die(mysql_error());
	  $tmp=mysql_result($query_categ,0);
	  if( empty($tmp) or $tmp<0)
	    $tmp=0;
	  $tmp_income[]=$tmp;
	  $sum=$sum+$tmp;

	  $max_income=max($max_income,$sum);

	  $data_income[]=$tmp_income;
	}
      }
    }

    //Define the object
    $graph2=& new PHPlot($width,$height);
    //$graph2->SetImageBorderType('plain');

    /*
     * $plot->SetPlotAreaPixels($x1, $y1, $x2, $y2)
     */
    $graph2->SetPlotAreaPixels(80, 40, 720, 350);

    /*
     * $plot->SetLegendPixels($x, $y)
     */
    $graph2->SetLegendPixels(730, 40);

    $legends=array();
    $colors=array();

    //echo "<pre/>";

    if($_GET['sign']=="positive"){

      if(count($data_income)==0){
	$data_income = array(array('',0) );
      }

      //Set titles
      $title=utf8_decode(_("Income by category / all history"));

      $graph2->SetYTitle('€');

      foreach($categ_income as $category){
	$legends[]=utf8_decode($category['name']);
	$colors[]=$category['color'];
      }
      $legends[]="prev,asap";
      $colors[]="orchid";

      $legends[]="unknown";
      $colors[]="peru";

      $data=$data_income;

      $graph2->SetDataValues($data);

      //echo $max_income." ".getRoundMax($max_income);

      if($max_income>0){
	$graph2->SetPlotAreaWorld(null, null, null,getRoundMax($max_income));

	$range = $max_income;

	if($range != 0 AND abs($range)>1000 ){
	  $ratioy = 1000*$height/$range; // $ratioy = nb of pixels per 1000 euro
	  if ($ratioy > 20 ) {
	    $graph2->SetYTickIncrement( 1000 );
	  } else if ($ratioy > 9) {
	    $graph2->SetYTickIncrement( 2500 );
	  } else if ($ratioy > 5) {
	    $graph2->SetYTickIncrement( 5000 );
	  } else if($ratioy > 2) {
	    $graph2->SetYTickIncrement( 10000 );
	  }
	}

      }

    }else{

      if(count($data_outgo)==0){
	$data_outgo = array(array('',0) );
      }

      //Set titles
      $title= utf8_decode(_("Outgo by category / all history"));

      $graph2->SetYTitle('-€');


      foreach($categ_outgo as $category){
	$legends[] = utf8_decode($category['name']);
	$colors[] = $category['color'];
      }

      $legends[]="prev,asap";
      $colors[]="orchid";

      $legends[]="unknown";
      $colors[]="peru";

      $data=$data_outgo;

      $graph2->SetDataValues($data);

      if(abs($min_outgo)>0){
	$graph2->SetPlotAreaWorld(null, null, null, getRoundMax(abs($min_outgo)));
	$range = abs($min_outgo);

	if($range != 0 AND abs($range)>1000 ){
	  $ratioy = 1000*$height/$range; // $ratioy = nb of pixels per 1000 euro
	  if ($ratioy > 20) {
	    $graph2->SetYTickIncrement( 1000 );
	  } else if ($ratioy > 9) {
	    $graph2->SetYTickIncrement( 2500 );
	  } else if ($ratioy > 5) {
	    $graph2->SetYTickIncrement( 5000 );
	  } else if($ratioy > 2) {
	    $graph2->SetYTickIncrement( 10000 );
	  }
	}
      }

    }

    $graph2->SetTitle($title);
    $graph2->SetXTitle('');

    $graph2->SetXTickLabelPos('none');
    $graph2->SetXTickPos('none');

    //Legende
    $graph2->SetLegend($legends);

    //$graph2->SetXAxisPosition(0);
    $graph2->SetXLabelAngle(90);

    $graph2->SetDataColors($colors);

    $graph2->SetDataType("text-data");

    $graph2->SetPlotType("stackedbars");

    $fonts = array(
		   'title_font' => array('size' => 13, 'font'=>$GLOBALS['_SERVER']['DOCUMENT_ROOT']."/css/themes/".$User->prefs->theme."/buttonfont.ttf"),
		   'legend_font' => array('size' => 7, 'font'=>$ttf_dir.'/FreeSansBold.ttf'),
		   'generic_font' => array('size' => 7, 'font'=>$ttf_dir.'/FreeSansBold.ttf'),
		   'x_label_font' => array('size' => 7, 'font'=>$ttf_dir.'/FreeSans.ttf'),
		   'y_label_font' => array('size' => 7, 'font'=>$ttf_dir.'/FreeSans.ttf'),
		   'x_title_font' => array('size' => 10, 'font'=>$ttf_dir.'/FreeSansBold.ttf'),
		   'y_title_font' => array('size' => 10, 'font'=>$ttf_dir.'/FreeSansBold.ttf')
		   );
    foreach ($fonts as $object=>$fontdata) {
      $graph2->$object = $fontdata;
    }
    $graph2->use_ttf = TRUE;

    //Draw it
    $graph2->DrawGraph();

  }else if($_GET['plot']=="piecharts"){

    $query_categories=mysql_query("SELECT id FROM webfinance_categories ORDER BY name") or die(mysql_error());

    $data_positive=array();
    $data_negative=array();

    $nb_categories=mysql_num_rows($query_categories);

    $tmp_categ=array();

    $query_sum_category=mysql_query("SELECT SUM(amount) as sum FROM webfinance_transactions WHERE  id_category<1 AND amount<0 ")
      or die(mysql_error());
    $res=mysql_fetch_assoc($query_sum_category);
    if($res['sum']!=0)
      $tmp_categ[0][]=array('unknown',$res['sum'],'peru');

    $query_sum_category=mysql_query("SELECT SUM(amount) as sum FROM webfinance_transactions WHERE  id_category<1 AND amount>0 ")
      or die(mysql_error());
    $res=mysql_fetch_assoc($query_sum_category);
    if($res['sum']!=0)
      $tmp_categ[1][]=array('unknown',$res['sum'],'peru');

    while($category=mysql_fetch_assoc($query_categories)){
      $query_sum_category=mysql_query("SELECT SUM(amount) as sum , webfinance_categories.name as name, webfinance_categories.color as color ".
				      "FROM webfinance_categories LEFT JOIN webfinance_transactions ON webfinance_categories.id=webfinance_transactions.id_category ".
				      "WHERE id_category=".$category['id']." ".
				      "GROUP BY webfinance_transactions.id_category ")
	or die(mysql_error());
      $res=mysql_fetch_assoc($query_sum_category);
      if(!empty($res['sum']) AND $res['sum']<0){
	$tmp_categ[0][]=array(utf8_decode($res['name']),$res['sum'],$res['color']);
      }else if(!empty($res['sum']) AND $res['sum']>0){
	$tmp_categ[1][]=array(utf8_decode($res['name']),$res['sum'],$res['color']);
      }
    }

    $plot =& new PHPlot(900,450);
    $plot->SetDataType('text-data');
    $plot->SetPlotType('pie');


      $plot->SetLabelScalePosition(0.37);

      $colors=array();

      if(isset($_GET['sign']) AND $_GET['sign']=="negative" AND isset($tmp_categ[0])){

	usort($tmp_categ[0],"cmp_data");

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

	$plot->SetDataValues($data_negative);
	$plot->SetTitle(utf8_decode(_("Outgo by category / all history")));
	$legend=array();
	foreach ($data_negative as $row){
	  $sum=array_sum($row);
	  $legend[]=$row[0]." : ".sprintf("%01.2f", $sum);
	}
	//color
	if(isset($tmp_categ[0]))
	  foreach($tmp_categ[0] as $row)
	    $colors[]=$row[2];

	$plot->SetDataColors($colors);
	$plot->SetLegend($legend);


      }else if(isset($tmp_categ[1])){

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

	$plot->SetDataValues($data_positive);
	$plot->SetTitle(utf8_decode(_("Income by category / all history")));
	$legend=array();
	//legend
	foreach ($data_positive as $row){
	  $sum=array_sum($row);
	  $legend[]=$row[0]." : ".sprintf("%01.2f", $sum);
	}
	//color
	if(isset($tmp_categ[1]))
	  foreach($tmp_categ[1] as $row)
	    $colors[]=$row[2];

	$plot->SetDataColors($colors);
	$plot->SetLegend($legend);

      }else{
	if(isset($_GET['sign']) AND $_GET['sign']=="negative")
	  $plot->SetTitle(utf8_decode(_("Outgo by category / all history")));
	else
	  $plot->SetTitle(utf8_decode(_("Income by category / all history")));
	$data=array(array('',100));
	$plot->SetDataValues($data);
	$plot->SetDataColors(array('white'));
	$plot->SetLegend(array(_('Nothing')));
    }

    $fonts = array(
		   'title_font' => array('size' => 13, 'font'=>$GLOBALS['_SERVER']['DOCUMENT_ROOT']."/css/themes/".$User->prefs->theme."/buttonfont.ttf"),
		   'legend_font' => array('size' => 7, 'font'=>$ttf_dir.'/FreeSansBold.ttf'),
		   'generic_font' => array('size' => 7, 'font'=>$ttf_dir.'/FreeSansBold.ttf'),
		   'x_label_font' => array('size' => 7, 'font'=>$ttf_dir.'/FreeSans.ttf'),
		   'y_label_font' => array('size' => 7, 'font'=>$ttf_dir.'/FreeSans.ttf'),
		   'x_title_font' => array('size' => 10, 'font'=>$ttf_dir.'/FreeSansBold.ttf'),
		   'y_title_font' => array('size' => 10, 'font'=>$ttf_dir.'/FreeSansBold.ttf')
		   );
    foreach ($fonts as $object=>$fontdata) {
      $plot->$object = $fontdata;
    }

    $plot->use_ttf = TRUE;

    $plot->DrawGraph();

  }

 }


?>
