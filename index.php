<?php

/* version 0.1 */

//require('configuration.php'); // namespace declaration, global constants, etc.
isset($namespaces) or die('Restricted access');

/* document header */
foreach($namespaces as $ns => $uri) {
	$doctype .= $format->indent(1).'<!ENTITY '.$ns.' "'.$uri.'" >';
	$xmlns .= $format->indent(1).' xml'.($ns=='base'?'':'ns').':'.$ns.'="'.$uri.'"';
}
$xmlns .= $format->indent(1).' xmlns="'.$namespaces['base'].'#"';

/* open db connection */
$link = mysql_connect(DB_SERVER, DB_SERVER_USERNAME, DB_SERVER_PASSWORD);
mysql_select_db(DB_DATABASE, $link);

$rdf = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";

$rdf .= "<!DOCTYPE rdf:RDF [$doctype]>\n";
$rdf .= "<rdf:RDF $xmlns>\n";

// ontology
	$rdf .= $format->indent(1)."<!-- Ontology -->";
	$rdf .= $format->indent(1)."<owl:Ontology rdf:about=\"\">";
		$rdf .= $format->indent(2)."<dc:creator rdf:datatype=\"".$namespaces['xsd']."string\">".$format->xml_character_encode(strip_tags(STORE_OWNER))."</dc:creator>";
		$rdf .= $format->indent(2)."<owl:imports rdf:resource=\"".substr($namespaces['gr'], 0, -1)."\"/>";
		$rdf .= $format->indent(2)."<rdfs:label rdf:datatype=\"".$namespaces['xsd']."string\">RDF/XML data for ".$format->xml_character_encode(strip_tags(STORE_NAME)).", based on http://purl.org/goodrelations/</rdfs:label>";
	$rdf .= $format->indent(1)."</owl:Ontology>";

// store owner
	$rdf .= $format->indent(1)."<!-- Store owner -->";
	$rdf .= $format->indent(1)."<gr:BusinessEntity rdf:ID=\"".implode('_',explode(' ', $format->xml_character_encode(strip_tags(STORE_NAME))))."\">";
		$rdf .= $format->indent(2)."<gr:legalName rdf:datatype=\"".$namespaces['xsd']."string\">".$format->xml_character_encode(strip_tags(STORE_NAME))."</gr:legalName>";
		$rdf .= $format->indent(2)."<rdfs:seeAlso rdf:resource=\"".$format->url(HTTP_SERVER)."\"/>";
		$rdf .= $format->indent(2)."<vcard:adr>";
			$rdf .= $format->indent(3)."<vcard:Address rdf:ID=\"Address_".implode('_',explode(' ', $format->xml_character_encode(strip_tags(STORE_NAME))))."\">";
				if(defined('SHIPPING_ORIGIN_ZIP') && !eregi("NONE", SHIPPING_ORIGIN_ZIP) && STORE_COUNTRY==SHIPPING_ORIGIN_COUNTRY)
				$rdf .= $format->indent(4)."<vcard:postal-code xml:lang=\"en\">".SHIPPING_ORIGIN_ZIP."</vcard:postal-code>";
				if(defined('STORE_COUNTRY')) {
					$store_country = tep_db_query("select countries_iso_code_3 as code from ".TABLE_COUNTRIES." where countries_id='".STORE_COUNTRY."'");
					if($store_country_values = tep_db_fetch_array($store_country))
						$rdf .= $format->indent(4)."<vcard:country-name xml:lang=\"en\">".$store_country_values['code']."</vcard:country-name>";
				}
				if(defined('STORE_ZONE')) {
					$store_zone = tep_db_query("select zone_code as code from ".TABLE_ZONES." where zone_id='".STORE_ZONE."'");
					if($store_zone_values = tep_db_fetch_array($store_zone))
						$rdf .= $format->indent(4)."<vcard:region xml:lang=\"en\">".$store_zone_values['code']."</vcard:region>";
				}
				if(defined('STORE_NAME_ADDRESS'))
				$rdf .= $format->indent(4)."<vcard:label xml:lang=\"en\">".$format->xml_character_encode(strip_tags(STORE_NAME_ADDRESS))."</vcard:label>";
			$rdf .= $format->indent(3)."</vcard:Address>";
		$rdf .= $format->indent(2)."</vcard:adr>";
		$rdf .= $format->indent(2)."<vcard:url rdf:resource=\"".$format->url(HTTP_SERVER)."\"/>";
		if(defined('STORE_OWNER_EMAIL_ADDRESS'))
		$rdf .= $format->indent(2)."<vcard:email>".$format->xml_character_encode(strip_tags(STORE_OWNER_EMAIL_ADDRESS))."</vcard:email>";
		
$products = "select products_id from ".TABLE_PRODUCTS." where products_status='1'"; // just consider online products
$products = tep_db_query($products);
while($products_values = tep_db_fetch_array($products)):
		$rdf .= $format->indent(2)."<gr:offers rdf:resource=\"#Offering_".$products_values['products_id']."\"/>";
endwhile;
	$rdf .= $format->indent(1)."</gr:BusinessEntity>";

// manufacturers
	$rdf .= $format->indent(1)."<!-- Manufacturers -->";
// query manufacturers
$manufacturers = "select t1.manufacturers_id, t1.manufacturers_name, t2.manufacturers_url, t3.code as url_lang from ".TABLE_MANUFACTURERS." t1, ".TABLE_MANUFACTURERS_INFO." t2, ".TABLE_LANGUAGES." t3, ".TABLE_PRODUCTS." t4 where t1.manufacturers_id=t2.manufacturers_id and t2.manufacturers_id=t4.manufacturers_id and t2.languages_id=t3.languages_id and t2.languages_id='".$config['default_language_id']."' group by t1.manufacturers_id";
$manufacturers = tep_db_query($manufacturers);
while($manufacturers_values = tep_db_fetch_array($manufacturers)):

	$rdf .= $format->indent(1)."<gr:BusinessEntity rdf:ID=\"Manufacturer_".$manufacturers_values['manufacturers_id']."\">";
		$rdf .= $format->indent(2)."<gr:legalName rdf:datatype=\"".$namespaces['xsd']."string\">".$manufacturers_values['manufacturers_name']."</gr:legalName>";
if($manufacturers_values['manufacturers_url']):
		$rdf .= $format->indent(2)."<rdfs:seeAlso rdf:resource=\"".$format->url($manufacturers_values['manufacturers_url'])."\"/>";
endif;
	$rdf .= $format->indent(1)."</gr:BusinessEntity>";
endwhile;

// object property
	$rdf .= $format->indent(1)."<!-- Object Property -->";
	$rdf .= $format->indent(1)."<owl:ObjectProperty rdf:ID=\"hasProductWeight\">";
		$rdf .= $format->indent(2)."<rdfs:label rdf:datatype=\"".$namespaces['xsd']."string\">Product weight property</rdfs:label>";
		$rdf .= $format->indent(2)."<rdfs:comment rdf:datatype=\"".$namespaces['xsd']."string\">This property specifies the weight of a product or service.</rdfs:comment>";
		$rdf .= $format->indent(2)."<rdfs:subPropertyOf rdf:resource=\"".$namespaces['gr']."quantitativeProductOrServiceProperty\"/>";
		$rdf .= $format->indent(2)."<rdfs:domain rdf:resource=\"".$namespaces['gr']."ProductOrService\"/>";
		$rdf .= $format->indent(2)."<rdfs:range rdf:resource=\"".$namespaces['gr']."QuantitativeValueFloat\"/>";
	$rdf .= $format->indent(1)."</owl:ObjectProperty>";


// categories
	$rdf .= $format->indent(1)."<!-- Categories -->";
// query categories
$categories = "select t1.categories_id, t1.parent_id, t2.categories_name, t2.language_id from ".TABLE_CATEGORIES." t1, ".TABLE_CATEGORIES_DESCRIPTION." t2 where t1.categories_id=t2.categories_id and t2.language_id='".$config['default_language_id']."' order by t1.parent_id asc, t1.categories_id asc";
$categories = tep_db_query($categories);
while($categories_values = tep_db_fetch_array($categories)):
$rdf .= $format->indent(1)."<owl:Class rdf:ID=\"Category_".$categories_values['categories_id']."\">";
if($categories_values['parent_id'] == 0):
		$rdf .= $format->indent(2)."<rdfs:subClassOf rdf:resource=\"".$namespaces['gr']."ProductOrService\"/>";
else:
		$rdf .= $format->indent(2)."<rdfs:subClassOf rdf:resource=\"#Category_".$categories_values['parent_id']."\"/>";
endif;
		$rdf .= $format->indent(2)."<rdfs:label rdf:datatype=\"".$namespaces['xsd']."string\">".$format->xml_character_encode(strip_tags($categories_values['categories_name']))." (Catalog Group / Category)</rdfs:label>";
		$rdf .= $format->indent(2)."<rdfs:comment rdf:datatype=\"".$namespaces['xsd']."string\">This class specifies the ".$format->xml_character_encode(strip_tags($categories_values['categories_name']))." category that is used to classify product offerings.</rdfs:comment>";
	$rdf .= $format->indent(1)."</owl:Class>";
endwhile;

// warranty promises
	$rdf .= $format->indent(1)."<!-- Warranty Promises -->";
// query warranty promises
$warranty_promise = NULL;
$i=0;
$outer_query = tep_db_query("select t2.definition_value from ".TABLE_SEMANTICWEB_DEFINITION." t1, ".TABLE_SEMANTICWEB_DEFINITION_VALUES." t2 where t1.definition_id=t2.definition_id and t1.definition_name='WarrantyScope'");
while($outer_row = tep_db_fetch_array($outer_query)) {
	$inner_query = tep_db_query("select t2.definition_id, t2.definition_value from ".TABLE_SEMANTICWEB_DEFINITION." t1, ".TABLE_SEMANTICWEB_DEFINITION_VALUES." t2 where t1.definition_id=t2.definition_id and t1.definition_name='".$outer_row['definition_value']."'");
	while($inner_row = tep_db_fetch_array($inner_query)) {
	 	$warranty_promise[$i]['val1'] = $inner_row['definition_value'];
	 	$warranty_promise[$i++]['val2'] = $outer_row['definition_value'];
	} //end while
} // end while
if(!empty($warranty_promise))
	foreach($warranty_promise as $warranty_detail) {
	$rdf .= $format->indent(1)."<gr:WarrantyPromise rdf:ID=\"WarrantyPromise_".urlencode($warranty_detail['val2'])."\">";
		$rdf .= $format->indent(2)."<rdfs:label rdf:datatype=\"".$namespaces['xsd']."string\">Warranty promise for ".$warranty_detail['val2']."</rdfs:label>";
		$rdf .= $format->indent(2)."<rdfs:comment rdf:datatype=\"".$namespaces['xsd']."string\">".STORE_NAME." offers a warranty duration of ".$warranty_detail['val1']." months for ".$warranty_detail['val2']."</rdfs:comment>";
		$rdf .= $format->indent(2)."<gr:durationOfWarrantyInMonths rdf:datatype=\"".$namespaces['xsd']."int\">".$warranty_detail['val1']."</gr:durationOfWarrantyInMonths>";
		$rdf .= $format->indent(2)."<gr:hasWarrantyScope rdf:resource=\"".$namespaces['gr'].$warranty_detail['val2']."\"/>";
	$rdf .= $format->indent(1)."</gr:WarrantyPromise>";
	} //end foreach

// offerings
	$rdf .= $format->indent(1)."<!-- Offerings -->";
// query offerings
//$products = "select t1.products_id, t1.products_tax_class_id, t1.products_quantity, t1.products_price, t1.products_date_added, t1.products_date_available, t1.products_status, t2.categories_id from ".TABLE_PRODUCTS." t1, ".TABLE_PRODUCTS_TO_CATEGORIES." t2  where t1.products_id=t2.products_id";
$products = "select t1.products_id, t1.products_tax_class_id, t1.products_quantity, t1.products_price, t1.products_date_added, t1.products_date_available, t1.products_status, t1.products_weight, t1.manufacturers_id, t2.categories_id, t3.products_name, t3.products_description, t3.products_url, t4.code as lang_code from ".TABLE_PRODUCTS." t1, ".TABLE_PRODUCTS_TO_CATEGORIES." t2, ".TABLE_PRODUCTS_DESCRIPTION." t3, ".TABLE_LANGUAGES." t4 where t1.products_id=t2.products_id and t2.products_id=t3.products_id and t3.language_id='".$config['default_language_id']."' and t3.language_id=t4.languages_id order by t1.products_id asc";
$products = tep_db_query($products);
while($products_values = tep_db_fetch_array($products)):


$validfrom = time(); // offering is valid from today
$products_values['validfrom'] = $format->w3cdate($validfrom);

/* fetch expiration date from the master configuration */
$offering_validity = tep_db_query("select t2.definition_value from ".TABLE_SEMANTICWEB_DEFINITION." t1, ".TABLE_SEMANTICWEB_DEFINITION_VALUES." t2 where t1.definition_id=t2.definition_id and t1.definition_name='OfferingValidity'");
if($offering_validity = tep_db_fetch_array($offering_validity)) {
	if($offering_validity['definition_value'] > 0) // validthrough must at least be 1 day after validfrom!
		$products_values['validthrough'] = $format->w3cdate($validfrom, $offering_validity['definition_value']);
	else $products_values['validthrough'] = $format->w3cdate($validfrom, 1);
}

if($products_values['products_status']) {/* && ($products_values['validfrom']=='' || $products_values['validfrom']<$format->w3cdate(date('r'))) && ($products_values['validthrough']=='' || $products_values['validthrough']>$format->w3cdate(date('r')))): /* check, if product is online, and if it is valid */
	$rdf .= $format->indent(1)."<gr:Offering rdf:ID=\"Offering_".$products_values['products_id']."\">";
		$rdf .= $format->indent(2)."<gr:includesObject>";
			$rdf .= $format->indent(3)."<gr:TypeAndQuantityNode rdf:ID=\"TypeAndQuantityNode_".$products_values['products_id']."\">";
				$rdf .= $format->indent(4)."<gr:amountOfThisGood rdf:datatype=\"".$namespaces['xsd']."float\">"./*$products_values['products_quantity']*/"1.0"."</gr:amountOfThisGood>";
				$rdf .= $format->indent(4)."<gr:hasUnitOfMeasurement rdf:datatype=\"".$namespaces['xsd']."string\">C62</gr:hasUnitOfMeasurement>";
				$rdf .= $format->indent(4)."<gr:typeOfGood>";
					$rdf .= $format->indent(5)."<gr:ProductOrServicesSomeInstancesPlaceholder rdf:ID=\"ProductOrServiceInstance_".$products_values['products_id']."\">";
						if($products_values['categories_id'] != 0) // is in a category
						$rdf .= $format->indent(6)."<rdf:type rdf:resource=\"#Category_".$products_values['categories_id']."\"/>";
						$rdf .= $format->indent(6)."<rdfs:label rdf:datatype=\"".$namespaces['xsd']."string\">".$format->xml_character_encode(strip_tags($products_values['products_name']))." (ProductOrServicesSomeInstancesPlaceholder)</rdfs:label>";
						$rdf .= $format->indent(6)."<rdfs:comment rdf:datatype=\"".$namespaces['xsd']."string\">".$format->xml_character_encode(strip_tags($products_values['products_description']))."</rdfs:comment>";
						if($products_values['products_quantity']): // hasInventoryLevel property
							$rdf .= $format->indent(6)."<gr:hasInventoryLevel>";
								$rdf .= $format->indent(7)."<gr:QuantitativeValueFloat rdf:ID=\"QuantitativeValueFloat_ProductInventoryLevel_".$products_values['products_id']."\">";
									$rdf .= $format->indent(8)."<rdfs:label rdf:datatype=\"".$namespaces['xsd']."string\">Inventory level of ".$format->xml_character_encode(strip_tags($products_values['products_name']))."</rdfs:label>";
									$rdf .= $format->indent(8)."<rdfs:comment rdf:datatype=\"".$namespaces['xsd']."string\">".number_format($products_values['products_quantity'], 1, '.', '')." pieces of ".$format->xml_character_encode(strip_tags($products_values['products_name']))." are on stock</rdfs:comment>";
									$rdf .= $format->indent(8)."<gr:hasValueFloat rdf:datatype=\"".$namespaces['xsd']."float\">".number_format($products_values['products_quantity'], 1, '.', '')."</gr:hasValueFloat>";
									$rdf .= $format->indent(8)."<gr:hasUnitOfMeasurement rdf:datatype=\"".$namespaces['xsd']."string\">C62</gr:hasUnitOfMeasurement>";
								$rdf .= $format->indent(7)."</gr:QuantitativeValueFloat>";
							$rdf .= $format->indent(6)."</gr:hasInventoryLevel>";
						endif;
						if($products_values['products_weight'] != NULL): // hasWeight property
							$rdf .= $format->indent(6)."<self:hasProductWeight rdf:resource=\"#QuantitativeValueFloat_ProductWeight_".$products_values['products_id']."\"/>";
						endif;
						if($products_values['products_url']):
							$rdf .= $format->indent(6)."<rdfs:seeAlso rdf:resource=\"".$format->url($products_values['products_url'])."\"/>";
						endif;
						$rdf .= $format->indent(6)."<gr:hasMakeAndModel rdf:resource=\"#ProductOrServiceModel_".$products_values['products_id']."\"/>";
					$rdf .= $format->indent(5)."</gr:ProductOrServicesSomeInstancesPlaceholder>";
				$rdf .= $format->indent(4)."</gr:typeOfGood>";
			$rdf .= $format->indent(3)."</gr:TypeAndQuantityNode>";
		$rdf .= $format->indent(2)."</gr:includesObject>";
		$rdf .= $format->indent(2)."<gr:validFrom rdf:datatype=\"".$namespaces['xsd']."dateTime\">".$products_values['validfrom']."</gr:validFrom>";
		$rdf .= $format->indent(2)."<gr:validThrough rdf:datatype=\"".$namespaces['xsd']."dateTime\">".$products_values['validthrough']."</gr:validThrough>";
		// price specification
		$rdf .= $format->indent(2)."<gr:hasPriceSpecification>";
			$rdf .= $format->indent(3)."<gr:UnitPriceSpecification rdf:ID=\"UnitPriceSpecification_".$products_values['products_id']."\">";
				$rdf .= $format->indent(4)."<gr:hasCurrency rdf:datatype=\"".$namespaces['xsd']."string\">".($config['default_currency_code']?$config['default_currency_code']:$config['default_currency_symbol'])."</gr:hasCurrency>";
				$rdf .= $format->indent(4)."<gr:hasCurrencyValue rdf:datatype=\"".$namespaces['xsd']."float\">".$format->curr_format(tep_add_tax($products_values['products_price'], tep_get_tax_rate($products_values['products_tax_class_id']))*$config['default_currency_conversion_value'])."</gr:hasCurrencyValue>";
				$rdf .= $format->indent(4)."<gr:hasUnitOfMeasurement rdf:datatype=\"".$namespaces['xsd']."string\">C62</gr:hasUnitOfMeasurement>";
				$rdf .= $format->indent(4)."<gr:valueAddedTaxIncluded rdf:datatype=\"".$namespaces['xsd']."boolean\">".DISPLAY_PRICE_WITH_TAX."</gr:valueAddedTaxIncluded>";
				$rdf .= $format->indent(4)."<gr:validFrom rdf:datatype=\"".$namespaces['xsd']."dateTime\">".$products_values['validfrom']."</gr:validFrom>";
				$rdf .= $format->indent(4)."<gr:validThrough rdf:datatype=\"".$namespaces['xsd']."dateTime\">".$products_values['validthrough']."</gr:validThrough>";
			$rdf .= $format->indent(3)."</gr:UnitPriceSpecification>";
		$rdf .= $format->indent(2)."</gr:hasPriceSpecification>";

// special price specification
$specials = "select specials_id, specials_new_products_price as price, specials_date_added as validfrom, expires_date as validthrough, status from ".TABLE_SPECIALS." where products_id='".$products_values['products_id']."'";
$specials = tep_db_query($specials);
while($specials_values = tep_db_fetch_array($specials)): // while loop is run once, because at most 1 special per offer

if($specials_values['validthrough'] == "0000-00-00 00:00:00")
	$specials_values['validthrough'] = NULL;
	
$offset = 1; // default offset value of 1
/* calculate the validthrough element */
if($specials_values['validfrom'] != NULL && $specials_values['validthrough'] == NULL) { // validthrough is not set? - fetch it from the master configuration
	$special_validity = tep_db_query("select t2.definition_value from ".TABLE_SEMANTICWEB_DEFINITION." t1, ".TABLE_SEMANTICWEB_DEFINITION_VALUES." t2 where t1.definition_id=t2.definition_id and t1.definition_name='SpecialValidity'");
	if($special_validity = tep_db_fetch_array($special_validity)) {
	 	if($special_validity['definition_value'] > 0) // validthrough must at least be 1 day after validfrom!
			$offset = $special_validity['definition_value'];
	}
}
if(!isset($specials_values['validthrough']) || empty($specials_values['validthrough']))
	$specials_values['validthrough'] = $format->w3cdate(strtotime($specials_values['validfrom']), $offset);
else // e.g. valid through 1.1.2009, means valid through 1.1.2009 the whole day long --> shift by 1 day
	$specials_values['validthrough'] = $format->w3cdate(strtotime($specials_values['validthrough']), 1);

if($specials_values['status']):// && (empty($offset) || $specials_values['validthrough']>$format->w3cdate(time()))): /* check, if product special is online, and if it is valid */
		$rdf .= $format->indent(2)."<gr:hasPriceSpecification>";
			$rdf .= $format->indent(3)."<gr:UnitPriceSpecification rdf:ID=\"UnitPriceSpecification_".$products_values['products_id'].'_Special'."\">";
				$rdf .= $format->indent(4)."<rdfs:comment rdf:datatype=\"".$namespaces['xsd']."string\">Special price offer</rdfs:comment>";
				$rdf .= $format->indent(4)."<gr:hasCurrency rdf:datatype=\"".$namespaces['xsd']."string\">".($config['default_currency_code']?$config['default_currency_code']:$config['default_currency_symbol'])."</gr:hasCurrency>";
				$rdf .= $format->indent(4)."<gr:hasCurrencyValue rdf:datatype=\"".$namespaces['xsd']."float\">".$format->curr_format($specials_values['price']*$config['default_currency_conversion_value'])."</gr:hasCurrencyValue>";
if(!empty($specials_values['validfrom'])):
				$rdf .= $format->indent(4)."<gr:validFrom rdf:datatype=\"".$namespaces['xsd']."dateTime\">".$format->w3cdate(strtotime($specials_values['validfrom']))."</gr:validFrom>";
//endif;
//if(!empty($specials_values['validfrom']) && $specials_values['validthrough']>$format->w3cdate(time())): // validthrough should now be set (show only if validfrom is set)
				$rdf .= $format->indent(4)."<gr:validThrough rdf:datatype=\"".$namespaces['xsd']."dateTime\">".$specials_values['validthrough']."</gr:validThrough>";
endif;
				$rdf .= $format->indent(4)."<gr:hasUnitOfMeasurement rdf:datatype=\"".$namespaces['xsd']."string\">C62</gr:hasUnitOfMeasurement>";
				$rdf .= $format->indent(4)."<gr:valueAddedTaxIncluded rdf:datatype=\"".$namespaces['xsd']."boolean\">".DISPLAY_PRICE_WITH_TAX."</gr:valueAddedTaxIncluded>";
			$rdf .= $format->indent(3)."</gr:UnitPriceSpecification>";
		$rdf .= $format->indent(2)."</gr:hasPriceSpecification>";
endif;
endwhile; // end while special values


// get this array from the configuration.php
if(!empty($elements_arr)) {
	foreach($elements_arr as $element=>$upper) {
		switch($element) {
			case 'BusinessFunction':
				$rdf .= create_properties('has'.$element, $upper);
				break;
			case 'PaymentMethod':
				$rdf .= create_properties('accepted'.$element.'s', $upper);
				break;
			case 'DeliveryMethod':
				$rdf .= create_properties('available'.$element.'s', $upper);
				break;
			case 'BusinessEntityType':
				$rdf .= create_properties('eligibleCustomerTypes', $upper);
				break;
			case 'eligibleRegions':
				$rdf .= create_properties($element, $upper, false);
		}
	}
}
	
for($i=0; $i<count($warranty_promise); $i++):
		$rdf .= $format->indent(2)."<gr:hasWarrantyPromise rdf:resource=\"#WarrantyPromise_".urlencode($warranty_promise[$i]['val2'])."\"/>";
endfor;
	$rdf .= $format->indent(1)."</gr:Offering>";
}
endwhile;

// product or service models
	$rdf .= $format->indent(1)."<!-- Product or Service models -->";
// query product or service models
$products = "select t1.products_id, t1.products_status, t1.products_weight, t1.manufacturers_id, t2.categories_id, t3.products_name, t3.products_description, t3.products_url, t4.code as lang_code from ".TABLE_PRODUCTS." t1, ".TABLE_PRODUCTS_TO_CATEGORIES." t2, ".TABLE_PRODUCTS_DESCRIPTION." t3, ".TABLE_LANGUAGES." t4 where t1.products_id=t2.products_id and t2.products_id=t3.products_id and t3.language_id='".$config['default_language_id']."' and t3.language_id=t4.languages_id order by t1.products_id asc";
$products = tep_db_query($products);
while($products_values = tep_db_fetch_array($products)):
if($products_values['products_status']):
	$rdf .= $format->indent(1)."<gr:ProductOrServiceModel rdf:ID=\"ProductOrServiceModel_".$products_values['products_id']."\">";
		if($products_values['categories_id'] != 0) // is in a category
		$rdf .= $format->indent(2)."<rdf:type rdf:resource=\"#Category_".$products_values['categories_id']."\"/>";
		$rdf .= $format->indent(2)."<rdfs:label rdf:datatype=\"".$namespaces['xsd']."string\">".$format->xml_character_encode(strip_tags($products_values['products_name']))." (ProductOrServiceModel)</rdfs:label>";
		$rdf .= $format->indent(2)."<rdfs:comment rdf:datatype=\"".$namespaces['xsd']."string\">".$format->xml_character_encode(strip_tags($products_values['products_description']))."</rdfs:comment>";
		$rdf .= $format->indent(2)."<gr:hasManufacturer rdf:resource=\"#Manufacturer_".$products_values['manufacturers_id']."\"/>";
if($products_values['products_url']):
		$rdf .= $format->indent(2)."<rdfs:seeAlso rdf:resource=\"".$format->url($products_values['products_url'])."\"/>";
endif;
if($products_values['products_weight']!=0): // hasWeight property
		$rdf .= $format->indent(2)."<self:hasProductWeight>";
			$rdf .= $format->indent(3)."<gr:QuantitativeValueFloat rdf:ID=\"QuantitativeValueFloat_ProductWeight_".$products_values['products_id']."\">";
				$rdf .= $format->indent(4)."<rdfs:label rdf:datatype=\"".$namespaces['xsd']."string\">Weight description for ".$format->xml_character_encode(strip_tags($products_values['products_name']))."</rdfs:label>";
				$rdf .= $format->indent(4)."<rdfs:comment rdf:datatype=\"".$namespaces['xsd']."string\">".$format->xml_character_encode(strip_tags($products_values['products_name']))." has a weight of ".$format->curr_format($products_values['products_weight'])." ".$config['default_uom_weight_name']."</rdfs:comment>";
				$rdf .= $format->indent(4)."<gr:hasValueFloat rdf:datatype=\"".$namespaces['xsd']."float\">".$format->curr_format($products_values['products_weight'])."</gr:hasValueFloat>";
				$rdf .= $format->indent(4)."<gr:hasUnitOfMeasurement rdf:datatype=\"".$namespaces['xsd']."string\">".$config['default_uom_weight_id']."</gr:hasUnitOfMeasurement>";
			$rdf .= $format->indent(3)."</gr:QuantitativeValueFloat>";
		$rdf .= $format->indent(2)."</self:hasProductWeight>";
endif;
	$rdf .= $format->indent(1)."</gr:ProductOrServiceModel>";
endif;
endwhile;
			
$rdf .= "\n</rdf:RDF>\n\n";

//$rdf = ereg_replace("&[^amp;]", "&amp;", $rdf);


/* close db connection */
mysql_close($link);

$filename = "semanticweb.rdf";
if(!isset($timeout))
	$timeout = 3600; // cache time of 1 hour
$verbose = false;
if (!$_POST && file_exists($filename) && (time()-filemtime($filename) < $timeout)) {
	// file exists and is cached	
	if($verbose)
		echo "File is cached for 1 hour";
}
else {
	// write produced output into an rdf file
	$feedFile = fopen($filename, "w+");
	if ($feedFile) {
		fputs($feedFile, $rdf);
		fclose($feedFile);
		if($verbose)
			echo "Success creating feed file";
	} else {
	 	if($verbose)
			echo "Error creating feed file, please check write permissions";
	}
}

?>