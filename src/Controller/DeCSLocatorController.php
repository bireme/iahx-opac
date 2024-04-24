<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Detection\MobileDetect;

final class DeCSLocatorController extends AbstractController
{

    public function __construct(
    ){}


    #[Route('{instance}/decs-locator/{lang}/')]
    public function index(Request $request, string $instance, string $lang): Response
    {
        global $config, $lang, $texts;

        $app_dir = $this->getParameter('kernel.project_dir');

        require($app_dir . '/config/load-instance-definitions.php');

        $params = [];
        foreach($request->query as $key => $value) {
          $params[$key] = $value;
        }

        $collectionData = $DEFAULT_PARAMS['defaultCollectionData'];
        $site = $DEFAULT_PARAMS['defaultSite'];
        $col = $DEFAULT_PARAMS['defaultCollection'];

        $tree_id = $request->get("tree_id");        // D02.065.589.099.750.124
        $descriptor = $request->get("descriptor");  // get detais of specific descriptor
        $mode = $request->get("mode");              // mode dataentry

        if ($descriptor != ''){
            $api_url = "https://api.bvsalud.org/decs/v2/search-boolean?lang=" . $lang;
            $api_url .= "&bool=101%20" . str_replace(' ','%20', $descriptor); // get descriptor by authorized term
        }else{
            $api_url = "https://api.bvsalud.org/decs/v2/get-tree?lang=" . $lang;
            $api_url .= "&tree_id=" . $tree_id;        // get descriptor by tree
        }

        $API_KEY = ($mode == 'dataentry' ? $_ENV['DECS_APIKEY_DATAENTRY'] : $_ENV['DECS_APIKEY_LOCATE']);

        $opts = array(
            'http'=>array(
              'method' => "GET",
              'header' =>' apikey: ' . $API_KEY
            )
        );

        $context = stream_context_create($opts);
        $api_result = file_get_contents($api_url, false, $context);

        $decs_xml = simplexml_load_string($api_result);

        // translate
        $texts_interface = parse_ini_file(TRANSLATE_PATH . $lang . "/texts.ini", true);
        $texts_decs = parse_ini_file(APP_TRANSLATE_PATH . $lang . "/decs-locator.ini", true);

        $texts = array_merge($texts_interface, $texts_decs);

        // start session
        $SESSION = $request->getSession();
        $SESSION->start();

        //print_r($decs_xml);

        // log user action
        // log_user_action($lang, '', '', $tree_id, '', '', '', '', 'decs_lookup', $SESSION->getId());

        if ($decs_xml->decsws_response->tree->ancestors->term_list) {
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
                if ($i+1 < $total_ancestors) {
                    $current_tree_id = preg_split('/\|/', $ancestors_tree[$i]);
                    $next_tree_id = preg_split('/\|/', $ancestors_tree[$i+1]);

                    if ( strlen($current_tree_id[0]) >= strlen($next_tree_id[0]) ) {
                        $tree++;
                    }
                }

            }
        }

        // output vars
        $output_array = array();
        $output_array['q'] = $request->get("tree_id");
        $output_array['current_page'] = 'decs_lookup';
        $output_array['lang'] = $lang;
        $output_array['decs'] = $decs_xml->decsws_response;
        $output_array['ancestors_i_tree'] = (isset($ancestors_i_tree) ? $ancestors_i_tree : '');
        $output_array['texts'] = $texts;
        $output_array['tree_id_category'] = substr($tree_id,0,1);
        $output_array['params'] = $params;
        $output_array['filters'] = ( isset($params['filter']) ? $params['filter'] : array() );
        $output_array['config'] = $config;
        $output_array['collectionData'] = $collectionData;
        $output_array['mode'] = $mode;
        $output_array['filter_prefix'] = ( isset($config->decs_locate_filter) ? $config->decs_locate_filter : 'mh' );

        return $this->render(TEMPLATE_NAME . '/decs-locator-page.html', $output_array);

    }
}