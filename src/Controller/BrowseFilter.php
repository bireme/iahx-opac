<?php

namespace App\Controller;

use App\Service\AuxFunctions;
use App\Service\CacheService;
use App\Service\SearchSolr;
use App\Service\InstanceConfigService;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BrowseFilter extends AbstractController
{

    public function __construct(
        private AuxFunctions $auxFunctions,
        private CacheService $cache,
        private InstanceConfigService $instanceConfigService,
    ){}

    #[Route('{instance}/browse-filter/{filter}/')]
    public function index(Request $request, string $instance, string $filter): Response
    {
        global $lang, $texts;

        list($config, $defaults) = $this->instanceConfigService->loadInstanceConfiguration($instance);

        $lang = $request->get('lang', $defaults['lang']);
        $texts = $this->cache->get_texts($instance, $lang);

        $from = $request->get('from', 0);
        $limit = $request->get('limit', 999999);

        $collectionData = $defaults['defaultCollectionData'];
        $site = $defaults['defaultSite'];
        $col = $defaults['defaultCollection'];
        $initial_filter = html_entity_decode($collectionData->initial_filter);
        $config_cluster_list = $collectionData->cluster_list->cluster;


        // filter browse param (ex. au:10)
        $fb = $filter . ":" . ($limit + $from);

        $search = new SearchSolr($site, $col, 1, 'site', $lang);
        $search->setParam('fb', $fb);
        $search->setParam('initial_filter', $initial_filter);
        $search->setParam('filter_list', $config_cluster_list);

        $search_response = $search->search();
        $result = json_decode($search_response, true);

        $filter_list = $result['diaServerResponse'][0]['facet_counts']['facet_fields'][$filter];

        if ($from != 0){
            $filter_list = array_slice($filter_list, $from);
        }

        $config_cluster_list = $collectionData->cluster_list->cluster;
        $only_translated_items_clusters = $this->auxFunctions->getOnlyTranslatedItemsClusterList($collectionData);

        // output vars
        $template_vars = array();
        $template_vars['lang'] = $lang;
        $template_vars['config'] = $config;
        $template_vars['texts'] = $texts;
        $template_vars['filter'] = $filter;
        $template_vars['from'] = $from;
        $template_vars['filter_list'] = $filter_list;
        $template_vars['only_translated_items_clusters'] = $only_translated_items_clusters;

        if ($filter_list){
            $response = $this->render(TEMPLATE_NAME . '/browse-filter.html', $template_vars);
        } else {
            $response = new Response('Error fetching content', 500);
        }

        return $response;
    }
}