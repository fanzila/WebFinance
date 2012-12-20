<?php
/*
   This file is part of Webfinance.

    Webfinance is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    Webfinance is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Webfinance; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
<?php
require("../inc/main.php");
//require_once("/usr/share/phplot/phplot.php");
require_once("phplot.php"); //<- this is the newest phplot than the debian package (stackedbars support)

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
    while ($tmp_max > 100) {
      $tmp_max /= 100;
      $exp++;
    }
    $tmp_max = ceil($tmp_max);
    while ($exp > 0){
      $tmp_max *= 100;
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
    while ($tmp_min > 100) {
      $tmp_min /= 100;
      $exp++;
    }
    $tmp_min = ceil($tmp_min);
    while ($exp > 0) {
      $tmp_min *= 100;
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
if(isset($_GET['type']) AND isset($_GET['sign']) AND isset($_GET['plot']) AND $_GET['type']=="category"){

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
	    $end_date= date("Y-m-d",$max);
	    $start_date=date("Y-m-d",$min);
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

	  if($max>$min){

	    $start_ex=explode("-",$start_date);
	    $end_ex=explode("-",$end_date);

	    $begin_date=date("Y-m-d" , mktime(0, 0, 0, $start_ex[1],1, $start_ex[0]));
	    $end_date=date("Y-m-d" , mktime(0, 0, 0, $end_ex[1],0, $end_ex[0]));

	    $nb_day= ceil((mktime(0, 0, 0, $end_ex[1],0, $end_ex[0]) - mktime(0, 0, 0, $start_ex[1],1, $start_ex[0])) / 86400);
	    $nb_month= ceil($nb_day/30);

	    $min_outgo=0;
	    $max_income=0;

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
	  $graph2=& new PHPlot(900,400);
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
	    //Set titles
	    $title="Income by category / all history";

	    $graph2->SetYTitle('Amount (Euro)');

	    $graph2->SetYTickIncrement( round($max_income/10,-3) );

	    foreach($categ_income as $category){
	      $legends[]=$category['name'];
	      $colors[]=$category['color'];
	    }
	    $legends[]="prev,asap";
	    $colors[]="orchid";

	    $legends[]="unknown";
	    $colors[]="peru";

	    //print_r($data_income);
	    $data=$data_income;

	    $graph2->SetDataValues($data);

	    $graph2->SetPlotAreaWorld(null, null, null,getRoundMax($max_income));
	    //$graph2->plot_min_y = getRoundMin($min);
	    //$graph2->plot_max_y = getRoundMax($max_income);


	  }else{
	    //Set titles
	    $title="Outgo by category / all history";

	    $graph2->SetYTitle('Amount (-Euro)');

	    $graph2->SetYTickIncrement( round($min_outgo/-10,-3) );

	    foreach($categ_outgo as $category){
	      $legends[]=$category['name'];
	      $colors[]=$category['color'];
	    }

	    $legends[]="prev,asap";
	    $colors[]="orchid";

	    $legends[]="unknown";
	    $colors[]="peru";

	    //echo "<pre/>";
	    //print_r($data_outgo);
	    $data=$data_outgo;

	    $graph2->SetDataValues($data);


	  }

			$graph2->SetTitle($title);
			$graph2->SetXTitle('');
			//$graph2->SetNumXTicks($nb_day);

			$graph2->SetXTickLabelPos('none');
			$graph2->SetXTickPos('none');

			//Legende
			$graph2->SetLegend($legends);

			//$graph2->SetXAxisPosition(0);
			$graph2->SetXLabelAngle(90);

			$graph2->SetDataColors($colors);

			$graph2->SetDataType("text-data");


			//$graph2->SetPlotType("lines");
			$graph2->SetPlotType("stackedbars");
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
					$tmp_categ[0][]=array($res['name'],$res['sum'],$res['color']);
				}else if(!empty($res['sum']) AND $res['sum']>0){
					$tmp_categ[1][]=array($res['name'],$res['sum'],$res['color']);
				}
			}
//			echo "<pre/>";
//			print_r($tmp_categ);

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

			$plot =& new PHPlot(900,450);
			//$plot->SetImageBorderType('plain');
			//$plot->SetShading(0);

			/*
			 * $plot->SetPlotAreaPixels($x1, $y1, $x2, $y2)
			 * $plot->SetPlotAreaWorld([$xmin], [$ymin], [$xmax], [$ymax])
			 */
			//$plot->SetPlotAreaPixels(1, 40, 40 ,300 );
			//$plot->SetPlotAreaWorld(10, 40, 40 ,300);


			/*
			 * $plot->SetLegendPixels($x, $y)
			 */
			//$plot->SetLegendPixels(830, 40);


			$plot->SetDataType('text-data');
			$plot->SetPlotType('pie');

			$plot->SetLabelScalePosition(0.37);

			$colors=array();

			if(isset($_GET['sign']) AND $_GET['sign']=="negative"){
				$plot->SetDataValues($data_negative);
				$plot->SetTitle("Outgo by category / all history");
				$legend=array();
				foreach ($data_negative as $row){
						$sum=array_sum($row);
						$legend[]=$row[0]." : ".sprintf("%01.2f", $sum);
				}
				//color
				foreach($tmp_categ[0] as $row)
					$colors[]=$row[2];
			}else{
				$plot->SetDataValues($data_positive);
				$plot->SetTitle("Income by category / all history");
				$legend=array();
				//legend
				foreach ($data_positive as $row){
						$sum=array_sum($row);
						$legend[]=$row[0]." : ".sprintf("%01.2f", $sum);
				}
				//color
				foreach($tmp_categ[1] as $row)
					$colors[]=$row[2];

			}
			$plot->SetDataColors($colors);
			$plot->SetLegend($legend);
			$plot->DrawGraph();

	}

}


?>
