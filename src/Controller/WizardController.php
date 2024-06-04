<?php

namespace App\Controller;

use App\Service\AuxFunctions;
use App\Service\SearchSolr;
use App\Service\CacheService;
use App\Service\InstanceConfigService;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WizardController extends AbstractController
{

    public function __construct(
        private AuxFunctions $auxFunctions,
        private CacheService $cache,
        private InstanceConfigService $instanceConfigService,
    ){}

    #[Route('{instance}/wizard/{wizard_id}')]
    public function index(Request $request, string $instance, string $wizard_id): Response
    {
        global $texts;

        list($config, $defaults) = $this->instanceConfigService->loadInstanceConfiguration($instance);

        $params = [];
        foreach($request->query as $key => $value) {
          $params[$key] = $value;
        }

        $lang = $params['lang'] ?? $defaults['lang'];

        $texts = $this->cache->get_texts($instance, $lang);

        $step = (isset($params['step']) ? intval($params['step']) : 1);
        $output = $params['output'] ?? 'html';
        $previous_filter_name = $params['previous_filter_name'] ?? '';
        $previous_filter_value = $params['previous_filter_value'] ?? '';
        $previous_filter_label = $params['previous_filter_label'] ?? '';
        $filter_by_prefix = null;

        if (isset($previous_filter_name) && $previous_filter_name != 'undefined' && isset($previous_filter_value) && $previous_filter_value != 'undefined'){
            $previous_step = $step - 1;
            $previous_filter = array('filter' => $previous_filter_name, 'value' => $previous_filter_value, 'label' => $previous_filter_label);

            //update wizard_session with previous filter
            $wizard_session[$previous_step] = $previous_filter;

            $found_filter_prefix = preg_match('/^[0-9]+_/', $previous_filter_value, $match_prefix);
            if ($found_filter_prefix){
                $filter_by_prefix = $match_prefix[0];
            }
        }

        // mount internal class filter param used to Solr query
        $filters_to_apply = array();
        if ($wizard_session){
            for ($i = 1; $i < $step; $i++){
                $wizard_filter = $wizard_session[$i];
                // skip wizard_option filter (used only to filter API option list)
                if ($wizard_filter['filter'] != 'wizard_option_group'){
                    $name = $wizard_filter['filter'];
                    $value = $wizard_filter[ 'value'];
                    $filters_to_apply[$name] = array($value);
                }
            }
        }

        // get step info
        $step_url = $_ENV['WIZARD_API_URL'] . '/step/?wizard=' . $wizard_id . '&step=' . $step;
        //die($step_url);

        $step_response = @file_get_contents($step_url);
        //die($step_response);

        if ($step_response){
            $step_data = json_decode($step_response, true);
            $step_info = $step_data['results'] ? $step_data['results'][0] : 0;
            $step_filter = $step_info['filter_name'] ?? '';
        }

        // get solr filter values
        if ($step_filter != ''){
            $collectionData = $defaults['defaultCollectionData'];
            $only_translated_items_filters = $this->auxFunctions->getOnlyTranslatedItemsClusterList($collectionData);
            $site = $defaults['defaultSite'];
            $col = $defaults['defaultCollection'];
            $initial_filter = html_entity_decode($collectionData->initial_filter);
            $filter_list = array($step_filter);
            // set empty for query and index search params
            $q = $index = '';

            // filter browse param (ex. tag:999)
            $fb = $step_filter . ":999";

            $search = new SearchSolr($site, $col, 1, 'site', $lang);
            $search->setParam('fb', $fb);
            $search->setParam('initial_filter', $initial_filter);
            $search->setParam('filter_list', $filter_list);

            $solr_response = $search->search($q, $index, $filters_to_apply);
            $solr_result = json_decode($solr_response, true);

            $option_list = $solr_result['diaServerResponse'][0]['facet_counts']['facet_fields'][$step_filter];

            // create a array with only valid options (translated and of same prefix)
            $option_list_step = array();
            $label_group = 'REFINE_' . $step_filter;
            foreach ($option_list as $index=>$option) {
                if ( in_array($step_info['filter_name'], $only_translated_items_filters) and !has_translation($option[0], $label_group) ){
                }else{
                    if ( !$filter_by_prefix or substr($option[0], 0, strlen($filter_by_prefix)) === $filter_by_prefix){
                        // add translation to array
                        array_push($option, $this->auxFunctions->translate($option[0], $label_group));
                        $option_list_step[] = $option;
                    }
                }
            }
            // sort by translation (array index 2)
            usort($option_list_step, function($a, $b) {
                return $a[2] <=> $b[2];
            });
        // option list from API
        }else{
            $option_list = $step_info['options'];
            $option_group = '';
            if ($previous_filter_name == 'wizard_option_group'){
                $option_group = $previous_filter_value;
                // filter option_list for option_group
                $option_list_filtered_by_group = [];
                foreach ($option_list as $option){
                    if ($option['group'] == $option_group){
                        $option_list_filtered_by_group[] = $option;
                    }
                }
                $option_list = $option_list_filtered_by_group;

            }
        }

        // output vars
        $template_vars = array();
        $template_vars['lang'] = $lang;
        $template_vars['texts'] = $texts;
        $template_vars['step_info'] = $step_info;
        $template_vars['option_list'] = $option_list;
        $template_vars['option_list_step'] = $option_list_step;
        $template_vars['option_group'] = $option_group ?? '';
        $template_vars['filter_by_prefix'] = $filter_by_prefix;
        $template_vars['only_translated_items_filters'] = $only_translated_items_filters;

        if (isset($wizard_session[$step])){
            $template_vars['previous_session_selection'] = $wizard_session[$step];
        }

        if ($output == 'json'){
            $result = json_encode($template_vars);

            $response = new Response();
            $response->setContent($result);
            $response->headers->set('Content-Type', 'application/json');
            $response->headers->set('Access-Control-Allow-Origin', '*');
            return $response;
        }else{
            return $this->render(TEMPLATE_NAME . '/wizard-step.html', $template_vars);
        }

    }
}