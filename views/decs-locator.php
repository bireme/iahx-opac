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

    // start session
    $SESSION = $app['session'];
    $SESSION->start();    

    //print_r($decs_xml);

    // log user action
    log_user_action($lang, '', '', $tree_id, '', '', '', '', 'decs_lookup', $SESSION->getId());

    if ($decs_xml->decsws_response->tree->ancestors->term_list->term) {
        $ancestors_tree = array();
        foreach ($decs_xml->decsws_response->tree->ancestors->term_list->term as $ancestor) {
            //var_dump($ancestor);
            $tree_id = (string) $ancestor['tree_id'];
            $ancestors_tree[] = $tree_id . '|' . (string) $ancestor;
        }

        $total_ancestors = count($ancestors_tree);
        $ancestors_i_tree = array(); // ancestors individual tree
        $tree = 0;
        for ($i = 0; $i < $total_ancestors; $i++){

            $ancestors_i_tree[$tree][] = $ancestors_tree[$i] ;
            if ($i < $total_ancestors ) {
                $current_tree_id = preg_split('/\|/', $ancestors_tree[$i]);
                $next_tree_id = preg_split('/\|/', $ancestors_tree[$i+1]);

                if ( strlen($current_tree_id[0]) > strlen($next_tree_id[0]) ) {
                    $tree++;            
                }
            }

        }
    }

    // output vars
    $output_array = array();
    $output_array['lang'] = $lang;
    $output_array['decs'] = $decs_xml->decsws_response;
    $output_array['ancestors_i_tree'] = $ancestors_i_tree;
    $output_array['texts'] = $texts;
    $output_array['tree_id_category'] = substr($tree_id,0,1);

    return $app['twig']->render( 'decs-locator-page.html', $output_array );     

});

?>