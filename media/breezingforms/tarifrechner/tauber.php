<?php 
// tarifradiodata Tauberenergie hat folgende Elemente
// checked;gesamtpreis;tarifid;tauber;tarifanzeigename;tarifinfo;Endpreisbeschreibung;Arbeitspreis mit Beschreibung;ArbeitspreisNT mit Beschreibung;Grundpreis mit Beschreibung; 
$tchecked=($iEx[0] == 1);
$tpreis=htmlentities(trim($iEx[1]), ENT_QUOTES, 'UTF-8');
$tpreisbeschr=' EUR/Jahr';
$tid='tarif'.htmlentities(trim($iEx[2]), ENT_QUOTES, 'UTF-8');
// $iEx[3] enthält	 den Selektor für das Template
$tname=htmlentities(trim($iEx[4]), ENT_QUOTES, 'UTF-8');
$tinfo=trim($iEx[5]);
$tarbeitspreis='Verbrauchspreis HT: '.htmlentities(trim($iEx[6]), ENT_QUOTES, 'UTF-8').' Cent pro kWh.<br>';
$tarbeitspreisnt=($iEx[7] == 0) ? '' : 'Verbrauchspreis NT: '.htmlentities(trim($iEx[7]), ENT_QUOTES, 'UTF-8').' Cent pro kWh.<br>';
$tgrund='Grundpreis: '.htmlentities(trim($iEx[8]), ENT_QUOTES, 'UTF-8').' EUR pro Jahr';
/*$ttinhalt='<div class=\"bfToolTipLabel\"><b>Ihre Preisbestandteile ...</b><div/>'.$tarbeitspreis.$tarbeitspreisnt.$tgrund;
echo '<div class="whTarifWrapper">'."\n";
	echo '<div class="whTarif">'."\n";
	echo '   <p class="whTitel">'.$tname.'</p>';
	echo '   <div id="whPreis" class="whPreis">'.$tpreis.'<br>'.$tpreisbeschr.'<br><span id="whPreisbestandteile'.$i.'" class="bfTooltip"></span></div>'."\n";
	echo '</div>'."\n";
echo '   <div class="whRadio"><input '.($tchecked ? 'checked="checked" ' : '').' class="ff_elem" '.$tabIndex.$onclick.$onblur.$onchange.$onfocus.$onselect.$readonly.'type="radio" name="ff_nm_'.$mdata['bfName'].'[]" value="'.$tid.'" id="ff_elem'.$mdata['dbId'].$idExt.'"/>';
echo '      '.$tname."</div>\n";
echo '</div>'."\n";
*/

$tinfobutton='<div class="whTarifFooter">
				<button id="wechselbutton'.$tid.'" class="ff_elem bfCustomSubmitButton" onClick="JQuery(\'#ff_elem'.$mdata['dbId'].$idExt.'\').prop(\'checked\',true);ff_validate_nextpage(this,\'click\');">Jetzt zu "'.$tname.'" wechseln</button>
			  </div>';


?>
<div class="whTarifWrapper">
	<div class="whTarifInfoHTML" id="whTarifInfo<?php echo $tid?>"><?php echo ($tinfo.$tinfobutton);?></div>	
	<div class="whTarif" onClick="wh_showTarifInfo('<?php echo $tid?>')">
		<p class="whTitel"><?php echo $tname?></p>
		<div class="whPreis"><?php echo $tpreis?></div>
		<div class="whTarifInfo"></div>
		<div class="whRadio">
			<input type="radio" name="ff_nm_<?php echo $mdata['bfName']?>[]" value="<?php echo $tid?>" id="ff_elem<?php echo $mdata['dbId'].$idExt?>"/>
		</div>
	</div>
</div>
<?php if(!$fdeclared){$fdeclared=true;?>
<script type="text/javascript">
<!--
function wh_showTarifInfo(id){
	   var myHTML=JQuery("#whTarifInfo" + id).html();
	   JQuery("#tarifinfo").html(myHTML).show();
	   //window.parent.JQuery(".breezingforms_iframe").iframeAutoHeight({heightOffset: 15, debug: false, diagnostics: false});
}
//-->
</script>
<?php }?>
