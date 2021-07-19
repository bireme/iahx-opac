<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->match('impact-measurement/{lang}/{code}', function (Request $request, $lang, $code) use ($app, $DEFAULT_PARAMS, $config) {

    $output = array();
    $impact_measurement_cookie = '';
    $im_api = strval($config->impact_measurement_domain) . '/api/main/?format=json&code=';
    $im_scope = strval($config->impact_measurement_cookie_domain_scope);

    if ( ! $_COOKIE['impact_measurement'] ) {
        $impact_measurement_cookie = md5(uniqid(rand(), true));
        setcookie("impact_measurement", $impact_measurement_cookie, (time() + (10 * 365 * 24 * 60 * 60)), '/', $im_scope);
    }

    $texts = parse_ini_file(TRANSLATE_PATH . $lang . "/texts.ini", true);
    $page_type = get_page_type($_GET['page_type'], true);
    $content = file_get_contents($im_api.$code.'&page='.$page_type);
    $data = json_decode($content, TRUE);

    if ( $data && count($data['objects']) > 0 ) {
        $output['questions'] = $data['objects'][0]['questions'];
        $output['code'] = $code;
        $output['user'] = ( $_COOKIE['impact_measurement'] ) ? $_COOKIE['impact_measurement'] : $impact_measurement_cookie;
        $output['myvhl_user'] = ( $_COOKIE['userID'] ) ? $_COOKIE['userID'] : '';
        $output['page'] = $_SERVER['HTTP_REFERER'];
        $output['page_base64'] = base64_encode($_SERVER['HTTP_REFERER']);
        $output['page_type'] = get_page_type($_GET['page_type']);
        $output['page_type_slug'] = $page_type;
        $output['texts'] = $texts['IMPACT_MEASUREMENT'];
        $output['lang'] = ( 'pt' == $lang ) ? 'pt-BR' : $lang;
        $output['im_survey_link'] = $config->impact_measurement_survey_link;
        $output['config'] = $config;

        $response = $app['twig']->render('survey.html', $output);
    } else {
        $response = new Response('No Content', 204);
    }

    return $response;

});

?>
