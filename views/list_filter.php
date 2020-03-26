<?php

use Symfony\Component\HttpFoundation\Request;

$app->get('list-filter/{filter_id}', function (Request $request, $filter_id) use ($app, $DEFAULT_PARAMS, $config) {
    global $texts;

    $params = array_merge(
        $app['request']->request->all(),
        $app['request']->query->all()
    );

    define('MAX_LIMIT', 999999);
    $collectionData = $DEFAULT_PARAMS['defaultCollectionData'];
    $site = $DEFAULT_PARAMS['defaultSite'];
    $col = $DEFAULT_PARAMS['defaultCollection'];
    $initial_filter = html_entity_decode($collectionData->initial_filter);
    $filter = array();

    $lang = $DEFAULT_PARAMS['lang'];
    if(isset($params['lang']) and $params['lang'] != "") {
        $lang = $params['lang'];
    }

    $q = $request->get("q");
    $index = $request->get("index");
    $filter_param = $request->get("filter");
    if ( !empty($filter_param) ){
        $filter = $filter_param;
    }

    $from = ($request->get("from") ? strval($request->get("from")) : 0);
    $limit = ($request->get("limit") ? strval($request->get("limit")) + $from : MAX_LIMIT);

    // filter browse param (ex. au:10)
    $fb = $filter_id . ":" . $limit;

    $dia = new Dia($site, $col, 1, 'site', $lang);
    $dia->setParam('fb', $fb);
    $dia->setParam('initial_filter', $initial_filter );

    $dia_response = $dia->search($q, $index, $filter);
    $result = json_decode($dia_response, true);

    $filter_list = $result['diaServerResponse'][0]['facet_counts']['facet_fields'][$filter_id];

    if ($from != 0){
        $filter_list = array_slice($filter_list, $from);
    }

    //print_r($filter_list);

    // translation file
    $texts = parse_ini_file(TRANSLATE_PATH . $lang . "/texts.ini", true);

    // output vars
    $output_array = array();
    $output_array['lang'] = $lang;
    $output_array['config'] = $config;
    $output_array['texts'] = $texts;
    $output_array['filter_id'] = $filter_id;
    $output_array['filter_list'] = $filter_list;

    if ($filter_list){
        return $app['twig']->render(custom_template('list-filter.html'), $output_array);
    }else{
        return '';
    }

});

?>
