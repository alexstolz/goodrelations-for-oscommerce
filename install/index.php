<?php

/* error reporting */
error_reporting(E_ALL ^ E_NOTICE);

chdir('..');

/* include other useful files */
require('configuration.php'); // namespace declaration, global constants, etc.

/* master data, that have to be configured by webshop owner */
$config_arc = array(
  /* db */
  'db_host' => DB_SERVER,
  'db_name' => DB_DATABASE,
  'db_user' => DB_SERVER_USERNAME,
  'db_pwd' => DB_SERVER_PASSWORD,
  /* store name = table prefix */
  'store_name' => 'arc',
);
$store = ARC2::getStore($config_arc);
if (!$store->isSetUp()) {
  $store->setUp(); // create mysql tables
}
$store->query('LOAD <'.$namespaces['gr'].'>');


/* print warning message, if install directory still exists on POST */
$message = NULL;
if ($_POST && file_exists(dirname('install'))) {
	$message['warning'][] = 'Installation directory exists at: catalog/semanticweb/install. Please remove or chmod this directory for security reasons.';
}
if($_POST) {
	$message['success'][] = 'Your business information have been submitted and stored permanently. <a href="'.DIR_WS_CATALOG.'semanticweb.rdf" target="_self">&raquo; show output</a>';
	/* create or update your business ontology */
	require('semanticweb/index.php');
}
else {
	$message['note'][] = 'Before you can start with generating machine-processable code, you should provide some further information about your business. After you have passed the configuration of your user-defined data, the system should be ready for publishing offerings annotations on the Web. <a href="'.DIR_WS_CATALOG.'semanticweb.rdf" target="_self">&raquo; show output</a>';
}

/* open db connection */
$link_install = mysql_connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD);
mysql_select_db(DB_DATABASE, $link_install);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>
	<title>Configuration for Semantic Web</title>
	<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
	<meta name="description" content="Install Semantic Web for osCommerce" />
	<link rel="stylesheet" type="text/css" href="../css/general.css"/>
</head>
<body>
<?php
if ($message != NULL) {
 	foreach($message as $msg_type=>$msg_array) {
 		foreach($msg_array as $msg)
			echo '<div class="'.$msg_type.'">'.strtoupper($msg_type).': '.$msg.'</div>';
		if($msg_type == 'error')
			die();
	}
}
?>
	<h1>Initial configuration of the master data</h1>
	<div>Please choose items from the lists below that you think that best fit your business (intention/structure). By pressing the Ctrl-key, you may check or <b>uncheck</b>!! multiple items from the lists. Pressing the <i>Submit alltogether</i>-button stores the selected values. Please note: changes are applied to <b>all</b> offerings in your Web shop.<br/>Several eligible instances originate from the <a href="http://purl.org/goodrelations/" target="_blank">GoodRelations</a> ontology.</div>
<form name="all" action="" method="post">

	<div class="outer">
	<h2>Global user defined data</h2>
<?php
$elements = array(
	//$element_name => $default_value
	'SpecialValidity'=>'1',
	'OfferingValidity'=>'1'
);
foreach($elements as $search=>$default) {
	global_var_config($search, $default);
}
?>
</div>
<?php
// get description from sparql
$search = 'Class';
$search_id = strtolower($search);
$q = '
PREFIX owl: <http://www.w3.org/2002/07/owl#> .
PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#> .
SELECT ?'.$search_id.' ?comment
	WHERE {
	?'.$search_id.' a owl:'.$search.'; rdfs:comment ?comment .
}
';
$description = NULL;
if ($rows = $store->query($q, 'rows')) {
	foreach ($rows as $row) {
		$item = ereg_replace('^.*#', '', $row[$search_id]);
		$description[$item] = $row['comment'];
	}
}
$description['eligibleLanguages'] = "Specify which language should be displayed in your businesses RDF feed.";
$description['eligibleCurrencies'] = "Specify the currency that best applies to your business and which will be displayed in the RDF feed.";
$description['WeightUnitOfMeasurement'] = "Select from the list below the value representing the product weights' unit of measurement.";
$description['eligibleRegions'] = "Specify in the list below all regions that your business is offering products for.<br />Multiple selection is possible through pressing STRG+(Left Mouse Click).";

// read master configuration data from sparql or mysql
$elements = array(
	'eligibleLanguages',
	'eligibleCurrencies',
	'WeightUnitOfMeasurement',
	'BusinessFunction',
	'BusinessEntityType',
	'DeliveryMethod',
	'PaymentMethod',
	'WarrantyScope',
	'eligibleRegions'
);
foreach($elements as $element) {
 	if(eregi('(eligibleRegions|eligibleLanguages|eligibleCurrencies|WeightUnitOfMeasurement)', $element))
 		master_config($element, $description[$element], false);
 	else
		master_config($element, $description[$element]);
}
?>
<br />
<input type="submit" value="Submit alltogether" style="color:green"/>
<input type="reset" value="Reset values" style="color:red"/>
</form>

<div class="foot">LGPL licensed by <a href="http://www.unibw.de/ebusiness/team/alex-stolz/" target="_blank">Alex Stolz</a>, team member of the <a href="http://www.unibw.de/ebusiness/" target="_blank">E-Business and Web Science Research Group</a> at UniBW</div>
</body>

</html>
<?php
/* close db connection */
mysql_close($link_install);
?>
