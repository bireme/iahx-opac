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

    // output vars
    $output_array = array();
    $output_array['lang'] = $lang;
    $output_array['col'] = $col;
    $output_array['site'] = $site;
    $output_array['docs'] = $result['diaServerResponse'][0]['response']['docs'];
    $output_array['config'] = $config;
    $output_array['texts'] = $texts;
    
    return $app['twig']->render('result-detail.html', $output_array);     

});

?>