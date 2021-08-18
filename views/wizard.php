<?php

use Symfony\Component\HttpFoundation\Request;

$app->get('wizard/{wizard_id}', function (Request $request, $wizard_id) use ($app, $DEFAULT_PARAMS, $config) {
    global $texts;

    $wizard_api_url = "https://iahx-wizard.bireme.org/api";

    $params = array_merge(
        $app['request']->request->all(),
        $app['request']->query->all()
    );

    $SESSION = $app['session'];
    $SESSION->start();
    $wizard_session = $SESSION->get('wizard_session');

    $lang = $DEFAULT_PARAMS['lang'];
    if(isset($params['lang']) and $params['lang'] != "") {
        $lang = $params['lang'];
    }
    // translation file
    $texts = parse_ini_file(TRANSLATE_PATH . $lang . "/texts.ini", true);

    $step = (isset($params['step']) ? intval($params['step']) : 1);
    $previous_filter_name = $params['previous_filter_name'];
    $previous_filter_value = $params['previous_filter_value'];
    $previous_filter_label = $params['previous_filter_label'];
    $filter_by_prefix = null;

    /* DEBUG
    print_r($wizard_session);
    print('** previous_fiter_name: '  . $previous_filter_name . ' previous_filter_value: '  . $previous_filter_value . ' **');
    */

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

    // update session
    $SESSION->set('wizard_session', $wizard_session);
    $SESSION->save();
    /* DEBUG
    print_r($wizard_session);
    */

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
        $filter_list = array($step_filter);

        // filter browse param (ex. tag:999)
        $fb = $step_filter . ":999";

        $dia = new Dia($site, $col, 1, 'site', $lang);
        $dia->setParam('fb', $fb);
        $dia->setParam('initial_filter', $initial_filter);
        $dia->setParam('filter_list', $filter_list);

        $solr_response = $dia->search($q, $index, $filters_to_apply);
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
                    array_push($option, translate($option[0], $label_group));
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
        }
    }

    // output vars
    $output_array = array();
    $output_array['lang'] = $lang;
    $output_array['texts'] = $texts;
    $output_array['config'] = $filter_config;
    $output_array['step_info'] = $step_info;
    $output_array['option_list'] = $option_list;
    $output_array['option_list_step'] = $option_list_step;
    $output_array['option_group'] = $option_group;
    $output_array['filter_by_prefix'] = $filter_by_prefix;
    $output_array['only_translated_items_filters'] = $only_translated_items_filters;

    if ($wizard_session[$step]){
        $output_array['previous_session_selection'] = $wizard_session[$step];
    }

    return $app['twig']->render(custom_template('wizard-step.html'), $output_array);

});

?>
