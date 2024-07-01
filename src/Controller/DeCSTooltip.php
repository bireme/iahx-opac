<?php

namespace App\Controller;

use App\Service\AuxFunctions;
use App\Service\CacheService;
use App\Service\InstanceConfigService;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DeCSTooltip extends AbstractController
{

    public function __construct(
        private AuxFunctions $auxFunctions,
        private CacheService $cache,
        private InstanceConfigService $instanceConfigService,
    ){}

    #[Route('{instance}/decs-tooltip/{lang}/')]
    public function index(Request $request, string $instance, string $lang): Response
    {

        list($config, $defaults) = $this->instanceConfigService->loadInstanceConfiguration($instance);
        $texts = $this->cache->get_texts($instance, $lang);

        $term = $request->get('term');
        $term = stripcslashes($term);
        $term = strtoupper($term);
        $term = $this->auxFunctions->remove_accents($term);

        // search by fields 101 (autorized name) and 102 (synonym)
        $api_url = "https://api.bvsalud.org/decs/v2/search-boolean?lang=" . $lang;
        $api_url .= "&bool=101%20" . str_replace(' ', '%20',$term);
        $api_url .= "%20OR%20102%20" . str_replace(' ', '%20',$term);

        $opts = array(
            'http'=>array(
              'method' => "GET",
              'header' =>' apikey: ' . $_ENV['DECS_APIKEY_LOCATE']
            )
        );

        $context = stream_context_create($opts);
        $api_result = file_get_contents($api_url, false, $context);

        $decs = @simplexml_load_string($api_result);
        if ($decs){
            $concept = (String) @$decs->decsws_response->record_list->record->definition->occ['n'];
            $concept_id = (String) @$decs->decsws_response->record_list->record['mfn'];
        }

        $decs_url = "https://decs.bvsalud.org/";
        $decs_url_lang = ($lang != "pt" ? $lang : '');

        $href = $decs_url . $decs_url_lang . "/ths/resource/?id=" . $concept_id . "&filter=ths_exact_term&q=" . $term;

        $template_vars = array();
        $template_vars['texts'] = $texts;
        $template_vars['concept'] = $concept;
        $template_vars['decs'] = $decs;
        $template_vars['href'] = $href;

        $response = $this->render(TEMPLATE_NAME . '/decs-tooltip.html', $template_vars);

        return $response;
    }
}