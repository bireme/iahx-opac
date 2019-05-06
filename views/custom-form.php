<?php

use Symfony\Component\HttpFoundation\Request;

$app->get('form/{form_id}', function (Request $request, $form_id) use ($app, $DEFAULT_PARAMS, $config) {
    global $texts;

    $params = array_merge(
        $app['request']->request->all(),
        $app['request']->query->all()
    );

    $lang = $DEFAULT_PARAMS['lang'];
    if(isset($params['lang']) and $params['lang'] != "") {
        $lang = $params['lang'];
    }

    // Form configuration
    $form_json = file_get_contents(PATH_DATA . 'config/form-' . $form_id . '.json');
    $form_config = json_decode($form_json, true);

    /*
    $collectionData = $DEFAULT_PARAMS['defaultCollectionData'];
    $site = $DEFAULT_PARAMS['defaultSite'];
    $col = $DEFAULT_PARAMS['defaultCollection'];
    $filter = array();


    $q = $request->get("q");
    $index = $request->get("index");
    $filter_param = $request->get("filter");
    if ( !empty($filter_param) ){
        $filter = $filter_param;
    }
    $limit = $request->get("limit");
    // if not set count list all itens from filter
    if ($limit == ''){
        $limit = 999999;
    }
    // filter browse param (ex. au:10)
    $fb = $filter_id . ":" . $limit;

    $dia = new Dia($site, $col, 1, 'site', $lang);
    $dia->setParam('fb', $fb);

    $dia_response = $dia->search($q, $index, $filter);
    $result = json_decode($dia_response, true);

    $filter_list = $result['diaServerResponse'][0]['facet_counts']['facet_fields'][$filter_id];

    */
    $site = $DEFAULT_PARAMS['defaultSite'];
    $col = $DEFAULT_PARAMS['defaultCollection'];

    $q = $request->get("q");
    $index = $request->get("index");
    $filter = $request->get("filter");
    if ( !isset($filter) ){
        $filter = [];
    }

    $dia = new Dia($site, $col, 1, 'site', $lang);

    $dia_response = $dia->search($q, $index, $filter);
    $result = json_decode($dia_response, true);

    $cluster_list = $result['diaServerResponse'][0]['facet_counts']['facet_fields'];

    // translation file
    $texts = parse_ini_file(TRANSLATE_PATH . $lang . "/texts.ini", true);

    // output vars
    $output_array = array();
    $output_array['lang'] = $lang;
    $output_array['config'] = $config;
    $output_array['q'] = $q;
    $output_array['filter'] = $filter;    
    $output_array['form'] = $form_config;
    $output_array['cluster_list'] = $cluster_list;
    $output_array['texts'] = $texts;

    return $app['twig']->render(custom_template('custom-form.html'), $output_array);

});

?>
