<?php

namespace App\Controller;

use App\Service\AuxFunctions;
use App\Service\CacheService;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdvancedFormPage extends AbstractController
{

    public function __construct(
        private AuxFunctions $auxFunctions,
        private CacheService $cache,
    ){}

    #[Route('{instance}/advanced/{lang}/')]
    public function index(Request $request, string $instance, string $lang): Response
    {

        $app_dir = $this->getParameter('kernel.project_dir');
        $config = $this->cache->get_config($instance);
        $texts = $this->cache->get_texts($instance, $lang);

        require($app_dir . '/config/load-instance-definitions.php');

        $params = [];
        foreach($request->query as $key => $value) {
          $params[$key] = $value;
        }

        $template_vars = array();
        $template_vars['lang'] = $lang;
        $template_vars['texts'] = $texts;
        $template_vars['config'] = $config;
        $template_vars['collectionData'] = $config->search_collection_list->collection[0];
        $template_vars['current_page'] = 'advanced_form';

        $response = $this->render(TEMPLATE_NAME . '/advanced-form.html', $template_vars);

        return $response;
    }
}