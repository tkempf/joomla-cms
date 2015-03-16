<?php
require_once("../../configuration.php");
$jc=new JConfig();
$conn = mysql_connect($jc->host,$jc->user,$jc->password)or die(mysql_error());
mysql_select_db($jc->db,$conn)or die(mysql_error());
mysql_query('SET CHARACTER SET utf8') or die(mysql_error());
$arr = array();
$table="plz_nb_".strtolower(mysql_real_escape_string($_POST['tarifart']));
$plz= mysql_real_escape_string($_POST['plz']);
#$table="plz_nb_strom";
#$plz="89547";
$rs = mysql_query("SELECT DISTINCT gemeinde,ort,netzbetreiber,ka
					FROM $table
					WHERE plz='$plz'
					AND ({$table}.gueltigab <= CURRENT_DATE())
					AND ({$table}.gueltigbis >= CURRENT_DATE())
					ORDER BY gemeinde,ort,netzbetreiber") or die(mysql_error());
while($obj = mysql_fetch_object($rs)) {
	$arr[] = $obj;
}
echo '{"netzbetreiber":'.json_encode($arr).'}';