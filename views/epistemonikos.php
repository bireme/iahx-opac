<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->match('epistemonikos/{lang}/{id}', function (Request $request, $lang, $id) use ($app, $DEFAULT_PARAMS, $config) {

    $texts = parse_ini_file(TRANSLATE_PATH . $lang . "/texts.ini", true);

    $api_url = 'https://api.epistemonikos.org/v1/documents/' . $id .'?show=relations_info,classification_status';

    // Create a stream for token authorization
    $opts = array(
      'http'=>array(
        'method'=>"GET",
        'header'=>'Authorization: Token token="' . $config->epistemonikos_token . "\r\n"
      )
    );

    $context = stream_context_create($opts);
    $api_result = @file_get_contents($api_url, false, $context);

    $data = json_decode($api_result, TRUE);
    //print_r($data);

    $classification = $data['metadata']['classification'];
    $classification_status = $data['metadata']['classification_status'];

    if ($classification != 'raw' && $classification_status != 'pending'){
        $output = array();
        $output['doc_id'] = (strpos($id, '-') !== false ? $id : 'mdl-' . $id);
        $output['texts'] = $texts['EPISTEMONIKOS'];
        $output['lang_param'] = "/" . $lang . "/";
        $output['classification'] = $classification;
        $output['classification_status'] = $classification_status;
        $output['primary_study_ref'] = (isset($data['relations_info']['references']['primary-study']) ? $data['relations_info']['references']['primary-study'] : '');
        $output['systematic_review_ref'] = (isset($data['relations_info']['references']['systematic-review']) ? $data['relations_info']['references']['systematic-review'] : '');
        $output['epistemonikos_doc_url'] = $data['external_links']['epistemonikos'];
        $output['total_references'] = $data['relations_info']['total_references'];
        $output['related_references'] = (isset($data['related_references']) ? $data['related_references'] : '') ;

        $response = $app['twig']->render('epistemonikos.html', $output);
    }else{
        $response = new Response('No Content', 204);
    }

    return $response;

});

?>
