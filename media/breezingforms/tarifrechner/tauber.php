<?php 
// tarifradiodata Tauberenergie hat folgende Elemente
// checked;gesamtpreis;tarifid;tauber;tarifanzeigename;tarifinfo;Endpreisbeschreibung;Arbeitspreis mit Beschreibung;ArbeitspreisNT mit Beschreibung;Grundpreis mit Beschreibung; 
$tchecked=($iEx[0] == 1);
$tpreis=htmlentities(trim($iEx[1]), ENT_QUOTES, 'UTF-8');
$tpreisbeschr='EUR/Jahr';
$tid='tarif'.htmlentities(trim($iEx[2]), ENT_QUOTES, 'UTF-8');
// $iEx[3] enthält den Selektor für das Template
$tname=htmlentities(trim($iEx[4]), ENT_QUOTES, 'UTF-8');
$tinfo=trim($iEx[5]);
$tarbeitspreis='Verbrauchspreis HT: '.htmlentities(trim($iEx[6]), ENT_QUOTES, 'UTF-8').' Cent pro kWh.<br>';
$tarbeitspreisnt=($iEx[7] == 0) ? '' : 'Verbrauchspreis NT: '.htmlentities(trim($iEx[7]), ENT_QUOTES, 'UTF-8').' Cent pro kWh.<br>';
$tgrund='Grundpreis: '.htmlentities(trim($iEx[8]), ENT_QUOTES, 'UTF-8').' EUR pro Jahr';
$ttinhalt='<div class=\"bfToolTipLabel\"><b>Ihre Preisbestandteile ...</b><div/>'.$tarbeitspreis.$tarbeitspreisnt.$tgrund;
echo '<div class="whTarif">'."\n";
echo '   <div class="whTitel">'.$tname.'</div>';
echo '   <div id="whPreis" class="whPreis"><span id="whPreisbestandteile'.$i.'" class="bfTooltip">'.$tpreis.'</span></div>'."\n";
echo '
		<script type="text/javascript"><!--
			JQuery(document).ready(function() {
				JQuery("#whPreisbestandteile'.$i.'").qtip({
					position: { adjust: { screen: true }},
					content: "'.$ttinhalt.'",
					style: {
						tip: !JQuery.browser.ie,
						background: "#ffc",
						color: "#000000",
						border : {color: "#C0C0C0", width: 1 },
						name: "cream"
					}
				});
			});
		//--></script>';
echo '   <div class="whPreisbeschreibung">'.$tpreisbeschr.'</div>'."\n";
echo '   <div class="whSeparator"></div>';
echo '   <div class="whText">'.$tinfo.'</div>'."\n";
echo '   <div class="whRadio"><input '.($tchecked ? 'checked="checked" ' : '').' class="ff_elem" '.$tabIndex.$onclick.$onblur.$onchange.$onfocus.$onselect.$readonly.'type="radio" name="ff_nm_'.$mdata['bfName'].'[]" value="'.$tid.'" id="ff_elem'.$mdata['dbId'].$idExt.'"/>';
echo '      '.$tname."</div>\n";
echo '</div>'."\n";
?>