<?php 

/*------------------------------------------------------------------------
# author    Gonzalo Suez
# copyright © 2012 gsuez.cl. All rights reserved.
# @license  http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
# Website   http://www.gsuez.cl
-------------------------------------------------------------------------*/

defined( '_JEXEC' ) or die; 

// variables

$tpath = $this->baseurl.'/templates/'.$this->template;



?>
<!doctype html>

<!--[if IEMobile]><html class="iemobile" lang="<?php echo $this->language; ?>"> <![endif]-->

<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="<?php echo $this->language; ?>"> <![endif]-->

<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="<?php echo $this->language; ?>"> <![endif]-->

<!--[if gt IE 8]><!-->
<html class="no-js" lang="<?php echo $this->language; ?>">
<!--<![endif]-->

<head>
<title><?php echo $this->error->getCode().' - '.$this->title; ?></title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0; user-scalable=0;" />
<!-- mobile viewport optimized -->

<link rel="stylesheet" href="<?php echo $tpath; ?>/css/error.css?v=1.0.0" type="text/css" />
<link rel="apple-touch-icon-precomposed" href="<?php echo $tpath; ?>/apple-touch-icon-57x57.png">
<!-- iphone, ipod, android -->

<link rel="apple-touch-icon-precomposed" sizes="72x72" href="<?php echo $tpath; ?>/apple-touch-icon-72x72.png">
<!-- ipad -->

<link rel="apple-touch-icon-precomposed" sizes="114x114" href="<?php echo $tpath; ?>/apple-touch-icon-114x114.png">
<!-- iphone retina -->

<link href="<?php echo $tpath; ?>/favicon.ico" rel="shortcut icon" type="image/vnd.microsoft.icon" />
<!-- favicon -->

<script src="<?php echo $tpath; ?>/js/modernizr-2.6.2.js" type="text/javascript"></script>
</head>
<style>
#error {
	max-width: 500px !important;
	padding: 20px;
	margin-top: 40px;
	border: 1px solid rgb(204, 204, 204);
}
.btn.button {
	border: medium none;
	background-color: #6AC123;
	color: #FFF;
	padding: 4px 10px;
	margin: 10px 0px 0px 5px;
	cursor: pointer;
}
p.error {
	font-size: 35px;
	color: #13244e;
	font-weight: 400;
}
</style>

<body>
<div style="color: #383431; font-family: 'Roboto', sans-serif;" align="center">
  <div id="error">
    <h1 align="center"><a href="<?php echo $this->baseurl; ?>/" class="ihrlogo"><img src="<?php echo $this->baseurl; ?>/images/logo.png"></a></h1>
    <?php 
		
		echo '<p class="error">Error-';
		
        echo $this->error->getCode().'</p>'; 

        if (($this->error->getCode()) == '404') {

          echo JText::_('JERROR_LAYOUT_REQUESTED_RESOURCE_WAS_NOT_FOUND');

        }

      ?>
    <?php
	
		echo '<br><br>';
		
	?>
    <a href="<?php echo $this->baseurl; ?>/">
    <button class="btn button"><?php echo 'Zurück zur Startseite'; ?></button>
    </a> </div>
</div>
</body>
</html>
