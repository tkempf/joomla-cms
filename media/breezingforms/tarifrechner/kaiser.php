<?php 
// tarifradiodata Kaiserenergie hat folgende Elemente
// checked;gesamtpreis;tarifid;kaiser|elissa|tauber;tarifanzeigename;tarifinfo;preisbeschreibung;Arbeitspreis mit Beschreibung;Grundpreis mit Beschreibung; Bonus mit Beschreibung;
$tpreis=htmlentities(trim($iEx[1]), ENT_QUOTES, 'UTF-8');
$tid=htmlentities(trim($iEx[2]), ENT_QUOTES, 'UTF-8');
$tname=htmlentities(trim($iEx[4]), ENT_QUOTES, 'UTF-8');
$tinfo=trim($iEx[5]);
$tpreisbeschr=htmlentities(trim($iEx[6]), ENT_QUOTES, 'UTF-8');
$tarbeit=htmlentities(trim($iEx[7]), ENT_QUOTES, 'UTF-8');
$tgrund=htmlentities(trim($iEx[8]), ENT_QUOTES, 'UTF-8');
$tbonus=htmlentities(trim($iEx[9]), ENT_QUOTES, 'UTF-8');
$ttinhalt='<div class=\"bfToolTipLabel\"><b>Ihre Preisbestandteile ...</b><div/>'.$tarbeit.'<br>'.$tgrund.'<br>'.$tbonus;
echo '<div class="swTarif">'."\n";
echo '   <div class="swTitel">'.$tname.'</div>';
echo '   <div id="swPreis" class="swPreis"><span id="swPreisbestandteile'.$i.'" class="bfTooltip">'.$tpreis.'</span></div>'."\n";
echo '
<script type="text/javascript"><!--
	JQuery(document).ready(function() {
		JQuery("#swPreisbestandteile'.$i.'").qtip({
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
echo '   <div class="swPreisbeschreibung">'.$tpreisbeschr.'</div>'."\n";
echo '   <div class="swSeparator"></div>';
echo '   <div class="swText">'.$tinfo.'</div>'."\n";
echo '   <div class="swRadio"><input '.($iEx[0] == 1 ? 'checked="checked" ' : '').' class="ff_elem" '.$tabIndex.$onclick.$onblur.$onchange.$onfocus.$onselect.$readonly.'type="radio" name="ff_nm_'.$mdata['bfName'].'[]" value="'.$tid.'" id="ff_elem'.$mdata['dbId'].$idExt.'"/>';
echo '      '.$tname."</div>\n";
echo '</div>'."\n";
?>