<?php 
// tarifradiodata Elissa hat folgende Elemente
// Elissa: oeko,tarifid,oekotarif,kaiser|elissa;tarifname;gesamtpreis,preisbeschreibung;bonusbeschreibung;tarifinfo;grundpreis;gpbeschreibung;arbeitspreis;apbeschreibung
$tchecked=($iEx[0] == 1);
$tid=htmlentities(trim($iEx[1]), ENT_QUOTES, 'UTF-8');
$tart=($iEx[2] == 1 ? 'oekotarif' : 'standardtarif');
$tname=htmlentities(trim($iEx[4]), ENT_QUOTES, 'UTF-8');
$tpreis=htmlentities(trim($iEx[5]), ENT_QUOTES, 'UTF-8');
$tpreisbeschr=htmlentities(trim($iEx[6]), ENT_QUOTES, 'UTF-8');
$tbonusbeschr=htmlentities(trim($iEx[7]), ENT_QUOTES, 'UTF-8');
$tinfo=trim($iEx[8]);
$tgrund=htmlentities(trim($iEx[9]), ENT_QUOTES, 'UTF-8');
$tgrundbeschr=htmlentities(trim($iEx[10]), ENT_QUOTES, 'UTF-8');
$tarbeit=htmlentities(trim($iEx[11]), ENT_QUOTES, 'UTF-8');
$tarbeitbeschr=htmlentities(trim($iEx[12]), ENT_QUOTES, 'UTF-8');
echo '<div class="swTarif" id="'.$tart.'">'."\n";
echo '   <div class="swPreis">'.$tpreis.'</div>'."\n";
echo '   <div class="swPreisbeschreibung">'.$tpreisbeschr.'</div>'."\n";
echo '   <div class="swBonus">'.$tbonusbeschr.'</div>'."\n";
echo '   <div class="swText">'.$tinfo.'</div>'."\n";
echo '   <div class="swEinzelpreise"><p>'.$tgrund.'</p><p>'.$tarbeit."</p></div>\n";
echo '   <div class="swEinzelpreisbeschreibung"><p>'.$tgrundbeschr.'</p><p>'.$tarbeitbeschr."</p></div>\n";
echo '   <div class="swRadio"><input '.($tchecked ? 'checked="checked" ' : '').' class="ff_elem" '.$tabIndex.$onclick.$onblur.$onchange.$onfocus.$onselect.($readonly ? ' disabled="disabled" ' : '').'type="radio" name="ff_nm_'.$mdata['bfName'].'[]" value="'.$tid.'" id="ff_elem'.$mdata['dbId'].$idExt.'"/>';
echo '      '.$tid."</div>\n";
echo '</div>'."\n";
?>