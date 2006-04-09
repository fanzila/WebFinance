<?php
require("../inc/main.php");

$title="transactions";

if(1){ // FIXME


	$vars=array(
	"id"=>_("Id"),
	"type"=> _("Type"),
	"date"=>_("Date"),
	"text"=>_("Name"),
	"amount"=>_("Amount"),
	"comment"=>_("Comment"),
	"document"=>_("Document"),
	"id_account"=>_("Account"),
	"id_category"=>_("Category"),
	"file"=>_("File")
	);

$types=array(
		"1"=>"real",
		"2"=>"prevision",
		"3"=>"asap"
		);

//display transactions;
 $show_op=true;

$step=50;
$pan=0;
$max=0;

if(isset($_GET['pan']) AND $_GET['pan']!='' AND $_GET['pan']>0)
	$pan=$_GET['pan'];
else if(isset($_POST['pan']) AND $_POST['pan']!='' AND $_POST['pan']>0)
	$pan=$_POST['pan'];

$begin=$pan*$step;

//show category color
 $show_color=1;

 if((isset($_GET['action']) AND !empty($_GET['action']) ) OR (isset($_POST['action']) AND !empty($_POST['action']))  ){
   if(isset($_GET['color']))
     $show_color=$_GET['color'];
   else if(isset($_POST['color']))
     $show_color=$_POST['color'];
   $show_color=$show_color*(-1);
 }else if(isset($_GET['color']))
   $show_color=$_GET['color'];
 else if(isset($_POST['color']))
   $show_color=$_POST['color'];

 $sh_col=$show_color*(-1);

//echo "<pre/>";
// echo "GET<br/>";
// print_r($_GET);

//echo "POST<br/>";
//print_r($_POST);

//echo "FILES<br/>";
//print_r($_FILES);

if( isset($_POST['action']) AND ($_POST['action']=='add' or $_POST['action']=='update') ){
  $errors=array();
  $error="";
  $text="";
  $upload=false;

	//checks
	if(isset($_POST['type']) AND $_POST['type']==3)
		$_POST['date']=date("Y-m-d",mktime(0,0,0,date("m"),date("d")+1,date("Y")));
	else if(isset($_POST['type']) AND $_POST['type']==1 AND isset($_POST['date']) AND check_date($_POST['date'])){
		$date_real=explode("-",$_POST['date']);
		if( mktime(0,0,0,$date_real[1],$date_real[2],$date_real[0])>mktime(23,59,59,date("m"),date("d"),date("Y"))  )
			$errors[]="A real transaction can't be in the future";
	}

	if($_POST['action']=='add') {
		foreach($vars as $var=>$display){
			if($var!="id" && $var!="file"){
				if(isset($_POST[$var])){
					$_POST[$var]=trim($_POST[$var]);
					$tmp=webfinance_transactions_check("add",$var,$display,$_POST[$var]);
					if(!empty($tmp))
						$errors[]=$tmp;
				}else
					$errors[]="Miss : <b>".$display."</b>";
			}
		}
		if(isset($_FILES['file'])){
			$upload=true;
			$ret= is_uploaded_file($_FILES['file']['tmp_name']);
			$img_size=$_FILES['file']['size'];
			$img_type=$_FILES['file']['type'];
			$img_name=$_FILES['file']['name'];
		}

		if(count($errors)==0){
			if(!get_magic_quotes_gpc()){
				$_POST['name']=addslashes($_POST['name']);
			}
			$id_account=$_POST['id_account'];
			$id_category=$_POST['id_category'];
			$text=htmlentities($_POST['text'],ENT_COMPAT);
			$amount=$_POST['amount'];
			$id_type=$_POST['type'];
			$type=$types[$id_type];
			$document=$_POST['document'];
			$date=$_POST['date'];
			$comment=htmlentities($_POST['comment'],ENT_COMPAT);
			$file="";
			$file_type="";
			$file_name="";

			if($upload){
				$file_type=addslashes($_FILES['file']['type']);
				$file_name=addslashes($_FILES['file']['name']);
				$file_blob = file_get_contents($_FILES['file']['tmp_name']);
				$file=addslashes($file_blob);
				//$file=base64_encode($file_blob);
			}
			$query="INSERT INTO webfinance_transactions SET ".
			  "id_account=%s , ".
			  "id_category=%s , ".
			  "text='%s' , ".
			  "amount=%s , ".
			  "type='%s' , ".
			  "document='%s' , ".
			  "date='%s' , ".
			  "comment='%s', ".
			  "file='%s' , ".
			  "file_name='%s', ".
			  "file_type='%s'";

			mysql_query(sprintf($query,$id_account,$id_category,$text,$amount,$type,$document,$date,$comment,$file,$file_name,$file_type ))
			  or die (mysql_error());
			$text="transaction recorded.";

		}else{
			foreach($errors as $err)
				$error=$error."&errors[]=".$err;

			$gets="";
			foreach($vars as $var=>$display){
				if(isset($_POST[$var]))
					$gets=$gets."&".$var."=".$_POST[$var];
			}
			//echo $gets;
			header("Location: ".$_SERVER['SCRIPT_NAME']."?action=add&color=".$sh_col."".$error."".$gets );
			exit;

		}
	}

	if($_POST['action']=='update'){

		$id=$_POST['id'];
		$table_name="webfinance_transactions";
		$text_if_success="transaction updated";
		$_POST['user']=0;
		$result=mysql_query("SELECT id,type FROM ".$table_name." where id=".$id)
		  or die (mysql_error());
		$nb=mysql_num_rows($result);
		if($nb!=1)
			$errors[]="No valid Id";

		foreach($vars as $var=>$display){
			if($var!="file"){
				if(isset($_POST[$var])){
					$tmp=webfinance_transactions_check("update",$var,$display,$_POST[$var]);
					if(!empty($tmp))
						$errors[]=$tmp;
				}else
					$errors[]="Miss : <b>".$display."</b>";
			}
		}
		if( (isset($_FILES['file']) AND $_FILES['file']['size']>0) OR !isset($_POST['file_del']))
			$upload=true;

		if(count($errors)==0){
			if(!get_magic_quotes_gpc()){
				$_POST['name']=addslashes($_POST['name']);
			}
			$id_account=$_POST['id_account'];
			$id_category=$_POST['id_category'];
			$text=htmlentities($_POST['text'],ENT_COMPAT);
			$amount=$_POST['amount'];
			$id_type=$_POST['type'];
			$type=$types[$id_type];
			$document=$_POST['document'];
			$date=$_POST['date'];
			$comment=htmlentities($_POST['comment'],ENT_COMPAT);
			$file="";
			$file_type="";
			$file_name="";


			if($upload){

				$file_type=addslashes($_FILES['file']['type']);
				$file_name=addslashes($_FILES['file']['name']);
				$file_blob = file_get_contents($_FILES['file']['tmp_name']);
				$file=addslashes($file_blob);
				//$file=base64_encode($file_blob);

				$query="UPDATE webfinance_transactions SET ".
				  "id_account=%s , ".
				  "id_category=%s , ".
				  "text='%s' , ".
				  "amount=%s , ".
				  "type='%s' , ".
				  "document='%s' , ".
				  "date='%s' , ".
				  "comment='%s', ".
				  "file='%s' , ".
				  "file_name='%s', ".
				  "file_type='%s' ".
				  "WHERE id=%s";

				mysql_query(sprintf($query,$id_account,$id_category, $text ,$amount,$type,$document , $date, $comment, $file,$file_name, $file_type, $id))
				  or die (mysql_error());

			}else{
				$query="UPDATE webfinance_transactions SET ".
				  "id_account=%s , ".
				  "id_category=%s , ".
				  "text='%s' , ".
				  "amount=%s , ".
				  "type='%s' , ".
				  "document='%s' , ".
				  "date='%s' , ".
				  "comment='%s' ".
				  "WHERE id=%s";

				mysql_query(sprintf($query,$id_account,$id_category, $text ,$amount,$type,$document , $date, $comment, $id))
				  or die (mysql_error());
			}

			$text="transaction updated.";
		}else{
			$gets="";
			foreach($vars as $var=>$display){
				if(isset($_POST[$var]))
					$gets=$gets."&".$var."=".$_POST[$var];
			}
			foreach($errors as $err)
				$error=$error."&errors[]=".$err;
			header("Location: ".$_SERVER['SCRIPT_NAME']."?action=edit&chk[]=".$_POST['id']."&color=".$sh_col."&pan=".$pan."".$error."".$gets );
			exit;
		}

	}


	foreach($errors as $err)
		$error=$error."&errors[]=".$err;

	header("Location: ".$_SERVER['SCRIPT_NAME']."?color=".$show_color."&pan=".$pan."".$error );
	exit;

}

if( isset($_GET['action'])  AND  ( $_GET['action']=='delete2' or $_GET['action']=='delete' or $_GET['action']=='update') )  {
  $errors=array();
  $error="";
  $text="";

  if($_GET['action']=='delete2'){

    $id=$_GET['id'];
    $table_name="webfinance_transactions";
    $text_if_success="transaction deleted";

    $query=mysql_query("SELECT id FROM ".$table_name." where id=".$id) or die (mysql_error());
    $nb=mysql_num_rows($query);
    if($nb!=1)
      $errors[]="No valid Id";
    if(count($errors)==0){
      mysql_query("DELETE FROM ".$table_name." WHERE id=".$id);
      $text=$text_if_success;
    }
  }

	if($_GET['action']=='delete' AND isset($_GET['chk'])){

		$table_name="webfinance_transactions";
		$text_if_success="transaction deleted";

		$x=0;
		foreach($_GET['chk'] as $id){
			$query=mysql_query("SELECT id FROM ".$table_name." where id=".$id) or die (mysql_error());
			$nb=mysql_num_rows($query);
			if($nb!=1)
				$errors[]="No valid Id";
			if(count($errors)==0){
				$x++;
				mysql_query("DELETE FROM ".$table_name." WHERE id=".$id);
				$text=$text_if_success." (".$x.")";
			}
		}
	}

	if($_GET['action']=='update'){

		$table_name="webfinance_transactions";
		$text_if_success="transaction updated";

//		$accounts=array();
//		foreach($_GET['acc'] as $account)
//			$accounts[]=$account;

		$categories=array();
		foreach($_GET['cat'] as $category)
			$categories[]=$category;

		$comments=array();
		foreach($_GET['com'] as $comment)
			$comments[]=$comment;

//		$type_array=array();
//		foreach($_GET['typ'] as $type)
//			$type_array[]=$type;

		$x=0;
		foreach($_GET['id_ope'] as $id){
			$result=mysql_query("SELECT id FROM ".$table_name." where id=".$id) or die (mysql_error());
			$nb=mysql_num_rows($result);
			if($nb!=1)
				$errors[]="No valid Id";

			if(count($errors)==0){
//				$id_account=$accounts[$x];
				$id_category=$categories[$x];
				$comment=htmlentities($comments[$x],ENT_COMPAT);
//				$id_type=$type_array[$x];
//				$type=$types[$id_type];

//				$query="UPDATE webfinance_transactions SET type='".$type."', comment='".$comment."' , id_account=".$id_account;
				$query="UPDATE webfinance_transactions SET comment='".$comment."' " ;

				if(!empty($id_category))
					$query=$query." , id_category=".$id_category;
				else
					$query=$query." , id_category=0";

				$query.=" WHERE id=".$id;
				mysql_query($query) or die(mysql_error());

				$text="transactions updated";
			}
			$x++;
		}

	}

	foreach($errors as $err)
		$error=$error."&errors[]=".$err;

	$search="";
	if(isset($_GET['search'])){
		$search="&action=";
		foreach($_GET['search'] as $key => $value)
			$search=$search."&search[".$key."]=".$value;
		//echo $search;
	}

	header("Location: ".$_SERVER['SCRIPT_NAME']."?color=".$show_color."&pan=".$pan."".$error."".$search);
	exit;

}

require("../top.php");
require("nav.php");

 $req=mysql_query('SELECT id, amount FROM webfinance_transactions ORDER BY date')
   or die(mysql_error());
 $balance_yesterday=0;
 $balance=array();
 while ($row=mysql_fetch_assoc($req)) {
   $balance[$row['id']]=$balance_yesterday+$row['amount'];
   $balance_yesterday+=$row['amount'];

   //   echo "id=".$row['id']." amount=".$row['amount']." balance=".$balance[$row['id']]."<br>";
 }
 // exit;
?>
<hr/>

<?
if(isset($_GET['display']) AND !empty($_GET['display'])){
?>
<font color="green" size="4" face="verdana"><?=$_GET['display']?></font><br>
	<?
}
if(isset($_GET['errors'])){
	foreach($_GET['errors'] as $error){
		?>
		<font color="red" size="4" face="verdana"><?=$error?></font><br>
		<?
	}
}


if(isset($_GET['action']) AND $_GET['action']=='edit' AND !isset($_GET['chk']) ){
	?>
		<font color="red" size="4" face="verdana">Choose an transaction before edit</font><br/><br/>
	<?
}else if(isset($_GET['action']) AND $_GET['action']=='edit' AND isset($_GET['chk']) AND count($_GET['chk'])>0 ){
//--------------------------------- EDIT ----------------------------------------

	if($_GET['action']=="edit"  ) {

	  //don't show the transactions
	  $show_op=false;

	  $tmp=$_GET['chk'];
	  $_GET['id']=$tmp[0];
	  if(check_unsigned_int($_GET['id'])){
	    mysql_select_db('webcash',$db);
	    $query_transaction=mysql_query("SELECT * FROM webfinance_transactions where id=".$_GET['id'])
	      or die (mysql_error());
	    $nb=mysql_num_rows($query_transaction);
	    if($nb==1){
	      $transaction=mysql_fetch_assoc($query_transaction);
	      $result_accounts=mysql_query("SELECT * FROM webfinance_accounts") or die(mysql_error());
	      if(mysql_num_rows($result_accounts)<1){
		echo "<font color='red'><a href='accounts'>Please, add an account!</a></font>";
	      }
	      $result_categories=mysql_query("SELECT * FROM webfinance_categories") or die(mysql_error());
	      if(mysql_num_rows($result_categories)<1){
		echo "<font color='red'><a href='categories'>Please, add a category!</a></font>";
	      }

				?>
				<font color="red" size="4" face="verdana">Edit transaction</font><br>
<form enctype="multipart/form-data" method="post">
	<table class="text" bgcolor="#E6E2E6" style="text-align: left; " border="0" cellpadding="2" cellspacing="4">
		<tr>
			<th><?=$vars['id_account']?> :</th>
			<td>
				<?

				if(mysql_num_rows($result_accounts)>0){
				?>
						<select class="form" name="id_account">
				<?
						while ($account=mysql_fetch_assoc($result_accounts)) {
				?>
							<option value="<?=$account['id']?>" <? if($transaction['id_account']==$account['id']) { echo "selected"; } ?>><?=$account['account_name']?></option>
				<?
				}
						?>
				</select>
		<?
					}else{
				?>
					<font color='red'><a href='accounts'>First add an account!</a></font>
				<?
					}
		?>
			</td>
		</tr>
		<tr>
			<th><?=$vars['id_category']?> :</th>
			<td>
				<?
				if(mysql_num_rows($result_categories)>0){
				?>
						<select class="form" name="id_category">
							<option value="0" <? if(empty($_GET['id_category'])) { echo "selected"; } ?>><?= _("-- Choose --") ?></option>
				<?
						while ($categorie=mysql_fetch_assoc($result_categories)) {
				?>
							<option value="<?=$categorie['id']?>" <? if($transaction['id_category']==$categorie['id']) { echo "selected"; } ?>><?=$categorie['name']?></option>
				<?
				}
						?>
				</select>
		<?
					}else{
				?>
					<font color='red'><a href='categories'>Add a category</a></font>
				<?
					}
		?>
			</td>
		</tr>
		<tr>
			<th><?=$vars['text']?> :</th>
			<td>
			<input type="text" name="text" class="form" value="<?=$transaction['text']?>" size="50" maxlength="250"/>
			</td>
		</tr>
		<tr>
			<th><?=$vars['amount']?>:</th>
			<td>
			<input type="text" name="amount" class="form" value="<?=$transaction['amount']?>" size="20" maxlength="50"/>
			</td>
		</tr>
		<tr>
				<th><?=$vars['type']?></th>
				<td>
					<select name="type" class="form">
							<?
								foreach($types as $id_type => $type){
							?>
							<option value="<? echo $id_type; ?>" <? 	if($transaction['type']==$type) 	echo "selected"; ?> 	>
						<? echo $type; ?>
				</option	>
							<?
								}
						?>
			</select>
				</td>
		</tr>
		<tr>
				<th><?=$vars['document']?></th>
				<td>
					<input type="text" name="document" class="form" value="<?=$transaction['document']?>" size="40" maxlength="50"/>
				</td>
		</tr>

	<tr>
		<th><?=$vars['date']?></th>
		<td>
			<input type="text" name="date" class="form" value="<?=$transaction['date']?>" size="10" maxlength="10"/>
		</td>
	</tr>
	<tr>
			<th><?=$vars['comment']?></th>
			<td>
				<textarea cols="40" rows="4" class="form" name="comment"><?=$transaction['comment']?></textarea>
			</td>
		</tr>
		<tr>
				<th><?=$vars['file']?></th>
				<td>
			    		<?
					if(strlen($transaction['file_name'])>0)
						echo "<input checked='checked' name='file_del' value='1' type='checkbox'' /><a href='file?action=file&id=".$transaction['id']."'>".$transaction['file_name']."</a><br/>";

					?>
					<input type="hidden" name="MAX_FILE_SIZE" value="5000000" />
					<input type="file" name="file" size="30" class="form" value="<?=$transaction['file_name']?>" />
				</td>
						</tr>
						<tr>
					   <td>
					   <a href="?pan=<?=$pan?>">Back</a>
					   </td>
					    <td rowspan="1" align="center">
					    <input type="hidden" name="id" value="<?=$transaction['id']?>"/>
					    <input type="hidden" name="pan" value="<?$pan?>"/>
 					    <input type="hidden" name="action" value="update"/>
					    <input type="hidden" name="chk" value="<?=$_GET['chk']?>"/>
								<input type="submit" class="form" value="Update"/>
								</td>
						</tr>
					 </table>
				</form>
				<?
			}else if($nb>1){
				?>
				<font color="red" size="4" face="verdana">Duplicate id !! </font><br>
				<?
			}else{
				?>
				<font color="red" size="4" face="verdana">The bank doesn't exist in the db</font><br>
				<?
			}

		}else{
			?>
				<font color="red" size="4" face="verdana">Wrong id</font><br>
			<?
		}

	}
}

//-------------------------------- end EDIT -----------------------------------

 if(isset($_GET['action']) AND $_GET['action']=="add"){
  //don't show the transactions
  $show_op=false;

//--------------------------------- ADD ----------------------------------------

	$result_accounts=mysql_query("SELECT * FROM webfinance_accounts") or die(mysql_error());
	if(mysql_num_rows($result_accounts)<1){
		echo "<font color='red'><a href='accounts'>Please, add an account!</a></font>";
	}
	$result_categories=mysql_query("SELECT id, name  FROM webfinance_categories ORDER BY name") or die(mysql_error());
	if(mysql_num_rows($result_categories)<1){
		echo "<font color='red'><a href='categories'>Please, add a category!</a></font>";
	}

?>

<form enctype="multipart/form-data" method="post">
	<table class="text" bgcolor="#E6E2E6" style="text-align: left; " border="0" cellpadding="2" cellspacing="4">
		<tr>
			<th><?=$vars['id_account']?> :</th>
			<td>
				<?
				if(mysql_num_rows($result_accounts)>0){
				?>
						<select name="id_account" class="form">
							<option value="" <? if(empty($_GET['id_account'])) { echo "selected"; } ?>><?= _("-- Choose --") ?></option>
				<?
						while ($account=mysql_fetch_assoc($result_accounts)) {
				?>
							<option value="<?=$account['id']?>" <? if(!empty($_GET['id_account']) AND $_GET['id_account']==$account['id']) { echo "selected"; } ?>><?=$account['account_name']?></option>
				<?
				}
						?>
				</select>
		<?
					}else{
				?>
					<font color='red'><a href='accounts'>First add an account!</a></font>
				<?
					}
		?>
			</td>
		</tr>
		<tr>
			<th><?=$vars['id_category']?> :</th>
			<td>
				<?
				if(mysql_num_rows($result_categories)>0){
				?>
						<select name="id_category" class="form">
							<option value="0" <? if(empty($_GET['id_category'])) { echo "selected"; } ?>><?= _("-- Choose --") ?></option>
				<?
						while ($categorie=mysql_fetch_assoc($result_categories)) {
				?>
							<option value="<?=$categorie['id']?>" <? if(!empty($_GET['id_category']) AND $_GET['id_category']==$categorie['id']) { echo "selected"; } ?>><?=$categorie['name']?></option>
				<?
				}
						?>
				</select>
		<?
					}else{
				?>
					<font color='red'><a href='categories'>Add a category</a></font>
				<?
					}
		?>
			</td>
		</tr>
		<tr>
			<th><?=$vars['text']?> :</th>
			<td>
			<input type="text" class="form" name="text" value="<?if(isset($_GET['text']) AND !empty($_GET['text'])) echo $_GET['text']; else echo "";?>" size="50" maxlength="250"/>
			</td>
		</tr>
		<tr>
			<th><?=$vars['amount']?>:</th>
			<td>
			<input type="text" class="form" name="amount" value="<?if(isset($_POST['amount']) AND !empty($_POST['amount'])) echo $_POST['amount']; else echo "0";?>" size="20" maxlength="50"/>
			</td>
		</tr>
		<tr>
				<th><?=$vars['type']?></th>
				<td>
					<select name="type" class="form">
							<?
								foreach($types as $id_type => $type){
							?>
							<option value="<? echo $id_type; ?>" <? 	if(!empty($_POST['type']) && (  $_POST['type']=="1" || $_POST['type']=="2") ) if($_POST['type']==$id_type) 	echo "selected"; ?> 	>
						<? echo $type; ?>
				</option	>
							<?
								}
						?>
			</select>
				</td>
		</tr>
		<tr>
				<th><?=$vars['document']?></th>
				<td>
					<input type="text" name="document" class="form" value="<?if(isset($_POST['document']) AND !empty($_POST['document'])) echo $_POST['document']; else echo "";?>" size="40" maxlength="50"/>
				</td>
		</tr>

	<tr>
		<th><?=$vars['date']?></th>
		<td>
			<input type="text" name="date" class="form" value="<?if(isset($_POST['date']) AND !empty($_POST['date'])) echo $_POST['date']; else echo date("Y-m-d");?>" size="10" maxlength="10"/>
		</td>
	</tr>
	<tr>
			<th><?=$vars['comment']?></th>
			<td>
				<textarea cols="40" rows="4" class="form" name="comment"></textarea>
			</td>
		</tr>
		<tr>
				<th><?=$vars['file']?></th>
				<td>
					<input type="hidden" name="MAX_FILE_SIZE" value="5000000" />
					<input type="file" name="file" class="form" size="30" />
				</td>
		</tr>


		<tr>
			    <td>
			    <a href="?pan=<?=$pan?>">Back</a>
			    </td>
			    <td rowspan="1" align="center">
			    <input type="hidden" name="user" value="1"/>
			    <input type="hidden" name="action" value="add"/>
			    <input class="form" type="submit" value="Add this transaction"/>
			    </td>
		</tr>
	 </table>
</form>
<?

}
?>
<?
  //START SHOW TRANSACTIONS
  if($show_op){

//--------------------------------- FILTER ----------------------------------------

//text
$text="";
$have_text=false;

//comment
$comment="";
$have_comment=false;

//Account
$id_account="-1";
$have_account=false;

//category
$have_categorie=false;
$id_category="-1";

//type
$have_type=false;
$type="";

//date
$have_date=false;
$date_start="";
$date_end="";

//Amount
$have_amount=false;
$amount_min="";
$amount_max="";

$search_result="";

//if(isset($_GET['action']) AND $_GET['action']=="search" ){

if(isset($_GET['action']) AND isset($_GET['search']) ){

	//text
	if(isset($_GET['search']['text']) AND !empty($_GET['search']['text'])){
		$have_text=true;
		$text=$_GET['search']['text'];
	}

	//Comment
	if(isset($_GET['search']['comment']) AND !empty($_GET['search']['comment'])){
		$have_comment=true;
		$comment=$_GET['search']['comment'];
	}

	//Account
	if(isset($_GET['search']['id_account']) AND !empty($_GET['search']['id_account']) AND $_GET['search']['id_account']>0){
		$id_account=$_GET['search']['id_account'];
		$have_account=true;
	}

	//Category
	if(isset($_GET['search']['id_category']) AND $_GET['search']['id_category']!="" AND $_GET['search']['id_category']>=0){
		$id_category=$_GET['search']['id_category'];
		$have_categorie=true;
	}

	//Type
	if(isset($_GET['search']['type']) AND !empty($_GET['search']['type']) AND $_GET['search']['type']>0){
		$type=$_GET['search']['type'];
		$have_type=true;
	}

	//Amount
	if(isset($_GET['search']['amount_min']) AND isset($_GET['search']['amount_max']) AND is_numeric($_GET['search']['amount_min']) AND is_numeric($_GET['search']['amount_max']) ){
	  $have_amount=true;
	  $amount_min=$_GET['search']['amount_min'];
	  $amount_max=$_GET['search']['amount_max'];
	  if($amount_min>$amount_max)
	    list($amount_min,$amount_max)=array($amount_max,$amount_min);
	}

	//Date
	if(isset($_GET['search']['date_start']) AND isset($_GET['search']['date_end']) AND !empty($_GET['search']['date_start']) AND !empty($_GET['search']['date_end'])){
		if(strtotime($_GET['search']['date_start'])<=strtotime($_GET['search']['date_end'])){
			$have_date=true;
			$date_start=$_GET['search']['date_start'];
			$date_end=$_GET['search']['date_end'];
		}
	}

	//Form query
	$search_query="SELECT ".
	  "id, ".
	  "id_account, ".
	  "id_category, ".
	  "text, ".
	  "amount, ".
	  "type, ".
	  "document, ".
	  "date, ".
	  "date_update, ".
	  "comment, ".
	  "file_name ".
	  "FROM webfinance_transactions";

	if($have_text OR $have_comment OR $have_account OR $have_categorie OR $have_type OR $have_amount OR $have_date){

		$search_query=$search_query." WHERE" ;

		if($have_text){
			$search_query=$search_query." text LIKE '%".$text."%'";
		}

		if($have_comment){
			if(! preg_match('/WHERE$/i',$search_query))
				$search_query=$search_query." AND ";
			$search_query=$search_query." comment LIKE '%".$comment."%' ";
		}

		if($have_account){
			if(! preg_match('/WHERE$/i',$search_query))
				$search_query=$search_query." AND ";
			$search_query=$search_query." id_account=".$id_account;
		}
		if($have_categorie){
			if(! preg_match('/WHERE$/i',$search_query))
				$search_query=$search_query." AND ";
			$search_query=$search_query." id_category=".$id_category;
		}
		if($have_type){
			if(!preg_match('/WHERE$/i',$search_query))
				$search_query=$search_query." AND ";
			$search_query=$search_query." type LIKE '%".$types[$type]."%'";
		}
		if($have_amount){
			if(! preg_match('/WHERE$/i',$search_query))
				$search_query=$search_query." AND ";
			if($amount_max!=$amount_min)
				$search_query=$search_query." amount BETWEEN $amount_min AND $amount_max ";
			else
				$search_query=$search_query." amount=".$amount_min;
		}
		if($have_date){
			if(!preg_match('/WHERE$/i',$search_query))
				$search_query=$search_query." AND ";
			$search_query=$search_query." date BETWEEN '$date_start' AND '$date_end' ";
		}
	}


//	echo $search_query."<br/>";

	$search_result=mysql_query($search_query) or die(mysql_error());

	$field="DATE";
	$order="DESC";
	if(isset($_GET['sort']['field']) AND !empty($_GET['sort']['field']) ){
		$field=$_GET['sort']['field'];
	}
	if(isset($_GET['sort']['order']) AND !empty($_GET['sort']['order']) ){
		$order=$_GET['sort']['order'];
	}
	$search_result_display=mysql_query($search_query. " ORDER BY $field $order LIMIT $step OFFSET $begin") or die(mysql_error());

	$sum=0;
	while($tmp=mysql_fetch_assoc($search_result))
		$sum=$sum+$tmp['amount'];

	if(mysql_num_rows($search_result)==0)
		echo "<b>Sorry, no match to your query.</b>";
	else{
		$x=$begin+1;
		$y=$begin+mysql_num_rows($search_result_display);
		$max=mysql_num_rows($search_result);
		echo "<b>".$x." - ".$y." / ".$max." rows  SUM=".$sum."</b>";
	}
}


//echo "<hr/>";

$query_accounts=mysql_query("SELECT id, account_name FROM webfinance_accounts ORDER BY account_name") or die (mysql_error());
$nb_accounts=mysql_num_rows($query_accounts);

$query_categories=mysql_query("SELECT id, name FROM webfinance_categories ORDER BY name") or die (mysql_error());
$nb_categories=mysql_num_rows($query_categories);

?>
<form>
<table class="text" bgcolor="#EEF2C5" border="0" cellpadding="2" cellspacing="4">
	<tr>
		<td>
			Text :
				<input class="form" type="text" name="search[text]" value="<?=$text?>" size="35" maxlength="100"/>
		</td>
		<td>
			Comment :
				<input class="form" type="text" name="search[comment]" value="<?=$comment?>" size="20" maxlength="20"/>
		</td>
		<td>
		<?
			if($nb_accounts>0){
		?>
			<?=$vars['id_account']?>:
						<select class="form" name="search[id_account]">
							<?
							if($nb_accounts>1)
							?>
								<option value='-1' <? if(!$have_account) echo "selected"; ?>>
									All
								</option>
							<?
							while ($account=mysql_fetch_assoc($query_accounts)) {
					?>
								<option value="<?=$account['id']?>" <? if($id_account==$account['id']) echo "selected"; ?>>
									<?=$account['account_name']?>
								</option>
					<?
					}
							?>
				</select>

		<?
				}else{
		?>
						<font color='red'><a href='accounts'>Add an account!</a></font>
		<?
			}
		?>
		</td>
		<td>
		<?
				if($nb_categories>0){
		?>
			<?=$vars['id_category']?> :
						<select class="form" name="search[id_category]">
							<option value='-1' <? if(!$have_categorie) echo "selected"; ?>><?= _("-- Choose --") ?></option>
							<?
							if($nb_categories>1){
							?>
								<option value='-1' <? if(!$have_categorie) echo "selected"; ?>>
									All
								</option>
								<option value='0' <? if($id_category==0) echo "selected"; ?>>
									Unknown
								</option>
							<?
									  }
							?>
					<?
						while($categorie=mysql_fetch_assoc($query_categories)) {
					?>
							  <option value="<?=$categorie['id']?>" <? if($id_category==$categorie['id']) echo "selected"; ?>>
							    <?=$categorie['name']?>
							  </option>
					<?
						}
					?>
				</select>
		<?
				}else{
		?>
						<font color='red'><a href='accounts'>Add an account!</a></font>
		<?
			}
		?>
		</td>
<!--
		<td colspan="1" rowspan="2">
			File :
			<input type="checkbox" name="file" value="1"/>
				</td>
-->
		<td colspan="1" rowspan="2">
			<input type="hidden" name="action" value="search"/>
		        <input type="hidden" name="color" value="<?=$sh_col?>"/>
					<input type="submit" class="form"	value="Search"/>
				</td>

	</tr>
	<tr>
		<td>
			Amount :
				<input type="text" class="form" name="search[amount_min]" value="<? if($amount_min!="") echo $amount_min; ?>" size="10" maxlength="17"/>
					<small>between</small>
					<input type="text" class="form" name="search[amount_max]" value="<? if($amount_max!="") echo $amount_max; ?>" size="10" maxlength="17"/>
		</td>
		<td>
			Date :
					<input type="text" class="form" name="search[date_start]" value="<? if($date_start!="") echo $date_start; ?>" size="10" maxlength="10"/>
					<small>to</small>
					<input type="text" class="form" name="search[date_end]" value="<? if($date_end!="") echo $date_end; ?>" size="10" maxlength="10"/>
		</td>
			<td>
				Type :
					<select name="search[type]" class="form">
						<?
					if(count($types)>1){
					?>
						<option value='-1' <? if(!$have_type) echo "selected"; ?>>
								All
						</option>
					<?
					}
					?>
					<?
				foreach($types as $id_type => $type_name){
						?>
							<option value="<? echo $id_type; ?>"	 <?if($id_type==$type) echo "selected"; ?>>
						<? echo $type_name; ?>
				</option	>
						<?
							}
					?>
			</select>
				</td>
	</tr>
</table>
</form>
<!-------------------------------- DISPLAY ---------------------------------------->
<?


echo "<hr/>";

if(empty($search_result)){
	$field="DATE";
	$order="DESC";
	if(isset($_GET['sort']['field']) AND !empty($_GET['sort']['field']) ){
		$field=$_GET['sort']['field'];
	}
	if(isset($_GET['sort']['order']) AND !empty($_GET['sort']['order']) ){
		$order=$_GET['sort']['order'];
	}
	$query_transactions=mysql_query("SELECT ".
				      "id, ".
				      "id_account, ".
				      "id_category, ".
				      "text, ".
				      "amount, ".
				      "type, ".
				      "document, ".
				      "date, ".
				      "date_update, ".
				      "comment, ".
				      "file_name ".
				      "FROM webfinance_transactions ".
				      "ORDER BY $field $order ".
				      "LIMIT $step OFFSET $begin")
	  or die (mysql_error());
	$query_transactions_all=mysql_query("SELECT COUNT(*) FROM webfinance_transactions") or die (mysql_error());
	list($max)=mysql_fetch_row($query_transactions_all);
}else
	$query_transactions=$search_result_display;





//echo $text." : ".$comment." : ".$id_account." : ".$id_category." : ".$amount_min." : ".$amount_max." : ".$date_start." : ".$date_end." : ".$type ;

if($max>0){

	$field="DATE";
	$order="DESC";
	$order_value=$order;

	if(isset($_GET['sort']['order']) AND !empty($_GET['sort']['order']) ){
		$order_value=$_GET['sort']['order'];
		if($order_value=="DESC")
			$order="ASC";
		else
			$order="DESC";
	}
	if(isset($_GET['sort']['field']) AND !empty($_GET['sort']['field']) ){
		$field=$_GET['sort']['field'];
	}


?>

<center>
	<table class="text" bgcolor="#E6E2E6" border="0" cellpadding="2" cellspacing="4" width="100%">
		<tr>
			<td align="center">
			<?
				if($pan>1){
				  if($pan==2)
				    $last_pan=0;
				  else
				    $last_pan=$pan-1;
					?>
						<a href='?pan=<?=$last_pan?>
							&search[text]=<?=$text?>
							&search[comment]=<?=$comment?>
							&search[id_account]=<?=$id_account?>
							&search[id_category]=<?=$id_category?>
							&search[amount_min]=<? if($amount_min!="") echo $amount_min; ?>
							&search[amount_max]=<? if($amount_max!="") echo $amount_max; ?>
							&search[date_start]=<? if($date_start!="") echo $date_start; ?>
							&search[date_end]=<? if($date_end!="") echo $date_end; ?>"
							&search[type]=<? echo $type; ?>
							&sort[field]=<?=$field?>&sort[order]=<?=$order_value?>
							&color=<?=$show_color?>
							&action=
						'>[<<]</a>
					<?
				}
				$nb_pan=floor($max/$step);
				for ($i = 1 ; $i <= $nb_pan ; $i++) {
				  $x=$i;
				  if($i==1 AND $nb_pan>1 AND $pan==0)
				    echo "[1]";
				  else if($i!=$pan){
				    if($i==1)
						$x=0;
					?>
					<a href='?pan=<?=$x?>
							&search[text]=<?=$text?>
							&search[comment]=<?=$comment?>
							&search[id_account]=<?=$id_account?>
							&search[id_category]=<?=$id_category?>
							&search[amount_min]=<? if($amount_min!="") echo $amount_min; ?>
							&search[amount_max]=<? if($amount_max!="") echo $amount_max; ?>
							&search[date_start]=<? if($date_start!="") echo $date_start; ?>
							&search[date_end]=<? if($date_end!="") echo $date_end; ?>"
							&search[type]=<? echo $type; ?>
							&sort[field]=<?=$field?>&sort[order]=<?=$order_value?>
							&color=<?=$show_color?>
							&action=
						'><?=$i?></a>
					<?
					}else
						echo "[".$i."]";
				}
				if($pan==0)
				    $next_pan=2;
				 else
				    $next_pan=$pan+1;

				$tmpx=($next_pan)*$step;
				if($max>$tmpx){
			?>
					<a href='?pan=<?=$next_pan?>
						&search[text]=<?=$text?>
						&search[comment]=<?=$comment?>
						&search[id_account]=<?=$id_account?>
						&search[id_category]=<?=$id_category?>
						&search[amount_min]=<? if($amount_min!="") echo $amount_min; ?>
						&search[amount_max]=<? if($amount_max!="") echo $amount_max; ?>
						&search[date_start]=<? if($date_start!="") echo $date_start; ?>
						&search[date_end]=<? if($date_end!="") echo $date_end; ?>
						&search[type]=<? echo $type; ?>
						&sort[field]=<?=$field?>&sort[order]=<?=$order_value?>
						&color=<?=$show_color?>
						&action=
					'>[>>]</a>

			<?
				}
			?>
			</td>
		</tr>
	</table>
</center>


<form id="main_form">
	<!--Filter params-->
	<input type="hidden" name="search[text]" value="<?=$text?>"/>
	<input type="hidden" name="search[comment]" value="<?=$comment?>"/>
	<input type="hidden" name="search[id_account]" value="<?=$id_account?>"/>
	<input type="hidden" name="search[id_category]" value="<?=$id_category?>"/>
	<input type="hidden" name="search[amount_min]" value="<? if($amount_min!="") echo $amount_min; ?>"/>
	<input type="hidden" name="search[amount_max]" value="<? if($amount_max!="") echo $amount_max; ?>"/>
	<input type="hidden" name="search[date_start]" value="<? if($date_start!="") echo $date_start; ?>"/>
	<input type="hidden" name="search[date_end]" value="<? if($date_end!="") echo $date_end; ?>"/>
	<input type="hidden" name="search[type]" value="<? echo $type; ?>"/>
<?
	//Categories array
	$query_categories=mysql_query("SELECT id, name, color FROM webfinance_categories ORDER BY name") or die (mysql_error());
	$nb_categories=mysql_num_rows($query_categories);
	$categories=array();
	while ($categorie=mysql_fetch_assoc($query_categories))
		$categories[]=$categorie;

	//Accounts arrays
	$query_accounts=mysql_query("SELECT id, account_name FROM webfinance_accounts ORDER BY account_name") or die (mysql_error());
	$nb_accounts=mysql_num_rows($query_accounts);
	$accounts=array();
	while ($account=mysql_fetch_assoc($query_accounts))
		$accounts[]=$account;
?>

	  <table class="text" border="0" cellpadding="3" cellspacing="0" width="100%">
	     <tr bgcolor="#ffffff">
	     <td colspan="8" align="left">
	     <?

	     if($show_color>0){
	       ?>
	     	<input type="submit" class="form" name="" value="hide color"/>
		 <?
	     }else{
	       ?>
	       <input type="submit" class="form" name="" value="show color"/>
	       <?
	     }
	     ?>
	       <input type="hidden" name="color" value="<?=$sh_col?>">

	     </td>
			<td colspan="2" align="right">
				<input type="hidden" name="pan" value="<?=$pan?>">
				<input type="submit" class="form" name="action" value="add"/>&nbsp;
				<input type="submit" class="form" name="action" value="edit"/>&nbsp;
				<input type="submit" class="form" name="action" value="update"/>&nbsp;
				<input type="submit" class="form" name="action" value="delete" onclick="return ask_confirmation('Do you really want to delete the selected transaction(s)?');"/>
			</td>
		</tr>
		<tr class="row_header">
			<th>&nbsp;</th>
			<th>
					<a href='?sort[field]=date&sort[order]=<?=$order?>
							&pan=<?=$pan?>
							&search[text]=<?=$text?>
							&search[comment]=<?=$comment?>
							&search[id_account]=<?=$id_account?>
							&search[id_category]=<?=$id_category?>
							&search[amount_min]=<? if($amount_min!="") echo $amount_min; ?>
							&search[amount_max]=<? if($amount_max!="") echo $amount_max; ?>
							&search[date_start]=<? if($date_start!="") echo $date_start; ?>
							&search[date_end]=<? if($date_end!="") echo $date_end; ?>
							&search[type]=<? echo $type; ?>
							&color=<?=$show_color?>
							&action=	'>
						<?=$vars['date']?>
					</a>
			</th>
			<th>
				<a href='?sort[field]=id_category&sort[order]=<?=$order?>
						&pan=<?=$pan?>
						&search[text]=<?=$text?>
						&search[comment]=<?=$comment?>
						&search[id_account]=<?=$id_account?>
						&search[id_category]=<?=$id_category?>
						&search[amount_min]=<? if($amount_min!="") echo $amount_min; ?>
						&search[amount_max]=<? if($amount_max!="") echo $amount_max; ?>
						&search[date_start]=<? if($date_start!="") echo $date_start; ?>
						&search[date_end]=<? if($date_end!="") echo $date_end; ?>
						&search[type]=<? echo $type; ?>
						&color=<?=$show_color?>
						&action=	'>
					Category
				</a>
			</th>
			<th>Type</th>
			<th>
				<a href='?sort[field]=text&sort[order]=<?=$order?>
						&pan=<?=$pan?>
						&search[text]=<?=$text?>
						&search[comment]=<?=$comment?>
						&search[id_account]=<?=$id_account?>
						&search[id_category]=<?=$id_category?>
						&search[amount_min]=<? if($amount_min!="") echo $amount_min; ?>
						&search[amount_max]=<? if($amount_max!="") echo $amount_max; ?>
						&search[date_start]=<? if($date_start!="") echo $date_start; ?>
						&search[date_end]=<? if($date_end!="") echo $date_end; ?>
						&search[type]=<? echo $type; ?>
						&color=<?=$show_color?>
						&action=	'>
					<?=$vars['text']?>
				</a>
			</th>
			<th colspan='2'>
				<a href='?sort[field]=amount&sort[order]=<?=$order?>
						&pan=<?=$pan?>
						&search[text]=<?=$text?>
						&search[comment]=<?=$comment?>
						&search[id_account]=<?=$id_account?>
						&search[id_category]=<?=$id_category?>
						&search[amount_min]=<? if($amount_min!="") echo $amount_min; ?>
						&search[amount_max]=<? if($amount_max!="") echo $amount_max; ?>
						&search[date_start]=<? if($date_start!="") echo $date_start; ?>
						&search[date_end]=<? if($date_end!="") echo $date_end; ?>
						&search[type]=<? echo $type; ?>
						&color=<?=$show_color?>
						&action=	'>
					<?=$vars['amount']?>
				</a>
			</th>
			<th>Balance</th>
			<th>
				<a href='?sort[field]=comment&sort[order]=<?=$order?>
						&pan=<?=$pan?>
						&search[text]=<?=$text?>
						&search[comment]=<?=$comment?>
						&search[id_account]=<?=$id_account?>
						&search[id_category]=<?=$id_category?>
						&search[amount_min]=<? if($amount_min!="") echo $amount_min; ?>
						&search[amount_max]=<? if($amount_max!="") echo $amount_max; ?>
						&search[date_start]=<? if($date_start!="") echo $date_start; ?>
						&search[date_end]=<? if($date_end!="") echo $date_end; ?>
						&search[type]=<? echo $type; ?>
						&color=<?=$show_color?>
						&action=	'>
					<?=$vars['comment']?>
				</a>
			</th>
			<th>File</th>
		</tr>
		<?
				$labels=array();
				$test_label=true;
				$test_date="";
				$previous_date="";
			while($transaction=mysql_fetch_assoc($query_transactions)){
			  //s�parer les mois
			  $op_date_ex=explode("-",$transaction['date']);
			  $current_month=date("F Y",mktime(0,0,0,$op_date_ex[1],1,$op_date_ex[0]));
				if(!empty($previous_date)){
					$prev_date_ex=explode("-",$previous_date);
					if($prev_date_ex[1]!=$op_date_ex[1])
					  echo "<tr bgcolor='#ffffff'><td colspan='10' align='center'><b>$current_month</b></td></tr>";
				}else
				  echo "<tr bgcolor='#ffffff'><td colspan='10' align='center'><b>$current_month</b></td></tr>";


				$previous_date=$transaction['date'];

// 				$query_expense=mysql_query("SELECT id FROM webfinance_expenses WHERE id_transaction=".$transaction['id'])
// 						or die(mysql_error());
// 				$nb_expense=mysql_num_rows($query_expense);

				$query_categorie=mysql_query("SELECT id , color, name FROM webfinance_categories WHERE id=".$transaction['id_category'])
						or die(mysql_error());
				$categ=mysql_fetch_assoc($query_categorie);

				//ROW color
				$passed_date=false;
				if($transaction['type']=="prevision"){
					$date_prev=explode("-",$transaction['date']);
					$passed_date=(mktime(23,59,59,$date_prev[1],$date_prev[2],$date_prev[0]) < mktime());
					if($passed_date)
						echo "<tr bgcolor='red'>";
					else
						echo "<tr bgcolor='yellow'>";
				}else if($transaction['type']=="asap")
					echo "<tr bgcolor='yellow'>";
				else if($categ['id']>0 AND strlen($categ['color'])>0 AND ($show_color>0))
					echo "<tr bgcolor=".$categ['color'].">";
				else
					echo "<tr bgcolor='#F3EFF3'>";

				//LABEL
				if($categ['id']>0){

					if($transaction['type']=="prevision" AND !$passed_date){
						$categ['color']="yellow";
						$categ['name']="prev";
					}else if($passed_date){
						$categ['color']="red";
						$categ['name']="outdated prev";
					}

					if(!array_key_exists($categ['color'],$labels))
						$labels[$categ['color']][]=$categ['name'];
					else{
						$color=$labels[$categ['color']];
						if(!in_array($categ['name'] ,$color))
							$labels[$categ['color']][]=$categ['name'];
					}
				}else if($test_label){
					$test_label=false;
					$labels["#E6E2E6"][]="no category";
				}

			?>
			<td>
				<input class="form" type="hidden" name="id_ope[]" value="<?=$transaction['id']?>"/>
				<input type="checkbox" name="chk[]" value="<?=$transaction['id']?>"/>
			</td>
			<td>
				<font size="1"><?=$transaction['date']?></font>
			</td>
			<td>
				<select class="form" name="cat[]">
					<option value="" <? if(mysql_num_rows($query_categorie)<1) echo "selected"; ?>><?= _("-- Choose --") ?></option>
			<?
				foreach($categories as $categorie){
			?>
					<option value="<?=$categorie['id']?>" <? if($categ['id']==$categorie['id']) echo "selected"; ?>>
						<?=$categorie['name']?>
					</option>
			<?
				}
			?>
				</select>
			</td>
			<td align="center">
				<small><?=substr($transaction['type'], 0, 4)?></small>
			</td>
			<td>
				 <small><small>

				<?
				    if($passed_date)
				      echo "<b>".$transaction['text']."</b>";
				    else
				      echo $transaction['text'];

				if($nb_expense>0)
				  echo "<a href='expenses' title='Expense linked'>[Expense (".$nb_expense.")]</a>";

				?>
				  <a href='?action=delete&chk[]=<?=$transaction['id']?>&color=<?=$sh_col?>' title='delete transaction' onclick="return ask_confirmation('Do you really want to delete this transaction?');">[Del]</a>&nbsp;
				<a href='?action=edit&chk[]=<?=$transaction['id']?>&pan=<?=$pan?>&color=<?=$sh_col?>' title='edit transaction' >[Edit]</a>

				   </small></small>
			</td>
				 <?
					$tmp=$transaction['amount'];
					if($tmp>=0){
					?>
						<td align='right'>&nbsp;</td>
									    <td align='right'><?=str_replace(' ','&nbsp;',number_format($tmp,2,',',' '))?></td>
					<?
					}else{
					?>
					  <td align='right'><?=str_replace(' ','&nbsp;',number_format($tmp,2,',',' '))?></td>
						<td align='right'>&nbsp;</td>
					<?
					}
				 ?>
			<td align='right'>
				 	<i>
				 	<? if($balance[$transaction['id']] <0)
			                     echo "<span style='color: rgb(255, 0, 0);'>".str_replace(' ','&nbsp;',number_format($balance[$transaction['id']],2))."</span>";
					   else
					     echo str_replace(' ', '&nbsp;', number_format($balance[$transaction['id']],2));
					?>
				 	</i>
			</td>
			<td>
					<input class="form" type="text" name="com[]" value="<?=$transaction['comment']?>" size="20" maxlength="250"/>
			</td>
			<td>
				<a href='file?action=file&id=<?=$transaction['id']?>'><?=$transaction['file_name']?></a>
			</td>
		</tr>

	<?
		}
	?>
		<tr bgcolor="#ffffff">
			<td colspan="10" align="right">
   				<input type="submit" class="form" name="action" value="add"/>
				<input class="form" type="submit" name="action" value="edit"/>
				<input class="form" type="submit" name="action" value="update"/>
				<input class="form" type="submit" name="action" value="delete" onclick="return ask_confirmation('Do you really want to delete the selected transaction(s)?');"/>
			</td>
		</tr>
	</table>
</form>
<center>
	<table class="text" bgcolor="#E6E2E6" border="0" cellpadding="2" cellspacing="4" width="100%">
		<tr>
			<td align="center">
			<?
				if($pan>1){
					?>

						<a href='?pan=<?=$last_pan?>
							&search[text]=<?=$text?>
							&search[comment]=<?=$comment?>
							&search[id_account]=<?=$id_account?>
							&search[id_category]=<?=$id_category?>
							&search[amount_min]=<? if($amount_min!="") echo $amount_min; ?>
							&search[amount_max]=<? if($amount_max!="") echo $amount_max; ?>
							&search[date_start]=<? if($date_start!="") echo $date_start; ?>
							&search[date_end]=<? if($date_end!="") echo $date_end; ?>
							&search[type]=<? echo $type; ?>
							&sort[field]=<?=$field?>&sort[order]=<?=$order_value?>
							&color=<?=$show_color?>
							&action=
						'>[<<]</a>
					<?
				}
				$nb_pan=floor($max/$step);
				for ($i = 1 ; $i <= $nb_pan ; $i++) {
				  $x=$i;
				  if($i==1 AND $nb_pan>1 AND $pan==0)
				    echo "[1]";
				  else if($i!=$pan){
				    if($i==1)
					$x=0;
					?>
					<a href='?pan=<?=$x?>
							&search[text]=<?=$text?>
							&search[comment]=<?=$comment?>
							&search[id_account]=<?=$id_account?>
							&search[id_category]=<?=$id_category?>
							&search[amount_min]=<? if($amount_min!="") echo $amount_min; ?>
							&search[amount_max]=<? if($amount_max!="") echo $amount_max; ?>
							&search[date_start]=<? if($date_start!="") echo $date_start; ?>
							&search[date_end]=<? if($date_end!="") echo $date_end; ?>"
							&search[type]=<? echo $type; ?>
							&sort[field]=<?=$field?>&sort[order]=<?=$order_value?>
							&color=<?=$show_color?>
							&action=
						'><?=$i?></a>
					<?
					}else
						echo "[".$i."]";
				}

				if($pan==0)
				    $next_pan=2;
				 else
				    $next_pan=$pan+1;

				$tmpx=($next_pan)*$step;
				if($max>$tmpx){
					?>
					<a href='?pan=<?=$next_pan?>
						&search[text]=<?=$text?>
						&search[comment]=<?=$comment?>
						&search[id_account]=<?=$id_account?>
						&search[id_category]=<?=$id_category?>
						&search[amount_min]=<? if($amount_min!="") echo $amount_min; ?>
						&search[amount_max]=<? if($amount_max!="") echo $amount_max; ?>
						&search[date_start]=<? if($date_start!="") echo $date_start; ?>
						&search[date_end]=<? if($date_end!="") echo $date_end; ?>"
						&search[type]=<? echo $type; ?>
						&sort[field]=<?=$field?>&sort[order]=<?=$order_value?>
						&color=<?=$show_color?>
						&action=
					'>[>>]</a>
					<?
				}
			?>
			</td>
		</tr>
	</table>
</center>

<!-- LABEL -->
<?
   if(count($labels)>0){
?>
<table width="100%" class="text">
	<tr>
		<?
			foreach($labels as $color=>$names)
				echo "<td bgcolor='".$color."' align='center'><small>".join(" | ",$names)."</small><td>";
		?>
	</tr>
</table>
<?
	    }
?>

<!-- END LABEL -->


 <?
 }

 ?>


<?

	 }
  //END SHOW TRANSACTIONS

}
?>
<hr/>
<?
require("../bottom.php");

?>
