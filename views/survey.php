<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->match('impact-measurement/{lang}/{code}', function (Request $request, $lang, $code) use ($app, $DEFAULT_PARAMS, $config) {

    $output = array();
    $impact_measurement_cookie = '';
    $im_api = 'https://im.bireme.org/api/main/?format=json&code=';
    $im_scope = strval($config->impact_measurement_cookie_domain_scope);

    if ( ! $_COOKIE['impact_measurement'] ) {
        $impact_measurement_cookie = md5(uniqid(rand(), true));
        setcookie("impact_measurement", $impact_measurement_cookie, (time() + (10 * 365 * 24 * 60 * 60)), '/', $im_scope);

        $domains = array(
            '.bvs.br' => $config->impact_measurement_bvs_cookie_domain,
            '.bvsalud.org' => $config->impact_measurement_bvsalud_cookie_domain,
            '.bireme.org' => $config->impact_measurement_bireme_cookie_domain
        );

        if ( array_key_exists($im_scope, $domains) ) {
            $im_cookie = array();
            unset($domains[$im_scope]);

            foreach ($domains as $domain => $url) {
                if ( ! empty($url) ) {
                    $im_cookie[] = $url.'/setcookie.php?im_cookie='.$impact_measurement_cookie.'&im_code='.$code.'&im_data='.base64_encode($im_api);
                }
            }

            $output['im_cookie'] = $im_cookie;
        }
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
        $output['page_type'] = get_page_type($_GET['page_type']);
        $output['texts'] = $texts['IMPACT_MEASUREMENT'];
        $output['lang'] = ( 'pt' == $lang ) ? 'pt-BR' : $lang;

        $response = $app['twig']->render('survey.html', $output);
    } else {
        $response = new Response('No Content', 204);
    }

    return $response;

});

?>
