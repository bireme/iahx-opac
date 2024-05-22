<?php

namespace App\Controller;

use App\Service\AuxFunctions;
use App\Service\SearchSolr;
use App\Service\CacheService;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Detection\MobileDetect;

final class ResourceController extends AbstractController
{

    public function __construct(
        private AuxFunctions $auxFunctions,
        private CacheService $cache,
    ){}


    #[Route('{instance}/resource/{lang}/{id}')]
    public function index(Request $request, string $instance, string $lang, string $id): Response
    {
        global $config;

        $app_dir = $this->getParameter('kernel.project_dir');
        require($app_dir . '/config/load-instance-definitions.php');

        // get texts used in template
        $texts = $this->cache->get_texts($instance, $lang);

        $params = [];
        foreach($request->query as $key => $value) {
          $params[$key] = $value;
        }

        $collectionData = $DEFAULT_PARAMS['defaultCollectionData'];
        $site = $DEFAULT_PARAMS['defaultSite'];
        $col = $DEFAULT_PARAMS['defaultCollection'];

        // controller response
        $dia = new SearchSolr($site, $col, 1, "site", $lang);

        if ($config->show_related_docs == "true"){
            $dia_response = $dia->related($id);
        }else{
            if ($config->check_alternate_id == "true"){
                $dia_response = $dia->search('id:"' . $id . '" OR alternate_id:"' . $id . '"');
            }else{
                $dia_response = $dia->search('id:"' . $id . '"');
            }
        }
        $result = json_decode($dia_response, true);

        $total_found = $result['diaServerResponse'][0]['response']['numFound'];

        if (intval($total_found) > 0){
            // output vars
            $output_array = array();
            $output_array['lang'] = $lang;
            $output_array['col'] = $col;
            $output_array['site'] = $site;
            $output_array['docs'] = $result['diaServerResponse'][0]['response']['docs'];
            $output_array['collectionData'] = $collectionData;
            $output_array['config_cluster_list'] = $collectionData->cluster_list->cluster;
            $output_array['current_page'] = 'detail';
            $output_array['q'] = 'id:' . $id;

            if ( $config->show_related_docs == "true"){
                $output_array['doc'] = $result['diaServerResponse'][0]['match']['docs'][0];
                $output_array['maxScore'] = $result['diaServerResponse'][0]['response']['maxScore'];
                $output_array['related_docs'] = $result['diaServerResponse'][0]['response']['docs'];
            }else{
                $output_array['doc'] = $result['diaServerResponse'][0]['response']['docs'][0];
                $output_array['clusters'] = $result['diaServerResponse'][0]['facet_counts']['facet_fields'];
            }

            $output_array['current_url'] = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
            $output_array['config'] = $config;
            $output_array['texts'] = $texts;

            // start session
            $SESSION = $request->getSession();
            $SESSION->start();

            // log user action
            //log_user_action($lang, $col, $site, 'id:' . $id, '', '', '', '', 'detail', $SESSION->getId());

            $check_mobile = $config->mobile_version;
            $view = ( isset($params['view']) ? $params['view'] : '');

            if( $view == 'desktop' ) {   // forced by user desktop version
                $view = '';              // use default view
            }else{
                if ($check_mobile == 'true'){  // activate alternate template for mobile
                    $detect = new MobileDetect();
                    if ($view == 'mobile' || ($detect->isMobile() && !$detect->isTablet()) )   {
                        $view = 'mobile';
                    }
                }
            }

            if( !isset($view) ) {
                $view = '';
            }

        }else{
            // return 404 if document not found
            header("HTTP/1.0 404 Not Found");
        }

        return $this->render(TEMPLATE_NAME . $view . '/result-detail.html', $output_array);

    }
}