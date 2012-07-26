<?php

require_once 'lib/class/dia.class.php';

use Symfony\Component\HttpFoundation\Request;

$app->match('/', function (Request $request) use ($app, $DEFAULT_PARAMS, $config) {

    
    $params = array_merge(
        $app['request']->request->all(),
        $app['request']->query->all()
    );

    $site = $DEFAULT_PARAMS['defaultSite'];
    if(isset($params['site']) and $params['site'] != "") {
        $site = $params['site'];
    } 

    $col = $DEFAULT_PARAMS['defaultCollection'];
    if(isset($params['col']) and $params['col'] != "") {
        $col = $params['col'];
    }

    $fb = "";
    if(isset($params['fb']) and $params['fb'] != "") {
        $fb = $params['fb'];
    }

    $count = $config->documents_per_page;
    if(isset($params['count'])and $params['col'] != "") {
        $count = $params['count'];
    }

    $output = "site";
    if(isset($params['output']) and $params['output'] != "") {
        $output = $params['output'];
    }

    $lang = $DEFAULT_PARAMS['lang'];
    if(isset($params['lang']) and $params['lang'] != "") {
        $lang = $params['lang'];
    }

    $q = "";
    if(isset($params['q']) and $params['lang'] != "") {
        $q = $params['q'];
    }
    
    //get sort field to apply
    if(isset($params['sort']) and $params['sort'] != "") {
        $sort = getSortValue($col, $params['sort']);     
    
    //get default sort
    } else {
        $sort = getDefaultSort($col, $q);
    }

    $index = "";
    if(isset($params['index']) and $params['index'] != "") {
        $index = $params['index'];
    }

    $from = 0;
    if(isset($params['from']) and $params['from'] != "") {
        $from = $params['from'];
    }

    $filter = array();
    if(isset($params['filter']) and $params['filter'] != "Array") {
        $filter = $params['filter'];
    }

    foreach($filter as $key => $value) {
        if($value == "Array" or $value == "Array#" or $value == "") {
            unset($filter[$key]);
        }

        $filter[$key] = str_replace("#", "", $value);
    }

    $filters = array();
    foreach($filter as $item) {
        $item = explode(":", $item);
        $filters[$item[0]][] = str_replace('"', '', $item[1]);
    }

    $filter_search = $filter;

    $debug = true;
    
    $dia = new Dia($site, $col, $count, $output, $lang);
    $dia->setParam('fb', $fb);

    $dia_response = $dia->search($q, $index, $filter_search, $from);
    $response = json_decode($dia_response, true);

    $output_array = array();
    $output_array['filters'] = $filters;
    $output_array['lang'] = $lang;
    $output_array['col'] = $col;
    $output_array['site'] = $site;
    $output_array['params'] = $params;
    $output_array['total'] = $response['diaServerResponse'][0]['response']['numFound'];
    $output_array['docs'] = $response['diaServerResponse'][0]['response']['docs'];
    $output_array['clusters'] = $response['diaServerResponse'][0]['facet_counts']['facet_fields'];
    $output_array['texts'] = parse_ini_file(__DIR__ . "/../languages/" . $lang . "/texts.ini", true);
    $output_array['config'] = $config;
    
    // output
    switch($output) {
        case "xml": 
        case "sol":
            header("Content-type: text/xml");
            print $dia_response;
            break;
        default: 
            return $app['twig']->render('results.html', $output_array);
            break;
    }
    
});


?>