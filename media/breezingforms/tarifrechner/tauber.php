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
$modalParams = array();
$modalParams['title']  = $tname;
$modalParams['height'] = "400";
$modalParams['width']  = "80%";

?>
<div class="whTarifWrapper">
	<a href="#modal<?php echo $tid?>" data-toggle="modal" role="button" class="btn btn-success">
	<div class="whTarif">
		<p class="whTitel"><?php echo $tname?></p>
		<div class="whPreis"><?php echo $tpreis?></div>
		<div class="whTarifInfo"></div>
	</div>
	</a>
	<div class="whRadio">
		<input <?php echo ($tchecked ? 'checked="checked" ' : '')?> class="ff_elem" <?php echo $tabIndex.$onclick.$onblur.$onchange.$onfocus.$onselect.$readonly ?>
		type="radio" name="ff_nm_<?php echo $mdata['bfName']?>[]" value="<?php echo $tid?>" id="ff_elem<?php echo $mdata['dbId'].$idExt?>"/><?php echo $tname?>
	</div>
</div>
<?php 
$modalBody='
		<div>
			<p>'.$tinfo.'</p>
		</div>
';
echo JHtml::_('bootstrap.renderModal', 'modal'.$tid , $modalParams, $modalBody);
?>		