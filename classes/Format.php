<?php

/* Static class Format */
class Format {
 
 	var $nl = "\n"; //newline
 	var $indent = '  '; //indent space
	
	/* create w3c date format from any different time/date format */
	function w3cDate($time, $days_offset=0) {
		$time = $time+$days_offset*86400;
		if ((int) PHP_VERSION >= 5)
			return date('c', $time);
		else {
			$offset = date('O', $time);
			return date('Y-m-d\TH:i:s', $time).substr($offset, 0, 3).':'.substr($offset, -2);
		}
	}
	
	/* create indent after newline with deepness "times" */
	function indent($times) {
	 	$r = $this->nl;
		for($i=0; $i<$times; $i++)
			$r .= $this->indent;
		return $r;
	}
	
	/* extract the first occurrence of a number in a string */
	function extract_numeric($separator, $extract_str, $extract_num) {
		$str_arr = explode($separator, $extract_str);
		$extract_str = '';
		$first = true;
		foreach ($str_arr as $elem) {
			if(is_numeric($elem) && $first) {
				$first = false;
				$extract_num = $elem;
			}
			else {
				$extract_str .= $elem;
			}	
		}
	}
	
	/* translate html entity references into respective xml-understood entities */
	function xml_character_encode($string, $trans='') {
		$trans = array(
			'&apos;'=>'&#39;', '&minus;'=>'&#45;', '&circ;'=>'&#94;', '&tilde;'=>'&#126;',
			'&Scaron;'=>'&#138;', '&lsaquo;'=>'&#139;', '&OElig;'=>'&#140;', '&lsquo;'=>'&#145;',
			'&rsquo;'=>'&#146;', '&ldquo;'=>'&#147;', '&rdquo;'=>'&#148;', '&bull;'=>'&#149;',
			'&ndash;'=>'&#150;', '&mdash;'=>'&#151;', '&tilde;'=>'&#152;', '&trade;'=>'&#153;',
			'&scaron;'=>'&#154;', '&rsaquo;'=>'&#155;', '&oelig;'=>'&#156;', '&Yuml;'=>'&#159;',
			'&yuml;'=>'&#255;', '&OElig;'=>'&#338;', '&oelig;'=>'&#339;', '&Scaron;'=>'&#352;',
			'&scaron;'=>'&#353;', '&Yuml;'=>'&#376;', '&fnof;'=>'&#402;', '&circ;'=>'&#710;',
			'&tilde;'=>'&#732;', '&Alpha;'=>'&#913;', '&Beta;'=>'&#914;', '&Gamma;'=>'&#915;',
			'&Delta;'=>'&#916;', '&Epsilon;'=>'&#917;', '&Zeta;'=>'&#918;', '&Eta;'=>'&#919;',
			'&Theta;'=>'&#920;', '&Iota;'=>'&#921;', '&Kappa;'=>'&#922;', '&Lambda;'=>'&#923;',
			'&Mu;'=>'&#924;', '&Nu;'=>'&#925;', '&Xi;'=>'&#926;', '&Omicron;'=>'&#927;', '&Pi;'=>'&#928;',
			'&Rho;'=>'&#929;', '&Sigma;'=>'&#931;', '&Tau;'=>'&#932;', '&Upsilon;'=>'&#933;',
			'&Phi;'=>'&#934;', '&Chi;'=>'&#935;', '&Psi;'=>'&#936;', '&Omega;'=>'&#937;','&alpha;'=>'&#945;',
			'&beta;'=>'&#946;', '&gamma;'=>'&#947;', '&delta;'=>'&#948;', '&epsilon;'=>'&#949;',
			'&zeta;'=>'&#950;', '&eta;'=>'&#951;', '&theta;'=>'&#952;', '&iota;'=>'&#953;', '&kappa;'=>'&#954;',
			'&lambda;'=>'&#955;', '&mu;'=>'&#956;', '&nu;'=>'&#957;', '&xi;'=>'&#958;', '&omicron;'=>'&#959;',
			'&pi;'=>'&#960;', '&rho;'=>'&#961;', '&sigmaf;'=>'&#962;', '&sigma;'=>'&#963;', '&tau;'=>'&#964;',
			'&upsilon;'=>'&#965;', '&phi;'=>'&#966;', '&chi;'=>'&#967;', '&psi;'=>'&#968;', '&omega;'=>'&#969;',
			'&thetasym;'=>'&#977;', '&upsih;'=>'&#978;', '&piv;'=>'&#982;', '&ensp;'=>'&#8194;',
			'&emsp;'=>'&#8195;', '&thinsp;'=>'&#8201;', '&zwnj;'=>'&#8204;', '&zwj;'=>'&#8205;',
			'&lrm;'=>'&#8206;', '&rlm;'=>'&#8207;', '&ndash;'=>'&#8211;', '&mdash;'=>'&#8212;',
			'&lsquo;'=>'&#8216;', '&rsquo;'=>'&#8217;', '&sbquo;'=>'&#8218;', '&ldquo;'=>'&#8220;',
			'&rdquo;'=>'&#8221;', '&bdquo;'=>'&#8222;', '&dagger;'=>'&#8224;', '&Dagger;'=>'&#8225;',
			'&bull;'=>'&#8226;', '&hellip;'=>'&#8230;', '&permil;'=>'&#8240;', '&prime;'=>'&#8242;',
			'&Prime;'=>'&#8243;', '&lsaquo;'=>'&#8249;', '&rsaquo;'=>'&#8250;', '&oline;'=>'&#8254;',
			'&frasl;'=>'&#8260;', '&euro;'=>'&#8364;', '&image;'=>'&#8465;', '&weierp;'=>'&#8472;',
			'&real;'=>'&#8476;', '&trade;'=>'&#8482;', '&alefsym;'=>'&#8501;', '&larr;'=>'&#8592;',
			'&uarr;'=>'&#8593;', '&rarr;'=>'&#8594;', '&darr;'=>'&#8595;', '&harr;'=>'&#8596;',
			'&crarr;'=>'&#8629;', '&lArr;'=>'&#8656;', '&uArr;'=>'&#8657;', '&rArr;'=>'&#8658;',
			'&dArr;'=>'&#8659;', '&hArr;'=>'&#8660;', '&forall;'=>'&#8704;', '&part;'=>'&#8706;',
			'&exist;'=>'&#8707;', '&empty;'=>'&#8709;', '&nabla;'=>'&#8711;', '&isin;'=>'&#8712;',
			'&notin;'=>'&#8713;', '&ni;'=>'&#8715;', '&prod;'=>'&#8719;', '&sum;'=>'&#8721;',
			'&minus;'=>'&#8722;', '&lowast;'=>'&#8727;', '&radic;'=>'&#8730;', '&prop;'=>'&#8733;',
			'&infin;'=>'&#8734;', '&ang;'=>'&#8736;', '&and;'=>'&#8743;', '&or;'=>'&#8744;', '&cap;'=>'&#8745;',
			'&cup;'=>'&#8746;', '&int;'=>'&#8747;', '&there4;'=>'&#8756;', '&sim;'=>'&#8764;', '&cong;'=>'&#8773;',
			'&asymp;'=>'&#8776;', '&ne;'=>'&#8800;', '&equiv;'=>'&#8801;', '&le;'=>'&#8804;', '&ge;'=>'&#8805;',
			'&sub;'=>'&#8834;', '&sup;'=>'&#8835;', '&nsub;'=>'&#8836;', '&sube;'=>'&#8838;', '&supe;'=>'&#8839;',
			'&oplus;'=>'&#8853;', '&otimes;'=>'&#8855;', '&perp;'=>'&#8869;', '&sdot;'=>'&#8901;',
			'&lceil;'=>'&#8968;', '&rceil;'=>'&#8969;', '&lfloor;'=>'&#8970;', '&rfloor;'=>'&#8971;',
			'&lang;'=>'&#9001;', '&rang;'=>'&#9002;', '&loz;'=>'&#9674;', '&spades;'=>'&#9824;', '&clubs;'=>'&#9827;',
			'&hearts;'=>'&#9829;', '&diams;'=>'&#9830;'
			);
		$html_trans = get_html_translation_table(HTML_ENTITIES, ENT_QUOTES);
		foreach ($html_trans as $k=>$v)
			$trans[$v]= "&#".ord($k).";";

		return strtr($string, $trans);
	}
	
	/* tests, if string holds an integer */
	function is_int($string) {
		return ereg('^[-]?[0-9]+$', $string) === 1;
	}
	
	/* tests, if string holds a float */
	function is_float($string) {
	 	$string = ereg_replace(',', '.', $string);
		return ereg('^[-]?[0-9]*\.[0-9]+$', $string) === 1;
	}
	
	/* returns currency formatted string */
	function curr_format($string) {
		return number_format((float)$string, 2, '.', '');
	}
	
	/* returns an http-url, if http:// is missing */
	function url($bad_url) {
		$bad_url = ereg_replace("&[^amp;]", "&amp;", $bad_url);
		return 'http://'.ereg_replace('http://', '', $bad_url);
	}
}

?>