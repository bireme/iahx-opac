<?php

use Symfony\Component\HttpFoundation\Request;

$app->get('history/{lang}/', function (Request $request, $lang) use ($app, $DEFAULT_PARAMS, $config) {

    global $texts;

    $params = array_merge(
        $app['request']->request->all(),
        $app['request']->query->all()
    );

    $SESSION = $app['session'];

    if(isset($params['clear']) and $params['clear'] == true) {
        $SESSION->remove('history');
        $SESSION->save();
    }

    $history = $SESSION->get('history');

    if(isset($params['remove']) and $params['remove'] == true) {
        $history_item = intval($params['item']) - 1; // array start at 0

        // remove item from history array
        unset($history[$history_item]);

        // reorder array
        $history = array_values($history);

        $SESSION->set('history', $history);
        $SESSION->save();
    }


    // translation strings
    $texts = parse_ini_file(TRANSLATE_PATH . $lang . "/texts.ini", true);

    $output_array = array();
    $output_array['lang'] = $lang;
    $output_array['texts'] = $texts;
    $output_array['history_list'] = $history;

    return $app['twig']->render( custom_template('history.html'), $output_array);
});

?>
