<?php

function get_ini($did) {
    $query = "
    SELECT * 
    FROM params
    WHERE device_id = $did
    ";

    $ini = null;
    $r = sql_query($query);
    while ($row = mysql_fetch_array($r)) {
        $ini[$row['object']][$row['attribute']] = $row['value'];
    }

    return $ini;
}

function getLines($url, $post = '') {
    global $username, $password;

    $process = curl_init() or die ("Init Error");                                          
    if ($post)  {
	$flds = http_build_query($post);
        curl_setopt($process, CURLOPT_POSTFIELDS, $flds);
	curl_setopt($process, CURLOPT_POST, count($post));
    }

    // curl_setopt($process, CURLOPT_USERPWD, "$username:$password");
    curl_setopt($process, CURLOPT_URL, $url);
    curl_setopt($process, CURLOPT_HEADER, 0);
    curl_setopt($process, CURLOPT_TIMEOUT, 30);
    curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($process, CURLOPT_SSL_VERIFYPEER, FALSE);

    $return = curl_exec($process) or curl_error($process);

    // $lines = explode("\n", $return);

    return $return;
}


function xmlParse($lines) {
    $parser = xml_parser_create('');
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, trim($lines), $xml_values);
    xml_parser_free($parser);
    foreach ($xml_values as $data) {
	if (!empty($data['value']))
	    $vars[$data['tag']] = $data['value'];
    }
    return $vars;
}

function xmlParse2($lines) {
    $parser = xml_parser_create('');
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, trim($lines), $xml_values);
    xml_parser_free($parser);

    return $xml_values;
}


?>
