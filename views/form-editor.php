<?php

use Symfony\Component\HttpFoundation\Request;

$app->match('form-editor/{form_id}', function (Request $request, $form_id) use ($app, $DEFAULT_PARAMS, $config) {
    global $texts;

    $params = array_merge(
        $app['request']->request->all(),
        $app['request']->query->all()
    );

    $lang = $DEFAULT_PARAMS['lang'];
    if(isset($params['lang']) and $params['lang'] != "") {
        $lang = $params['lang'];
    }

    $update_status = '';
    if (array_key_exists('json_content', $params)){
        $json_content = $params["json_content"];
        $json_file = PATH_DATA . 'config/form-' . $form_id . '.json';
        $saved = file_put_contents($json_file, $json_content, LOCK_EX);
        if ($saved == false){
            $update_status = 'warning';
        }else{
            $update_status = 'success';
        }
    }

    // Form configuration
    $json_form = file_get_contents(PATH_DATA . 'config/form-' . $form_id . '.json');

    // translation file
    $texts = parse_ini_file(TRANSLATE_PATH . $lang . "/texts.ini", true);

    // output vars
    $output_array = array();
    $output_array['lang'] = $lang;
    $output_array['config'] = $config;
    $output_array['json_form'] = $json_form;
    $output_array['form_id'] = $form_id;
    $output_array['texts'] = $texts;
    $output_array['update_status'] = $update_status;

    return $app['twig']->render(custom_template('form-editor.html'), $output_array);

});

?>
