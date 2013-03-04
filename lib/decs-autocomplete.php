<?php

include('functions.php');

$query = trim($_REQUEST['query']);
$count = ( isset($_REQUEST['count']) ? intval($_REQUEST['count']) : 20 );
$query = str_replace(" ","+",$query);
$query = remove_accents($query);
$query = $query . "*";

$lang = trim($_REQUEST['lang']);

$service_url = "http://srv.bvsalud.org/decsQuickTerm/search?query=" . $query . "&lang=" . $lang;
	
$service_response = file_get_contents($service_url);
$xml = simplexml_load_string($service_response);


$descriptors = array();
$i = 0;

foreach ($xml->Result->item as $item ) {
    $attr = $item->attributes();
    $descriptors[] = array('name' => (string)$attr['term'], 'id' => (string)$attr['id']);     
    $i++;
    if ($i >= $count){
        break;
    }
}

$result = array(
			'query' => $_REQUEST['query'], 
            'descriptors' => $descriptors, 
			);
$result_json = json_encode($result);

header("Content-type: text/plain; charset: utf8");
echo $result_json;
?>