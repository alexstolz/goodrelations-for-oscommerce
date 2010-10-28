<?php
/* function to configure the global variables of the master data */
function global_var_config($search, $default, $units='days') {
	global $format;
	
?>
	<h3><?php echo $search; ?></h3>
<?php
	$definition_id = NULL;
	$definition_query = tep_db_query("select definition_id from ".TABLE_SEMANTICWEB_DEFINITION." where definition_name='$search'");
	if(tep_db_num_rows($definition_query) == 0) {
		tep_db_query("insert into ".TABLE_SEMANTICWEB_DEFINITION." (definition_name) values ('$search')");
	}
	if($definition_row = tep_db_fetch_array($definition_query)) {
		$definition_id = $definition_row['definition_id'];
		if(isset($_POST[$search])) {
			tep_db_query("delete from ".TABLE_SEMANTICWEB_DEFINITION_VALUES." where definition_id='$definition_id'");
			$insert_values = "insert into ".TABLE_SEMANTICWEB_DEFINITION_VALUES." (definition_values_id, definition_id, definition_value) values ";
			$insert_values .= "('1', '$definition_id', '".($format->is_float($default)?$format->curr_format($_POST[$search]):$_POST[$search])."'), ";		 	
			$insert_values = substr($insert_values, 0, -2);
			tep_db_query($insert_values);
		}
	}

	$special_query = tep_db_query("select t2.definition_value from ".TABLE_SEMANTICWEB_DEFINITION." t1, ".TABLE_SEMANTICWEB_DEFINITION_VALUES." t2 where t1.definition_id=t2.definition_id and definition_name='$search'");
	if($special_row = tep_db_fetch_array($special_query)) {
?>
	<input type="text" name="<?php echo $search; ?>" value="<?php echo $special_row['definition_value']; ?>"/> <?php echo $units; ?> <i>[default=<?php echo $default; ?>]</i>
<?php
	}
	else {
?>
	<input type="text" name="<?php echo $search; ?>" value="<?php echo $default; ?>"/> <?php echo $units; ?> <i>[default=<?php echo $default; ?>]</i>
<?php
	}
} // end global_var_config

/* function to configure the master data */
function master_config($search, $description, $use_sparql=true) {
	global $store, $config;
	
	if($use_sparql):
	// sparql part
	$search_id = strtolower($search);
	$q = '
	PREFIX gr: <http://purl.org/goodrelations/v1#> .
	SELECT ?'.$search_id.' WHERE {
		{ ?'.$search_id.' a gr:'.$search.' } UNION
		{ ?'.$search_id.' a ?subclass .
		  ?subclass rdfs:subClassOf gr:'.$search.' . }
	}
	';
	endif;
	
	// determine definition_id
	$definition_id = NULL;
	$definition_query = tep_db_query("select definition_id from ".TABLE_SEMANTICWEB_DEFINITION." where definition_name='$search'");
	if(tep_db_num_rows($definition_query) == 0) {
		tep_db_query("insert into ".TABLE_SEMANTICWEB_DEFINITION." (definition_name) values ('$search')");
	}
	if($definition_row = tep_db_fetch_array($definition_query)) {
		$definition_id = $definition_row['definition_id'];
		if($_POST[$search]) {
			tep_db_query("delete from ".TABLE_SEMANTICWEB_DEFINITION_VALUES." where definition_id='$definition_id'");
			$insert_values = "insert into ".TABLE_SEMANTICWEB_DEFINITION_VALUES." (definition_values_id, definition_id, definition_value) values ";
			foreach($_POST[$search] as $key=>$value) {
			 	$insert_values .= "('".($key+1)."', '$definition_id', '$value'), ";		 	
			}
			$insert_values = substr($insert_values, 0, -2);
			tep_db_query($insert_values);
		}
	}
	
	$mark_selected = tep_db_query("select t2.definition_value from ".TABLE_SEMANTICWEB_DEFINITION." t1, ".TABLE_SEMANTICWEB_DEFINITION_VALUES." t2 where t1.definition_id=t2.definition_id and definition_name='$search'");
	while($mark_selected_row = tep_db_fetch_array($mark_selected)) {
		$items_marked[$mark_selected_row['definition_value']] = 1;
	}
?>
	<div class="outer">
	<h2><?php echo $search; ?></h2>
	<div class="inner"><?php echo $description; ?></div>
<?php if(eregi('(eligibleLanguages|eligibleCurrencies|BusinessFunction|WeightUnitOfMeasurement)', $search)): ?>
	<select name="<?php echo $search; ?>[]">
<?php else: ?>
	<select name="<?php echo $search; ?>[]" size="5" multiple="multiple">
<?php
	endif;
	if($use_sparql) { // sparql
		if ($rows = $store->query($q, 'rows')) {
			foreach ($rows as $row) {
				$item = ereg_replace('^.*#', '', $row[$search_id]);
				if(eregi('WarrantyScope', $search)) $item_arr[] = $item;
?>
		<option value="<?php echo $item; ?>"<?php echo $items_marked[$item]?' selected="selected"':''; ?>><?php echo $item; ?></option>
<?php
			} 
		}
	}
	else if(eregi('eligibleRegions', $search)) { // mysql, not sparql
		if($regions_query = tep_db_query("select countries_name as name, countries_iso_code_2 as code from ".TABLE_COUNTRIES)){  				
		 	while($rows = tep_db_fetch_array($regions_query)) {
		 	 	$cname = $rows['name'];
		 	 	$ccode = $rows['code'];
?>
		<option value="<?php echo $ccode; ?>"<?php echo $items_marked[$ccode]?' selected="selected"':''; ?>><?php echo $cname; ?></option>
<?php
			}
		}
	}
	else if(eregi('eligibleLanguages', $search)) { // mysql, not sparql
		if($languages_query = tep_db_query("select name, code, languages_id from ".TABLE_LANGUAGES)){  				
		 	while($rows = tep_db_fetch_array($languages_query)) {
		 	 	$lname = $rows['name'];
		 	 	$lcode = $rows['code'];
		 	 	$lid = $rows['languages_id'];
?>
		<option value="<?php echo $lid.','.$lcode; ?>"<?php echo $items_marked[$lid.','.$lcode]?' selected="selected"':''; ?>><?php echo $lname.' ('.$lcode.')'; ?></option>
<?php
			}
		}
	}
	else if(eregi('eligibleCurrencies', $search)) { // mysql, not sparql
		if($currencies_query = tep_db_query("select code, symbol_left, symbol_right, concat(symbol_left, symbol_right) as symbol, value from ".TABLE_CURRENCIES)){  				
		 	while($rows = tep_db_fetch_array($currencies_query)) {
		 	 	$ccode = $rows['code'];
		 	 	$csym = $rows['symbol'];
		 	 	$csyml = $rows['symbol_left'];
		 	 	$csymr = $rows['symbol_right'];
		 	 	$cval = $rows['value'];
?>
		<option value="<?php echo $ccode.','.$csym.','.$csyml.','.$csymr.','.$cval; ?>"<?php echo $items_marked[$ccode.','.$csym.','.$csyml.','.$csymr.','.$cval]?' selected="selected"':''; ?>><?php echo $ccode.' ('.$csym.')'; ?></option>
<?php
			}
		}
	}
	else if(eregi('WeightUnitOfMeasurement', $search)) { // mysql, not sparql
		$codes = array(
			"MGM" => "MGM (milligram)",
			"CGM" => "CGM (centigram)",
			"DG" => "DG (decigram)",
			"GRM" => "GRM (gram)",
			"DJ" => "DJ (decagram)",
			"KGM" => "KGM (kilogram)",
			"TNE" => "TNE (tonne)",
			"LBR" => "LBR (pound)",
			"GRN" => "GRN (grain)",
			"ONZ" => "ONZ (ounce)"
		);
		foreach($codes as $code => $description) {
?>
		<option value="<?php echo $description; ?>"<?php echo $items_marked[$description]?' selected="selected"':''; ?>><?php echo $description; ?></option>
<?php
		}
	}
?>
	</select><br/>
<?php
	if(count($item_arr)>0) {
	 	$unit = 'days';
	 	switch($search) {
			case 'WarrantyScope':
				$extra_h2 = "Specify Warranty Duration for ".$search;
				$default = 24;
				$unit = 'months';
			default:
		}
		if($extra_h2):
?>
	<br/><h3 class="extra"><?php echo $extra_h2; ?></h3>
<?php
		endif;
		foreach($item_arr as $item) {
		 	global_var_config($item, $default, $unit);
		}
	}
	?>
	</div>
<?php
} // end master_config

/* function that dynamically creates properties */
function create_properties($element, $definition, $is_objectproperty=true) {
 	global $namespaces, $format;
 	$rdf = "";
 	
	if(defined($definition)):
		if($is_objectproperty): //object property
			foreach (explode(",", constant($definition)) as $item) {
			    if(trim($item)!="")
				    $rdf .= $format->indent(2)."<gr:$element rdf:resource=\"".$namespaces['gr'].trim($item)."\"/>";		
			}
		else: //datatype property
			foreach (explode(",", constant($definition)) as $item) {
			    if(trim($item)!="")
				    $rdf .= $format->indent(2)."<gr:$element rdf:datatype=\"".$namespaces['xsd']."string\">".trim($item)."</gr:$element>";		
			}
		endif;
	endif;
	
	return $rdf;
} // end create_properties

/* function that dynamically creates properties for RDFa */
function create_properties_rdfa($element, $definition, $is_objectproperty=true) {
 	global $namespaces;
 	
	if(defined($definition)):
		if($is_objectproperty): //object property
			foreach (explode(",", constant($definition)) as $item) {
			    if(trim($item)!="") {
?>
		<span rel="gr:<?php echo $element; ?>" resource="<?php echo $namespaces['gr'].trim($item); ?>"></span>
<?php			
			    }
			}
		else: //datatype property
			foreach (explode(",", constant($definition)) as $item) {
			    if(trim($item)!="") {
?>
		<span property="gr:<?php echo $element; ?>" datatype="xsd:string" content="<?php echo trim($item); ?>"></span>
<?php			
			    }
			}
		endif;
	endif;
} // end create_properties
?>