<?php

use Symfony\Component\HttpFoundation\Request;

$app->get('wizard/{wizard_id}', function (Request $request, $wizard_id) use ($app, $DEFAULT_PARAMS, $config) {
    global $texts;

    $wizard_api_url = "http://iahx-wizard.teste.bireme.org/api";

    $params = array_merge(
        $app['request']->request->all(),
        $app['request']->query->all()
    );

    $SESSION = $app['session'];
    $SESSION->start();

    $lang = $DEFAULT_PARAMS['lang'];
    if(isset($params['lang']) and $params['lang'] != "") {
        $lang = $params['lang'];
    }

    $step = (isset($params['step']) ? $params['step'] : '1');
    $filter_name = $params['filter_name'];
    $filter_value = $params['filter_value'];
    $filter_by_prefix = null;

    if ($filter_name != '' && $filter_value != ''){
        $wizard_filter = $SESSION->get('wizard_filter');
        $wizard_filter_id = intval($step)-1;

        $filter = array('filter' => $filter_name, 'value' => $filter_value);
        $wizard_filter[$wizard_filter_id] = $filter;
        // remove filters after current filter
        array_splice($wizard_filter, $wizard_filter_id);

        $found_filter_prefix = preg_match('/^[0-9]+_/', $filter_value, $match_prefix);
        if ($found_filter_prefix){
            $filter_by_prefix = $match_prefix[0];
        }
    }
    // update session
    $SESSION->set('wizard_filter', $wizard_filter);
    $SESSION->save();

    // mount internal class filter param used to solr query
    $filter = array();
    if ($wizard_filter){
        foreach ($wizard_filter as $wf){
            // wizard_option filter is used only to filter API option list
            if ($wizard_filter != 'wizard_option'){
                $name = $wf['filter'];
                $value = $wf[ 'value'];
                $filter[$name] = array($value);
            }
        }
    }

    // get step info
    $step_url = $wizard_api_url . '/step/?wizard=' . $wizard_id . '&step=' . $step;

    $step_response = file_get_contents($step_url);
    $step_data = json_decode($step_response, true);
    $step_info = $step_data['results'][0];
    $step_filter = $step_info['filter_name'];

    // get solr filter values
    if ($step_filter != ''){
        $collectionData = $DEFAULT_PARAMS['defaultCollectionData'];
        $only_translated_items_filters = getOnlyTranslatedItemsClusterList($collectionData);
        $site = $DEFAULT_PARAMS['defaultSite'];
        $col = $DEFAULT_PARAMS['defaultCollection'];
        $initial_filter = html_entity_decode($collectionData->initial_filter);

        // filter browse param (ex. tag:999)
        $fb = $step_filter . ":999";

        $dia = new Dia($site, $col, 1, 'site', $lang);
        $dia->setParam('fb', $fb);
        $dia->setParam('initial_filter', $initial_filter);

        $solr_response = $dia->search($q, $index, $filter);
        $solr_result = json_decode($solr_response, true);

        $option_list = $solr_result['diaServerResponse'][0]['facet_counts']['facet_fields'][$step_filter];

    // option list from API
    }else{
        $option_list = $step_info['options'];
        $option_group = '';
        if ($filter == 'wizard_option_group'){
            $option_group = $filter_value;
        }
    }

    // translation file
    $texts = parse_ini_file(TRANSLATE_PATH . $lang . "/texts.ini", true);

    // output vars
    $output_array = array();
    $output_array['lang'] = $lang;
    $output_array['texts'] = $texts;
    $output_array['config'] = $filter_config;
    $output_array['step_info'] = $step_info;
    $output_array['option_list'] = $option_list;
    $output_array['option_group'] = $option_group;
    $output_array['filter_by_prefix'] = $filter_by_prefix;
    $output_array['only_translated_items_filters'] = $only_translated_items_filters;

    return $app['twig']->render(custom_template('wizard-step.html'), $output_array);

});

?>
