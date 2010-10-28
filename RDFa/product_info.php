<?php
/*
  $Id: product_info.php 1739 2007-12-20 00:52:16Z hpdl $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2003 osCommerce

  Released under the GNU General Public License
*/

//  require('includes/application_top.php');
	chdir('semanticweb'); // configuration.php then rechanges directory to ".."
	require('configuration.php');
	require('semanticweb/index.php');
	
	require(DIR_WS_LANGUAGES . $language . '/' . FILENAME_PRODUCT_INFO);
	
	$product_check_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_status = '1' and p.products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "'");
	$product_check = tep_db_fetch_array($product_check_query);
?>
<?php
echo '<?xml version="1.0"?>'."\n\n";

echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN" "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">'."\n";
/*<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>*/
/** create
echo '<html xmlns="http://www.w3.org/1999/xhtml"
   xml:base="http://localhost/oscommerce/catalog/"
   xmlns:dc="http://purl.org/dc/elements/1.1/"
   xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
   xmlns:xsd="http://www.w3.org/2001/XMLSchema#"
   xmlns:owl="http://www.w3.org/2002/07/owl#"
   xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
   xmlns:gr="http://purl.org/goodrelations/v1#" xml:lang="en">';
**/

$xmlns = '<html xmlns="http://www.w3.org/1999/xhtml"';
foreach($namespaces as $ns => $uri) {
	if($ns != "base")
	$xmlns .= "\n".'  xmlns:'.$ns.'="'.$uri.'"';
}
$xmlns .= ' xml:lang="'.$config['default_language_name'].'">'."\n\n";
echo $xmlns;
?>
<head>
<?php
echo '<meta http-equiv="Content-Type" content="application/xhtml+xml; charset='.CHARSET.'"/>';
/*<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">*/
if(file_exists("semanticweb.rdf")):
?>
<link rel="meta" type="application/rdf+xml" title="RDF/XML data" href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG . 'semanticweb.rdf'; ?>"/>
<?php endif; ?>
<title><?php echo TITLE; ?></title>
<base href="<?php echo (($request_type == 'SSL') ? HTTPS_SERVER : HTTP_SERVER) . DIR_WS_CATALOG . 'semanticweb.rdf'; ?>">
<link rel="stylesheet" type="text/css" href="stylesheet.css">
<script language="javascript"><!--
function popupWindow(url) {
  window.open(url,'popupWindow','toolbar=no,location=no,directories=no,status=no,menubar=no,scrollbars=no,resizable=yes,copyhistory=no,width=100,height=100,screenX=150,screenY=150,top=150,left=150')
}
//--></script>
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->
<span typeof="owl:Ontology" about="">
	<span property="dc:creator" datatype="xsd:string" content="<?php echo STORE_OWNER ?>"></span>
	<span rel="owl:imports" resource="<?php echo substr($namespaces['gr'], 0, -1) ?>"></span>
	<span property="rdfs:label" datatype="xsd:string" content="RDF/XML data for <?php echo STORE_NAME ?>, based on http://purl.org/goodrelations/"></span>
</span>
<!-- body //-->
<table border="0" width="100%" cellspacing="3" cellpadding="3">
  <tr>
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </table></td>
<!-- body_text //-->
    <td width="100%" valign="top"><?php echo tep_draw_form('cart_quantity', tep_href_link(FILENAME_PRODUCT_INFO, tep_get_all_get_params(array('action')) . 'action=add_product')); 
	/* Business Entity */
?>
    <span typeof="gr:BusinessEntity" about="#<?php echo implode('_',explode(' ', STORE_NAME)); ?>" property="gr:legalName" datatype="xsd:string" content="<?php echo STORE_NAME; ?>">
		<a rel="rdfs:seeAlso" href="<?php echo $format->url(HTTP_SERVER); ?>"></a>
		<span rel="vcard:adr">
			<span typeof="vcard:Address" about="#Address_<?php echo implode('_',explode(' ', STORE_NAME)); ?>">
				<?php
				if(defined('STORE_COUNTRY'))
				{
					$store_country = tep_db_query("select countries_iso_code_3 as code from ".TABLE_COUNTRIES." where countries_id='".STORE_COUNTRY."'");
					if($store_country_values = tep_db_fetch_array($store_country)) {
				?>
				<span property="vcard:country-name" content="<?php echo $store_country_values['code'] ?>"></span>
				<?php
					}
				}
				if(defined('STORE_ZONE')) {
					$store_zone = tep_db_query("select zone_code as code from ".TABLE_ZONES." where zone_id='".STORE_ZONE."'");
					if($store_zone_values = tep_db_fetch_array($store_zone)) {
				?>
				<span property="vcard:region" content="<?php echo $store_zone_values['code'] ?>"></span>
				<?php
					}
				}
				if(defined('STORE_NAME_ADDRESS')):
				?>
				<span property="vcard:label" content="<?php echo STORE_NAME_ADDRESS ?>"></span>
				<?php endif ?>
			</span>
		</span>
		<a rel="vcard:url" href="<?php echo $format->url(HTTP_SERVER); ?>"></a>
		<?php if(defined('STORE_OWNER_EMAIL_ADDRESS')): ?>
		<span property="vcard:email" content="<?php echo STORE_OWNER_EMAIL_ADDRESS ?>"></span>
		<?php endif ?>
		<span rel="gr:offers" resource="#Offering_<?php echo $HTTP_GET_VARS['products_id']; ?>"></span>
	</span>

	<span typeof="owl:ObjectProperty" about="#hasProductWeight">
		<span property="rdfs:label" datatype="xsd:string" content="Product weight property"></span>
		<span property="rdfs:comment" datatype="xsd:string" content="This property specifies the weight of a product or service."></span>
		<span rel="rdfs:subPropertyOf" resource="<?php echo $namespaces['gr']; ?>quantitativeProductOrServiceProperty"></span>
		<span rel="rdfs:domain" resource="<?php echo $namespaces['gr']; ?>ProductOrService"></span>
		<span rel="rdfs:range" resource="<?php echo $namespaces['gr']; ?>QuantitativeValueFloat"></span>
	</span>

<?php
// query categories
$categories = "select t1.categories_id, t1.parent_id, t2.categories_name, t2.language_id from ".TABLE_CATEGORIES." t1, ".TABLE_CATEGORIES_DESCRIPTION." t2 where t1.categories_id=t2.categories_id and t2.language_id='".$config['default_language_id']."' order by t1.parent_id asc, t1.categories_id asc";
$categories = tep_db_query($categories);
while($categories_values = tep_db_fetch_array($categories)): ?>
<span typeof="owl:Class" about="#Category_<?php echo $categories_values['categories_id'] ?>">
	<?php if($categories_values['parent_id'] == 0): ?>
	<span rel="rdfs:subClassOf" resource="<?php echo $namespaces['gr']; ?>ProductOrService"></span>
	<?php else: ?>
	<span rel="rdfs:subClassOf" resource="#Category_<?php echo $categories_values['parent_id']; ?>"></span>
	<?php endif; ?>
	<span property="rdfs:label" datatype="xsd:string" content="<?php echo $categories_values['categories_name'] ?> (Catalog Group / Category)"></span>
	<span property="rdfs:comment" datatype="xsd:string" content="This class specifies the <?php echo $categories_values['categories_name'] ?> category that is used to classify product offerings."></span>
</span>
<?php
endwhile;

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
if($warranty_promise != NULL)
	foreach($warranty_promise as $warranty_detail) {
?>
	<span about="#WarrantyPromise_<?php echo urlencode($warranty_detail['val2']); ?>" typeof="gr:WarrantyPromise">
		<span property="rdfs:label" datatype="xsd:string" content="Warranty promise for <?php echo $warranty_detail['val2'] ?>"></span>
		<span property="rdfs:comment" datatype="xsd:string" content="<?php echo STORE_NAME ?> offers a warranty duration of <?php echo $warranty_detail['val1'] ?> months for <?php echo $warranty_detail['val2'] ?>"></span>
		<span property="gr:durationOfWarrantyInMonths" datatype="xsd:int" content="<?php echo $warranty_detail['val1']; ?>"></span>
		<span rel="gr:hasWarrantyScope" resource="<?php echo $namespaces['gr'].$warranty_detail['val2']; ?>"></span>
	</span>
<?php
	} //end foreach
	
	$product_info_query = "select t1.products_id, t1.products_tax_class_id, t1.products_quantity, t1.products_price, t1.products_date_added, t1.products_model, t1.products_image, t1.products_date_available, t1.products_status, t1.products_weight, t1.manufacturers_id, t2.categories_id, t3.products_name, t3.products_description, t3.products_url, t4.code as lang_code from ".TABLE_PRODUCTS." t1, ".TABLE_PRODUCTS_TO_CATEGORIES." t2, ".TABLE_PRODUCTS_DESCRIPTION." t3, ".TABLE_LANGUAGES." t4 where t1.products_status='1' and t1.products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' and t1.products_id=t2.products_id and t2.products_id=t3.products_id and t3.language_id='" . (int)$languages_id . "' and t3.language_id=t4.languages_id order by t1.products_id asc";
	$product_info_query = tep_db_query($product_info_query);
	//$product_info_query = tep_db_query("select p.products_id, pd.products_name, pd.products_description, p.products_model, p.products_quantity, p.products_image, pd.products_url, p.products_price, p.products_tax_class_id, p.products_date_added, p.products_date_available, p.manufacturers_id, p.products_weight from " . TABLE_PRODUCTS . " p, " . TABLE_PRODUCTS_DESCRIPTION . " pd where p.products_status = '1' and p.products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' and pd.products_id = p.products_id and pd.language_id = '" . (int)$languages_id . "'");
    $product_info = tep_db_fetch_array($product_info_query);
	
	echo '<span about="#ProductOrServiceInstance_'.$HTTP_GET_VARS['products_id'].'" typeof="'.($product_info['categories_id']!=0?'self:Category_'.$product_info['categories_id'].' ':'').'gr:ProductOrServicesSomeInstancesPlaceholder">';
    echo '<span property="rdfs:comment" datatype="xsd:string" content="'.strip_tags($product_info['products_description']).'"></span>';
	echo '<span property="rdfs:label" datatype="xsd:string" content="'.$product_info['products_name'].' (ProductOrServicesSomeInstancesPlaceholder)"></span>';
	if($product_info['products_quantity']):
	echo '<span rel="gr:hasInventoryLevel">';
		echo '<span about="#QuantitativeValueFloat_ProductInventoryLevel_'.$product_info['products_id'].'" typeof="gr:QuantitativeValueFloat">';
			echo '<span property="rdfs:label" datatype="xsd:string" content="Inventory level of '.strip_tags($product_info['products_name']).'"></span>';
			echo '<span property="rdfs:comment" datatype="xsd:string" content="'.number_format($product_info['products_quantity'], 1, '.', '').' pieces of '.strip_tags($product_info['products_name']).' are on stock"></span>';
			echo '<span property="gr:hasValueFloat" datatype="xsd:float" content="'.number_format($product_info['products_quantity'], 1, '.', '').'"></span>';
			echo '<span property="gr:hasUnitOfMeasurement" datatype="xsd:string" content="C62"></span>';
		echo '</span>';
	echo '</span>';
	endif;

	if($product_info['products_url']): ?>
	<a rel="rdfs:seeAlso" href="<?php echo $format->url($product_info['products_url']); ?>"></a>
<?php endif; if(!empty($product_info['products_weight'])): // hasProductWeight property
?>
	<span rel="self:hasProductWeight" resource="#QuantitativeValueFloat_ProductWeight_<?php echo $product_info['products_id']; ?>"></span>
<?php endif;
	/* connect category type to product instance */
	echo '<span rel="gr:hasMakeAndModel" resource="#ProductOrServiceModel_'.$HTTP_GET_VARS['products_id'].'"></span>';
	echo '</span>';
	
	?>
	<table border="0" width="100%" cellspacing="0" cellpadding="0">
<?php
  if ($product_check['total'] < 1) {
?>
      <tr>
        <td><?php new infoBox(array(array('text' => TEXT_PRODUCT_NOT_FOUND))); ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
          <tr class="infoBoxContents">
            <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                <td align="right"><?php echo '<a href="' . tep_href_link(FILENAME_DEFAULT) . '">' . tep_image_button('button_continue.gif', IMAGE_BUTTON_CONTINUE) . '</a>'; ?></td>
                <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
<?php
  } else {
    /* validfrom and validthrough for product offering */
	$validfrom = time();//max(array($product_info['products_date_available'], $product_info['products_date_added']));
    $offering_validfrom = $format->w3cdate($validfrom);
    
    $offering_validity = tep_db_query("select t2.definition_value from ".TABLE_SEMANTICWEB_DEFINITION." t1, ".TABLE_SEMANTICWEB_DEFINITION_VALUES." t2 where t1.definition_id=t2.definition_id and t1.definition_name='OfferingValidity'");
	if($offering_validity = tep_db_fetch_array($offering_validity)) {
	 	$offset_in_days = $offering_validity['definition_value'];
	 	if($offset_in_days > 0) {
	 		$offering_validthrough = $format->w3cdate($validfrom, $offset_in_days);
		}
		else $offering_validthrough = $format->w3cdate($validfrom, 1);
	}

    tep_db_query("update " . TABLE_PRODUCTS_DESCRIPTION . " set products_viewed = products_viewed+1 where products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' and language_id = '" . (int)$languages_id . "'");
	
	/* get special price's validfrom and validthrough */
	$specials = "select specials_new_products_price as price, specials_date_added as validfrom, expires_date as validthrough, status from ".TABLE_SPECIALS." where products_id='".$HTTP_GET_VARS['products_id']."'";
	$specials = tep_db_query($specials);
	if($specials_values = tep_db_fetch_array($specials)) { // if can be used, because at most 1 special per offer
	
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
	
		if($specials_values['status']) {// && (empty($offset) || $specials_values['validthrough']>$format->w3cdate(time()))) { /* check, if product special is online, and if it is valid */
			if ($new_price = $specials_values['price']) {
			  // normal price
		      $products_price = '<s rel="gr:hasPriceSpecification">';
			  $products_price .= '<span typeof="gr:UnitPriceSpecification" about="#UnitPriceSpecification_'.$product_info['products_id'].'">';
			  $products_price .= '<span property="gr:hasCurrencyValue" datatype="xsd:float" content="'.number_format(($config['default_currency_code']?$currencies->get_value($config['default_currency_code']):1)*$currencies->calculate_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id'])), 2, '.', '').'">' . $currencies->display_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id'])) . '</span>';
			  $products_price .= '<span property="gr:hasUnitOfMeasurement" datatype="xsd:string" content="C62"></span>';
			  $products_price .= '<span property="gr:hasCurrency" datatype="xsd:string" content="'.($config['default_currency_code']?$config['default_currency_code']:($currencies->currencies[$currency]['symbol_left'].$currencies->currencies[$currency]['symbol_right'])).'"></span>';
			  $products_price .= '<span property="gr:valueAddedTaxIncluded" datatype="xsd:boolean" content="'.DISPLAY_PRICE_WITH_TAX.'"></span>';
			  // shall we display validfrom
			  if(!empty($offering_validfrom))
			  $products_price .= '<span property="gr:validFrom" datatype="xsd:dateTime" content="'.$offering_validfrom.'"></span>';
			  // shall we display validthrough (only, if validfrom is set)
			  if(!empty($offering_validfrom) && !empty($offering_validthrough))
			  $products_price .= '<span property="gr:validThrough" datatype="xsd:dateTime" content="'.$offering_validthrough.'"></span>';
			  $products_price .= '</span></s> ';
			  
			  // special price
			  $products_price .= '<span class="productSpecialPrice" rel="gr:hasPriceSpecification">';
			  $products_price .= '<span typeof="gr:UnitPriceSpecification" about="#UnitPriceSpecification_'.$product_info['products_id'].'_Special">';
			  $products_price .= '<span property="gr:hasCurrencyValue" datatype="xsd:float" content="'.number_format(($config['default_currency_code']?$currencies->get_value($config['default_currency_code']):1)*$currencies->calculate_price($new_price, tep_get_tax_rate($product_info['products_tax_class_id'])), 2, '.', '').'">' . $currencies->display_price($new_price, tep_get_tax_rate($product_info['products_tax_class_id'])) . '</span>';
			  $products_price .= '<span property="gr:hasUnitOfMeasurement" datatype="xsd:string" content="C62"></span>';
			  $products_price .= '<span property="gr:hasCurrency" datatype="xsd:string" content="'.($config['default_currency_code']?$config['default_currency_code']:($currencies->currencies[$currency]['symbol_left'].$currencies->currencies[$currency]['symbol_right'])).'"></span>';
			  $products_price .= '<span property="gr:valueAddedTaxIncluded" datatype="xsd:boolean" content="'.DISPLAY_PRICE_WITH_TAX.'"></span>';
			  $products_price .= '<span property="rdfs:comment" datatype="xsd:string" content="Special price offer"></span>';
			  $products_price .= '<span property="rdfs:label" datatype="xsd:string" content="Special price offer"></span>';
			  // shall we display validfrom
			  if(!empty($specials_values['validfrom'])) {
				  $products_price .= '<span property="gr:validFrom" datatype="xsd:dateTime" content="'.$format->w3cdate(strtotime($specials_values['validfrom'])).'"></span>';
				  // shall we display validthrough (only, if validfrom is set)
				  //if(!empty($specials_values['validfrom']) && $specials_values['validthrough']>$format->w3cdate(time()))
				  $products_price .= '<span property="gr:validThrough" datatype="xsd:dateTime" content="'.$specials_values['validthrough'].'"></span>';
			  }
			  $products_price .= '</span></span>';
		    }
	    
	    }
    }
    
    if(empty($new_price)) {
     /* no special price available for this product */
      $products_price = '<span rel="gr:hasPriceSpecification">';
	  $products_price .= '<span typeof="gr:UnitPriceSpecification" about="#UnitPriceSpecification_'.$product_info['products_id'].'">';
	  $products_price .= '<span property="gr:hasCurrencyValue" datatype="xsd:float" content="'.number_format(($config['default_currency_code']?$currencies->get_value($config['default_currency_code']):1)*$currencies->calculate_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id'])), 2, '.', '').'">' . $currencies->display_price($product_info['products_price'], tep_get_tax_rate($product_info['products_tax_class_id'])) . '</span>';
	  $products_price .= '<span property="gr:hasUnitOfMeasurement" datatype="xsd:string" content="C62"></span>';
	  $products_price .= '<span property="gr:hasCurrency" datatype="xsd:string" content="'.($config['default_currency_code']?$config['default_currency_code']:($currencies->currencies[$currency]['symbol_left'].$currencies->currencies[$currency]['symbol_right'])).'"></span>';
	  $products_price .= '<span property="gr:valueAddedTaxIncluded" datatype="xsd:boolean" content="'.DISPLAY_PRICE_WITH_TAX.'"></span>';
	  // shall we display validfrom
	  if(!empty($offering_validfrom))
	  $products_price .= '<span property="gr:validFrom" datatype="xsd:dateTime" content="'.$offering_validfrom.'"></span>';
	  // shall we display validthrough (only, if validfrom is set)
	  if(!empty($offering_validfrom) && !empty($offering_validthrough))
	  $products_price .= '<span property="gr:validThrough" datatype="xsd:dateTime" content="'.$offering_validthrough.'"></span>';
	  $products_price .= '</span></span>';
    }

    if (tep_not_null($product_info['products_model'])) {
      $products_name = $product_info['products_name'] . '<br><span class="smallText">[' . $product_info['products_model'] . ']</span>';
    } else {
      $products_name = $product_info['products_name'];
    }
    
?>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0" typeof="gr:Offering" about="#Offering_<?php echo $HTTP_GET_VARS['products_id']; ?>">
          <tr>
			<td class="pageHeading" valign="top" rel="gr:includesObject"><?php echo $products_name; ?><span typeof="gr:TypeAndQuantityNode" about="#TypeAndQuantityNode_<?php echo $product_info['products_id']; ?>"><span property="gr:amountOfThisGood" datatype="xsd:float" content="<?php echo /*$product_info['products_quantity']*/"1.0"; ?>"></span><span property="gr:hasUnitOfMeasurement" datatype="xsd:string" content="C62"></span><span rel="gr:typeOfGood" resource="#ProductOrServiceInstance_<?php echo $product_info['products_id']; ?>"></span></td>
            <td class="pageHeading" align="right" valign="top">
				<?php
					/* products price, special price */
					echo $products_price;
	           		/*validfrom and validthrough, if they exist */
					if(!empty($offering_validfrom)): ?>
				<span property="gr:validFrom" datatype="xsd:dateTime" content="<?php echo $offering_validfrom; ?>"></span>
				<?php endif; if(!empty($offering_validfrom) && !empty($offering_validthrough)): ?>
				<span property="gr:validThrough" datatype="xsd:dateTime" content="<?php echo $offering_validthrough; ?>"></span>
				<?php endif;
	// get this array from the configuration.php
	if($elements_arr != NULL)
		foreach($elements_arr as $element=>$upper) {
			switch($element) {
				case 'BusinessFunction':
					create_properties_rdfa('has'.$element, $upper);
					break;
				case 'PaymentMethod':
					create_properties_rdfa('accepted'.$element.'s', $upper);
					break;
				case 'DeliveryMethod':
					create_properties_rdfa('available'.$element.'s', $upper);
					break;
				case 'BusinessEntityType':
					create_properties_rdfa('eligibleCustomerTypes', $upper);
					break;
				case 'eligibleRegions':
					create_properties_rdfa($element, $upper, false);
			}
		}
	for($i=0; $i<count($warranty_promise); $i++):
?>
		<span rel="gr:hasWarrantyPromise" resource="#WarrantyPromise_<?php echo urlencode($warranty_promise[$i]['val2']); ?>"></span>
<?php endfor; ?>
			</td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td class="main" about="#ProductOrServiceModel_<?php echo $HTTP_GET_VARS['products_id']; ?>" typeof="<?php echo $product_info['categories_id']!=0?'self:Category_'.$product_info['categories_id'].' ':'' ?>gr:ProductOrServiceModel">
<?php
    if (tep_not_null($product_info['products_image'])) {
?>
          <table border="0" cellspacing="0" cellpadding="2" align="right">
            <tr>
              <td align="center" class="smallText">
<script language="javascript"><!--
document.write('<?php echo '<a href="javascript:popupWindow(\\\'' . tep_href_link(FILENAME_POPUP_IMAGE, 'pID=' . $product_info['products_id']) . '\\\')">' . tep_image(DIR_WS_IMAGES . $product_info['products_image'], addslashes($product_info['products_name']), SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="5" vspace="5"') . '<br>' . TEXT_CLICK_TO_ENLARGE . '</a>'; ?>');
//--></script>
<noscript>
<?php echo '<a href="' . tep_href_link(DIR_WS_IMAGES . $product_info['products_image']) . '" target="_blank">' . tep_image(DIR_WS_IMAGES . $product_info['products_image'], $product_info['products_name'], SMALL_IMAGE_WIDTH, SMALL_IMAGE_HEIGHT, 'hspace="5" vspace="5"') . '<br>' . TEXT_CLICK_TO_ENLARGE . '</a>'; ?>
</noscript>
              </td>
            </tr>
          </table>
<?php
    }
    	/* (full) product description */
?>
          <p property="rdfs:comment" datatype="xsd:string"><?php echo $product_info['products_description']; ?></p><span property="rdfs:label" datatype="xsd:string" content="<?php echo $product_info['products_name']; ?> (ProductOrServiceModel)"></span><span rel="gr:hasManufacturer" resource="#Manufacturer_<?php echo $product_info['manufacturers_id']; ?>"></span>
<?php if($product_info['products_url']): ?>
		  <a rel="rdfs:seeAlso" href="<?php echo $format->url($product_info['products_url']); ?>"></a>
<?php endif; if($product_info['products_weight']!=0): // hasProductWeight property
?>
		<span rel="self:hasProductWeight">
			<span typeof="gr:QuantitativeValueFloat" about="#QuantitativeValueFloat_ProductWeight_<?php echo $product_info['products_id']; ?>">
				<span property="rdfs:label" datatype="xsd:string" content="Weight description for <?php echo $product_info['products_name'] ?>"></span>
				<span property="rdfs:comment" datatype="xsd:string" content="<?php echo strip_tags($product_info['products_name']) ?> has a weight of <?php echo $format->curr_format($product_info['products_weight'])." ".$config['default_uom_weight_name'] ?>"></span>
				<span property="gr:hasValueFloat" datatype="xsd:float" content="<?php echo $format->curr_format($product_info['products_weight']); ?>"></span>
				<span property="gr:hasUnitOfMeasurement" datatype="xsd:string" content="<?php echo $config['default_uom_weight_id'] ?>"></span>
			</span>
		</span>
<?php endif; ?>
<?php
    $products_attributes_query = tep_db_query("select count(*) as total from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_ATTRIBUTES . " patrib where patrib.products_id='" . (int)$HTTP_GET_VARS['products_id'] . "' and patrib.options_id = popt.products_options_id and popt.language_id = '" . (int)$languages_id . "'");
    $products_attributes = tep_db_fetch_array($products_attributes_query);
    if ($products_attributes['total'] > 0) {
?>
          <table border="0" cellspacing="0" cellpadding="2">
            <tr>
              <td class="main" colspan="2"><?php echo TEXT_PRODUCT_OPTIONS; ?></td>
            </tr>
<?php
      $products_options_name_query = tep_db_query("select distinct popt.products_options_id, popt.products_options_name from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_ATTRIBUTES . " patrib where patrib.products_id='" . (int)$HTTP_GET_VARS['products_id'] . "' and patrib.options_id = popt.products_options_id and popt.language_id = '" . (int)$languages_id . "' order by popt.products_options_name");
      while ($products_options_name = tep_db_fetch_array($products_options_name_query)) {
        $products_options_array = array();
        $products_options_query = tep_db_query("select pov.products_options_values_id, pov.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_ATTRIBUTES . " pa, " . TABLE_PRODUCTS_OPTIONS_VALUES . " pov where pa.products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "' and pa.options_id = '" . (int)$products_options_name['products_options_id'] . "' and pa.options_values_id = pov.products_options_values_id and pov.language_id = '" . (int)$languages_id . "'");
        while ($products_options = tep_db_fetch_array($products_options_query)) {
          $products_options_array[] = array('id' => $products_options['products_options_values_id'], 'text' => $products_options['products_options_values_name']);
          if ($products_options['options_values_price'] != '0') {
            $products_options_array[sizeof($products_options_array)-1]['text'] .= ' (' . $products_options['price_prefix'] . $currencies->display_price($products_options['options_values_price'], tep_get_tax_rate($product_info['products_tax_class_id'])) .') ';
          }
        }

        if (isset($cart->contents[$HTTP_GET_VARS['products_id']]['attributes'][$products_options_name['products_options_id']])) {
          $selected_attribute = $cart->contents[$HTTP_GET_VARS['products_id']]['attributes'][$products_options_name['products_options_id']];
        } else {
          $selected_attribute = false;
        }
?>
            <tr>
              <td class="main"><?php echo $products_options_name['products_options_name'] . ':'; ?></td>
              <td class="main"><?php echo tep_draw_pull_down_menu('id[' . $products_options_name['products_options_id'] . ']', $products_options_array, $selected_attribute); ?></td>
            </tr>
<?php
      }
?>
          </table>
<?php
    }
?>
        </td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
    $reviews_query = tep_db_query("select count(*) as count from " . TABLE_REVIEWS . " where products_id = '" . (int)$HTTP_GET_VARS['products_id'] . "'");
    $reviews = tep_db_fetch_array($reviews_query);
    if ($reviews['count'] > 0) {
?>
      <tr>
        <td class="main"><?php echo TEXT_CURRENT_REVIEWS . ' ' . $reviews['count']; ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
    }

    if (tep_not_null($product_info['products_url'])) {
?>
      <tr>
        <td class="main"><?php echo sprintf(TEXT_MORE_INFORMATION, tep_href_link(FILENAME_REDIRECT, 'action=url&goto=' . urlencode($product_info['products_url']), 'NONSSL', true, false)); ?></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
<?php
    }

    if ($product_info['products_date_available'] > date('Y-m-d H:i:s')) {
?>
      <tr>
        <td align="center" class="smallText"><?php echo sprintf(TEXT_DATE_AVAILABLE, tep_date_long($product_info['products_date_available'])); ?></td>
      </tr>
<?php
    } else {
?>
      <tr>
        <td align="center" class="smallText"><?php echo sprintf(TEXT_DATE_ADDED, tep_date_long($product_info['products_date_added'])); ?></td>
      </tr>
<?php
    }
?>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
          <tr class="infoBoxContents">
            <td><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
                <td class="main"><?php echo '<a href="' . tep_href_link(FILENAME_PRODUCT_REVIEWS, tep_get_all_get_params()) . '">' . tep_image_button('button_reviews.gif', IMAGE_BUTTON_REVIEWS) . '</a>'; ?></td>
                <td class="main" align="right"><?php echo tep_draw_hidden_field('products_id', $product_info['products_id']) . tep_image_submit('button_in_cart.gif', IMAGE_BUTTON_IN_CART); ?></td>
                <td width="10"><?php echo tep_draw_separator('pixel_trans.gif', '10', '1'); ?></td>
              </tr>
            </table></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><?php echo tep_draw_separator('pixel_trans.gif', '100%', '10'); ?></td>
      </tr>
      <tr>
        <td>
<?php
    if ((USE_CACHE == 'true') && empty($SID)) {
      echo tep_cache_also_purchased(3600);
    } else {
      include(DIR_WS_MODULES . FILENAME_ALSO_PURCHASED_PRODUCTS);
    }
  }
?>
        </td>
      </tr>
    </table></form></td>
<!-- body_text_eof //-->
    <td width="<?php echo BOX_WIDTH; ?>" valign="top"><table border="0" width="<?php echo BOX_WIDTH; ?>" cellspacing="0" cellpadding="2">
<!-- right_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_right.php'); ?>
<!-- right_navigation_eof //-->
    </table></td>
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
