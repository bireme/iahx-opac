<?php

$query = trim($_REQUEST['query']);
$query = str_replace(" ","+",$query);
$query = $query . "*";


$service_url = "http://srv.bvsalud.org/decsQuickTerm/search?query=" . $query . "&lang=pt";
	
$service_response = file_get_contents($service_url);
$xml = simplexml_load_string($service_response);

$suggestions = array();
$data = array();
foreach ($xml->Result->item as $item ) {
	$attr = $item->attributes();
	$suggestions[] = (string)$attr['term']; 
	$data[] = (string)$attr['id'];
}


$result = array(
			'query' => $_REQUEST['query'], 
			'suggestions' => $suggestions, 
			'data' => $data,
			);
$result_json = json_encode($result);

header("Content-type: text/plain; charset: utf8");
echo $result_json;
?>