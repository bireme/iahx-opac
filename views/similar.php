<?php

use Symfony\Component\HttpFoundation\Request;

$app->match('similar/', function (Request $request) use ($app, $DEFAULT_PARAMS, $config) {
    global $lang, $texts;

    $params = array_merge(
        $app['request']->request->all(),
        $app['request']->query->all()
    );

    $text = (isset($params['text']) ? $params['text'] : '');
    $doc_id = (isset($params['doc_id']) ? $params['doc_id'] : '');

    if ($text != ''){

        $text = urlencode($text);

        $similar_service_url = 'http://similardocs.bireme.org/SDService?adhocSimilarDocs=' . $text;
        $similar_response = @file_get_contents($similar_service_url);
        $similar_xml = simplexml_load_string($similar_response, 'SimpleXMLElement',LIBXML_NOCDATA);
        $json = json_encode($similar_xml);
        $similar_docs = json_decode($json, TRUE);

        //echo  $similar_service_url;

        // translate
        $texts = parse_ini_file(TRANSLATE_PATH . $lang . "/texts.ini", true);

        // output vars
        $output_array = array();
        $output_array['lang'] = $lang;
        $output_array['texts'] = $texts;
        $output_array['similar_docs'] = $similar_docs;
        $output_array['doc_id'] = $doc_id;

        return $app['twig']->render( custom_template('/similar-docs.html'), $output_array );
    }

});

?>
