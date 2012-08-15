<?php

use Symfony\Component\HttpFoundation\Request;

$app->get('resource/{lang}/{id}', function (Request $request, $lang, $id) use ($app, $DEFAULT_PARAMS, $config) {

    global $lang, $texts;

    $collectionData = $DEFAULT_PARAMS['defaultCollectionData'];
    $site = $DEFAULT_PARAMS['defaultSite'];
    $col = $DEFAULT_PARAMS['defaultCollection'];

    // Dia response
    $dia = new Dia($site, $col, 1, "site", $lang);
    $dia_response = $dia->search("id:" . $id, "", array(), 0);
    $result = json_decode($dia_response, true);

    // translate
    $texts = parse_ini_file(TRANSLATE_PATH . $lang . "/texts.ini", true);

    // output vars
    $output_array = array();
    $output_array['lang'] = $lang;
    $output_array['col'] = $col;
    $output_array['site'] = $site;
    $output_array['docs'] = $result['diaServerResponse'][0]['response']['docs'];
    $output_array['doc'] = $result['diaServerResponse'][0]['response']['docs'][0];
    $output_array['config'] = $config;
    $output_array['texts'] = $texts;
    $output_array['display_file'] = "result-format-abstract.html";
    
    return $app['twig']->render( custom_template('result-detail.html'), $output_array );     

});

?>