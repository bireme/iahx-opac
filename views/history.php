<?php

use Symfony\Component\HttpFoundation\Request;

$app->get('history', function (Request $request) use ($app, $DEFAULT_PARAMS, $config) {

    global $lang, $texts;

    $params = array_merge(
        $app['request']->request->all(),
        $app['request']->query->all()
    );

    $SESSION = $app['session'];
    $SESSION->start();
    
    if(isset($params['clear']) and $params['clear'] == true) {
        $SESSION->remove('history');
        $SESSION->save();
    }

    $history = $SESSION->get('history');

    $output_array = array();
    $output_array['lang'] = $lang;
    $output_array['texts'] = $texts;
    $output_array['histories'] = $history;

    return $app['twig']->render('history.html', $output_array);
        
});

?>