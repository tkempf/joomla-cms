<?php
defined('_JEXEC') or die('Direct Access to this location is not allowed.');
/*
<?php if ($RECORD_ID != ''): ?>
	<?=$PROCESS_RECORDSAVEDID?><?=$RECORD_ID ?><?=$NL ?><br />
<?php endif; ?>
*/
foreach ($MAILDATA as $DATA)
	 $maildata[$DATA[_FF_DATA_NAME]]=$DATA[_FF_DATA_VALUE];
?>

<style type="text/css">
    h1,h2,h3,p, ul, li, a, td {
      font-family: arial,helvetica,sans-serif;
    }
</style>
<h2>Tauberenergie | Kuhn</h2>
<h2>Ihre Eingangsbestätigung</h2>
<p>Herzlich Willkommen sehr geehrte/r, <?php echo $maildata['Anrede'].' '.$maildata['Nachname']?>!</p>
<p>Vielen Dank, dass Sie sich für <?php echo $maildata['OrigAname']?>  entschieden haben.</p>
<p>In der Folge sehen Sie Ihre Auftragsdaten nochmal im Detail.</p>
<p>Bitte beachten Sie, dass der hier angezeigte Jahresgesamtpreis auf der von Ihnen<br>
gemachten Schätzung der benötigten Energiemenge beruht und daher vom tatsächlichen<br>
Jahresgesamtpreis abweichen kann.</p>
<p>Sie erhalten von uns nach erfolgter Prüfung eine Auftragsbestätigung zusammen mit <br>
Ihren Vertragsunterlagen per Post zugesandt</p>
<h3>Vielen Dank für Ihren Online-Auftrag vom <?php echo $SUBMITTED ?>.</h3>
<p>Gesamtpreis in EUR/Jahr: <?php echo $maildata['Gesamtpreis']?><br>
<p>Grundpreis in EUR/Jahr: <?php echo $maildata['Grundpreis']?><br>
Arbeitspreis HT in Cent/kWh: <?php echo $maildata['Arbeitspreis']?><br>
<?php if($maildata['ArbeitspreisNT'] > 0) : ?>
	Arbeitspreis NT in Cent/kWh: <?php echo $maildata['ArbeitspreisNT']?><br>
<?php endif ?>	
<?php if($maildata['SLPpreis'] > 0):?>
	Mehr-/Mindermengenpreis in Cent/kWh: <?php echo $maildata['SLPpreis']. ' Stand: '.$maildata['SLPtext'];?><br>
<?php endif?>
<?php if($maildata['Bonuspreis'] > 0) : ?>
    <?php echo $maildata['Bonustext'].': '.$maildata['Bonuspreis'];?><br>
	<br>Ihren Bonus schreiben wir Ihnen mit Ihrer ersten Verbrauchsabrechnung auf Ihrem Konto gut.<br>
<?php endif ?>
Mindestabnahme in kWh: <?php  echo $maildata['Mindestabnahme']?><br>
Vertragslaufzeit in Monaten: <?php  echo $maildata['Laufzeit']?></p>

<h3>Folgende Daten haben Sie uns übermittelt:</h3>
<p>Energiebedarf<?php if($maildata['verbrauchnt']>0) : ?> HT<?php endif?>: <?php echo $maildata['verbrauch']?> kWh/Jahr<br>
<?php if($maildata['verbrauchnt']>0) : ?>
Energiebedarf NT: <?php echo $maildata['verbrauchnt']?> kWh/Jahr<br>
<?php endif ?>	
Postleitzahl: <?php echo $maildata['Postleitzahl']?></p>
<p>Persönliche Angaben<br>
<?php echo $maildata['Anrede']?> <?php echo $maildata['Vorname']?> <?php echo $maildata['Nachname']?><br>
E-Mail: <?php echo $maildata['Email']?><br>
Vorwahl: <?php echo $maildata['Vorwahl']?><br>
Telefon: <?php echo $maildata['Telefon']?><br>
Geburtstag: <?php echo $maildata['Geburtstag']?><br>
Gewünschter Lieferbeginn: <?php echo $maildata['Lieferbeginn']?></p>
<p>Verbrauchsstelle<br>
Straße: <?php echo $maildata['Str']?><br>
Hausnummer: <?php echo $maildata['Hnr']?><?php echo $maildata['HnrZusatz']?><br>
PLZ, (Wohn-)Ort: <?php echo $maildata['Postleitzahl']?>, <?php echo $maildata['Wohnort']?><br>
Zählernummer: <?php echo $maildata['Zaehlernr']?><br>
Name des Netzbetreibers: <?php echo $maildata['Liefaltnetz']?></p>
<p>Bisheriger Lieferant<br>
Name: <?php echo $maildata['Liefaltname']?><br>
Kundennummer: <?php echo $maildata['Liefaltnr']?><br>
Kündigungsfrist: <?php echo $maildata['Liefaltkfrist']?><br>
Bereits gekündigt: <?php echo $maildata['Liefaltkuend']?></p>

<p>Grund des Wechsels: <?php echo $maildata['Wechselgrund']?><br>
Datum Neueinzug: <?php echo $maildata['Einzugsdatum']?></p>

<p>Bankverbindung<br>
Kontoinhaber: <?php echo $maildata['Kontoinhaber']?> <br>
Kreditinstitut: <?php echo $maildata['Kreditinstitut']?><br>
IBAN: <?php echo $maildata['MailKonto']?></p>

<p>Sie erteilen hiermit der 'Tauber Energie | Kuhn' die Ermächtigung, fällige Abschlagszahlungen<br>
von diesem Konto vorzunehmen und den Rechnungsbetrag gemäß tatsächlicher Verbrauchsabrechnung<br>
vom Konto abzubuchen bzw. diesem gutzuschreiben.</p>

<p>Rechnungsanschrift<br>
Straße: <?php echo $maildata['RE_Str']?> <br>
Hausnummer: <?php echo $maildata['RE_Hnr']?> <?php echo $maildata['RE_HnrZusatz']?> <br>
PLZ, Ort: <?php echo $maildata['RE_Plz']?>, <?php echo $maildata['RE_Ort']?></p>
<p>
(Anbei finden Sie unsere Allgemeinen Geschäftsbedingungen,besondere Vertragsbedingungen sowie<br>
die Grundversorgungsverordnung Strom/Gas als PDF Dokumente zur Information und Kenntnisnahme,<br>
mit deren Regelungen Sie sich einverstanden erklärt haben.)</p>
<p>Haben Sie noch Fragen? Dann melden Sie sich bei uns.<br>
Wir stehen Ihnen Mo. bis Fr. von 9.00 bis 17.00 telefonisch oder gerne auch per E-Mail<br>
für Ihre Fragen zur Verfügung.</p>
<p>Mit freundlichen Grüßen<br>
Ihr Team von Tauber Energie | Kuhn</p>

<h3>Tauber Energie | Kuhn</h3>

<table>
	<tr>
        <td width="60">Anschrift:</td>
    	<td>Engelsbergstr. 1, 97980 Bad Mergentheim</td>
    </tr>
	<tr>
    	<td>Telefon:</td>
        <td>07931/96494-0</td>
    </tr>
	<tr>
    	<td>E-Mail:</td>
        <td>info@ewerkkuhn.de</td>
    </tr>
    <tr>
    	<td valign="top">Internet:</td>
        <td>www.tauberenergie.de</td>
    </tr>
</table>
