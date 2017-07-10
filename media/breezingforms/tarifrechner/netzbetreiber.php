<?php
require_once("../../../configuration.php");
$jc=new JConfig();
$conn = mysql_connect($jc->host,$jc->user,$jc->password)or die(mysql_error());
mysql_select_db($jc->db,$conn)or die(mysql_error());
mysql_query('SET CHARACTER SET utf8') or die(mysql_error());
$arr = array();
$table="plz_nb_".strtolower(mysql_real_escape_string($_POST['energieart']));
$plz= mysql_real_escape_string($_POST['plz']);
$rs = mysql_query("SELECT DISTINCT *, '' as nbunknown
					FROM $table
					WHERE plz='$plz'
					AND ({$table}.gueltigab <= CURRENT_DATE())
					AND ({$table}.gueltigbis >= CURRENT_DATE())
					ORDER BY ort,netzentgelt DESC,netzentgeltwaerme DESC,netzbetreiber") or die(mysql_error());
$lastobj->ort='';
$found=false;
while($obj = mysql_fetch_object($rs)) {
	if($lastobj->ort==$obj->ort and !$found){
		$lastobj->nbunknown='Netzbetreiber ist mir nicht bekannt';
		array_splice($arr,-1,0,array($lastobj));
		$found=true;
	}
	else{
		$found=false;
	}	
	$arr[] = $obj;
	$lastobj = clone $obj;
}
echo '{"netzbetreiber":'.json_encode($arr).'}';