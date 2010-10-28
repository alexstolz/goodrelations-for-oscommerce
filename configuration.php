<?php

/* error reporting */
error_reporting(E_ALL ^ E_NOTICE);

chdir('..');

/* include ARC (RDF classes for PHP) */
include_once("semanticweb/arc/ARC2.php");

/* include other useful files */
include('semanticweb/includes/database_tables.php');
require('includes/application_top.php'); // includes also namespace definitions
include('semanticweb/classes/Format.php'); // format class
/* .. functions are included on bottom, after $config has been set */

$format = new Format();

/* open db connection */
$link_cfg = mysql_connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD);
mysql_select_db(DB_DATABASE, $link_cfg);

$query_table_exist = tep_db_query("show tables from ".DB_DATABASE." like '".TABLE_SEMANTICWEB_DEFINITION."'");
if(tep_db_num_rows($query_table_exist) == 0) {
 
	/* create tables */
	$create_table_definition = "create table if not exists ".TABLE_SEMANTICWEB_DEFINITION."(definition_id int(11) not null auto_increment primary key, definition_name varchar(64) not null)";
	tep_db_query($create_table_definition);
	
	$create_table_definition_values = "create table if not exists ".TABLE_SEMANTICWEB_DEFINITION_VALUES."(definition_values_id int(11) not null auto_increment, definition_id int(11) not null, definition_value varchar(64) not null, primary key (definition_values_id, definition_id))";
	tep_db_query($create_table_definition_values);
	
	/* goto install directory */
	if(is_dir('semanticweb/install'))
		header('Location: '.DIR_WS_CATALOG.'semanticweb/install/index.php');
}

/* define global, user defined, constants */
$elements = array(
	'BusinessFunction',
	'BusinessEntityType',
	'DeliveryMethod',
	'PaymentMethod',
	'WarrantyScope',
	'eligibleRegions',
	'SpecialValidity',
	'OfferingValidity',
	'eligibleLanguages',
	'eligibleCurrencies',
	'WeightUnitOfMeasurement'
);
$elements_arr = NULL;
foreach($elements as $element) {
 	$arr = NULL;
	$query = tep_db_query("select t2.definition_value from ".TABLE_SEMANTICWEB_DEFINITION." t1, ".TABLE_SEMANTICWEB_DEFINITION_VALUES." t2 where t1.definition_id=t2.definition_id and definition_name='".$element."'");
	while($row = tep_db_fetch_array($query)) {
		$arr[] = $row['definition_value'];
	}
	if($arr!=NULL && count($arr)>0) {
		// define(...)
		$upper_case = strtoupper(ereg_replace('^_', '', ereg_replace('[A-Z]','_\\0',$element)));
		$elements_arr[$element] = $upper_case;
		define($upper_case, implode(", ", $arr));
	}
}

/* configuration */
$config = array();
/* set some user defined values for the semantic web version (RDF dump) of the Web page */

// language, user defined
if(defined('ELIGIBLE_LANGUAGES') && constant('ELIGIBLE_LANGUAGES')!='') {
	$lang_udef_arr = explode(',',constant('ELIGIBLE_LANGUAGES'));
	$config['default_language_id'] = $lang_udef_arr[0];
	$config['default_language_name'] = $lang_udef_arr[1];
} else { // application language
	$config['default_language_id'] = $languages_id;
	$config['default_language_name'] = $language;
}

// currency, user defined
if(defined('ELIGIBLE_CURRENCIES') && constant('ELIGIBLE_CURRENCIES')!='') {
	$curr_udef_arr = explode(',',constant('ELIGIBLE_CURRENCIES'));
	$config['default_currency_code'] = $curr_udef_arr[0];
	$config['default_currency_symbol'] = $curr_udef_arr[1];
	$config['default_currency_symbol_left'] = $curr_udef_arr[2];
	$config['default_currency_symbol_right'] = $curr_udef_arr[3];
	$config['default_currency_conversion_value'] = $curr_udef_arr[4];
}
else if(defined('DEFAULT_CURRENCY') && $currencies) { // application currency
	$config['default_currency_code'] = DEFAULT_CURRENCY;
	$config['default_currency_symbol'] = $currencies->currencies[DEFAULT_CURRENCY]['symbol_left'].$currencies->currencies[DEFAULT_CURRENCY]['symbol_right'];
	$config['default_currency_symbol_left'] = $currencies->currencies[DEFAULT_CURRENCY]['symbol_left'];
	$config['default_currency_symbol_right'] = $currencies->currencies[DEFAULT_CURRENCY]['symbol_right'];
	$config['default_currency_conversion_value'] = $currencies->currencies[DEFAULT_CURRENCY]['value'];
}

// weight unit of measurement
if(defined('WEIGHT_UNIT_OF_MEASUREMENT') && constant('WEIGHT_UNIT_OF_MEASUREMENT')!='') {
	$uom_weight_udef_arr = explode(' ',constant('WEIGHT_UNIT_OF_MEASUREMENT'));
	$config['default_uom_weight_id'] = $uom_weight_udef_arr[0];
	$config['default_uom_weight_name'] = constant('WEIGHT_UNIT_OF_MEASUREMENT');
} else { // default unit of measurement for weights
	$config['default_uom_weight_id'] = "GRM";
	$config['default_uom_weight_name'] = "GRM (gram)";
}

/* now we've got all $config-variables .. time to include functions */
require('semanticweb/includes/functions.php');

/* close db connection */
mysql_close($link_cfg);
?>