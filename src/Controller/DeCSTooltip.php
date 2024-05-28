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

        $bool = array(
            "101", // Termo autorizado
            "102", // Sinônimo
            "104"  // Termo histórico
        );

        $concept = 0;
        $concept_id = 0;
        for( $i = 0; !$concept && ($i < sizeof($bool)); $i = $i + 1 ){
            $query = "https://decs.bvsalud.org/cgi-bin/mx/cgi=@vmx/decs/?bool=".$bool[$i]."%20$term&lang=$lang";
            $decs = @simplexml_load_file($query);
            if ($decs){
                $concept = (String) @$decs->decsws_response->record_list->record->definition->occ['n'];
                $concept_id = (String) @$decs->decsws_response->record_list->record['mfn'];
            }
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