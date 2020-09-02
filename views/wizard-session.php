<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->match('wizard-session/{action}', function (Request $request, $action) use ($app) {

    $SESSION = $app['session'];
    $SESSION->start();
    $wizard_session = $SESSION->get('wizard_session');

    $params = array_merge(
        $app['request']->request->all(),
        $app['request']->query->all()
    );

    if($action == 'add') {
        $filter_name = $params['filter_name'];
        $filter_value = $params['filter_value'];
        $filter_label = $params['filter_label'];

        if ($filter_name != '' && $filter_value != ''){
            $new_filter = array('filter' => $filter_name, 'value' => $filter_value, 'label' => $filter_label);
            //update wizard_session
            $wizard_session[] = $new_filter;
        }
    }

    if($action == 'clear') {
        $wizard_session = array();
    }

    $SESSION->set('wizard_session', $wizard_session);
    $SESSION->save();

    return new Response('OK');

});

?>