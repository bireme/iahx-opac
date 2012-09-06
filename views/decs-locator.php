<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->match('decs-locator/{lang}/', function (Request $request, $lang) use ($app) {

    $tree_id = $request->get("tree_id");
    //$tree_id = "D02.065.589.099.750.124";

    $decs_service_url = "http://decs.bvs.br/cgi-bin/mx/cgi=@vmx/decs?lang=" . $lang . "&tree_id=" . $tree_id;

    $decs_response = file_get_contents($decs_service_url);

    $decs_xml = simplexml_load_string($decs_response);
    // translate
    $texts = parse_ini_file(TRANSLATE_PATH . $lang . "/decs-locator.ini", true);

    //print_r($decs_xml);

    // output vars
    $output_array = array();
    $output_array['lang'] = $lang;
    $output_array['decs'] = $decs_xml->decsws_response;
    $output_array['texts'] = $texts;

    return $app['twig']->render( 'decs-locator-page.html', $output_array );     

});

?>
