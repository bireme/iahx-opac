<?php

namespace App\Controller;

use App\Service\AuxFunctions;
use App\Service\CacheService;
use App\Service\InstanceConfigService;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EpistemonikosGetInfo extends AbstractController
{

    public function __construct(
        private AuxFunctions $auxFunctions,
        private CacheService $cache,
        private InstanceConfigService $instanceConfigService,
    ){}

    #[Route('{instance}/epistemonikos-info/{lang}/{id}')]
    public function index(Request $request, string $instance, string $lang, string $id): Response
    {

        list($config, $defaults) = $this->instanceConfigService->loadInstanceConfiguration($instance);
        $texts = $this->cache->get_texts($instance, $lang);

        $api_url = 'https://api.epistemonikos.org/v1/documents/' . $id .'?show=relations_info,classification_status';

        // Create a stream for token authorization
        $opts = array(
          'http'=>array(
            'method'=>"GET",
            'header'=>'Authorization: Token token="' . $_ENV['EPISTEMONIKOS_TOKEN'] . "\r\n"
          )
        );

        $context = stream_context_create($opts);
        $api_result = @file_get_contents($api_url, false, $context);

        $data = json_decode($api_result, TRUE);
        // print_r($data);

        if ($data != null){
            $classification = $data['metadata']['classification'] ?? '';
            $classification_status = $data['metadata']['classification_status'] ?? '';
            $excluded = $data['metadata']['excluded'] ?? null;
        }

        //print(" CLASSIFICATION: " . $classification . " CLASSIFICATION STATUS: " . $classification_status . " EXCLUDED: " . $excluded);

        if ($data != null && $classification != 'raw' && $classification_status != 'pending' && !isset($excluded)){
            $template_vars = array();
            $template_vars['doc_id'] = (strpos($id, '-') !== false ? $id : 'mdl-' . $id);
            $template_vars['texts'] = $texts['EPISTEMONIKOS'];
            $template_vars['lang_param'] = "/" . $lang . "/";
            $template_vars['classification'] = $classification;
            $template_vars['classification_status'] = $classification_status;
            $template_vars['primary_study_ref'] = (isset($data['relations_info']['references']['primary-study']) ? $data['relations_info']['references']['primary-study'] : '');
            $template_vars['systematic_review_ref'] = (isset($data['relations_info']['references']['systematic-review']) ? $data['relations_info']['references']['systematic-review'] : '');
            $template_vars['epistemonikos_doc_url'] = $data['external_links']['epistemonikos'];
            $template_vars['total_references'] = $data['relations_info']['total_references'];
            $template_vars['related_references'] = (isset($data['related_references']) ? $data['related_references'] : '') ;

            $response = $this->render(TEMPLATE_NAME . '/epistemonikos-info.html', $template_vars);

        }else{
            $response = new Response('No Content', 204);
        }

        return $response;
    }
}