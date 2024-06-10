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

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Address;

use Detection\MobileDetect;

final class SearchController extends AbstractController
{

    public function __construct(
        private AuxFunctions $auxFunctions,
        private CacheService $cache,
        private InstanceConfigService $instanceConfigService,
        private MailerInterface $mailer,
    ){}

    #[Route('{instance}/')]
    public function index(Request $request, string $instance): Response
    {
        global $lang, $texts;

        list($config, $defaults) = $this->instanceConfigService->loadInstanceConfiguration($instance);

        $params = [];
        $request_params = isset($request->request) ? $request->request : $request->query;
        foreach($request_params as $key => $value) {
          $params[$key] = $value;
        }

        $collectionData = $defaults['defaultCollectionData'];
        $site = $defaults['defaultSite'];
        $col = $defaults['defaultCollection'];

        $fb = "";
        if(isset($params['fb']) and $params['fb'] != "") {
            $fb = $params['fb'];
        }

        $count = (string)$config->documents_per_page;
        if(isset($params['count']) and $params['count'] != "") {
            $count = $params['count'];
            if ($config->max_documents_per_page){
                // if max_documents_per_page is defined check count url param
                if (intval($count) > intval($config->max_documents_per_page)){
                    $count = $config->max_documents_per_page;
                }
            }
        }

        $output = "site";
        if(isset($params['output']) and $params['output'] != "") {
            $output = $params['output'];
        }
        $output_as_file = true;
        if(isset($params['output_as_file']) and $params['output_as_file'] != "") {
            $output_as_file = ($params['output_as_file'] === 'true'? true : false);
        }

        $lang = (string)$defaults['lang'];
        if(isset($params['lang']) and $params['lang'] != "") {
            $lang = $params['lang'];
        }

        $q = "";
        if(isset($params['q']) and $params['q'] != "") {
            $q = $params['q'];
        }

        $index = "";
        if(isset($params['index']) and $params['index'] != "") {
            $index = $params['index'];
        }

        // if user submit a new search restart values of from, page
        if( isset($params['search_form_submit']) ){
            $params['from'] = 0;
            $params['page'] = 1;
        }

        // check for reset_filters param
        if( isset($params['reset_filters']) && $params['reset_filters'] == 'ALL' ){
            $params['filter'] = array();
        }


        $from = 0;
        if(isset($params['from']) and $params['from'] != "") {
            $from = $params['from'];
        }

        $page = 1;
        if(isset($params['page'])and $params['page'] != "") {
            $page = $params['page'];
        }

        $where = array();
        if(isset($params['where'])and $params['where'] != "") {

            $where = $params['where'];
            foreach($collectionData->where_list->where as $item) {
                if($item->name == $where) {
                    $where = (string) $item->filter;

                    if(!empty($where)) {

                        $where = explode(":", $where);
                        $where[1] = str_replace('("', "", $where[1]);
                        $where[1] = str_replace('")', "", $where[1]);
                        $where = array($where[0] => array($where[1]));
                    }

                    if(!is_array($where)) {
                        $where = array($where);
                    }
                    break;
                }
            }
        } else {
            list($param_where, $where) = $this->auxFunctions->getDefaultWhere($collectionData, $q);
            $params['where'] = $param_where;
        }

        $sort = "";
        $sort_value = "";
        if(isset($params['sort']) and $params['sort'] != "Array") {

            $sort = $params['sort'];

            $exists = false;
            foreach($collectionData->sort_list->sort as $item) {
                if($sort == $item->name) {
                    $exists = true;
                    $sort_value = (string) $item->value;
                    break;
                }
            }

            if(!$exists)
                $sort = "";

        }
        if ($sort == ""){
            $sort_value = $this->auxFunctions->getDefaultSort($collectionData, $q);
        }

        $format = $defaults['defaultDisplayFormat'];
        if(isset($params['format'])and $params['format'] != "") {
            $format = $params['format'];
        }

        $filter = array();
        if(!empty($params['filter']) and $params['filter'] != "Array") {
            $filter = $params['filter'];
        }

        // alternative syntax for filter param ==> index:value (ex. db:LILACS)
        if ( !is_array($filter) ) {
            preg_match('/([a-z]+):(.+)/',$filter, $filter_parts);
            if ($filter_parts){
                // convert to internal format
                $filter = array($filter_parts[1] => array($filter_parts[2]));
            }
        }

        foreach($filter as $key => $value) {
            if($value == "Array" or $value == "Array#" or $value == "" or empty($value[0])) {
                unset($filter[$key]);
            }else{
                $filter[$key] = str_replace("#", "", $value);
            }
        }

        $range_filter = $range_year_field = $range_year_start = $range_year_end = '';
        if ($config->range_year_field){
            $range_year_field = $config->range_year_field;
            if(isset($params['range_year_start']) and $params['range_year_start'] != "") {
                $range_year_start = $params['range_year_start'];
            }
            if(isset($params['range_year_end']) and $params['range_year_end'] != "") {
                $range_year_end = $params['range_year_end'];
            }
            if ($range_year_start != '' && $range_year_end != ''){
                $range_filter = $range_year_field . ':[' . $range_year_start . ' TO ' . $range_year_end . ']';
            }
        }

        $is_email = (isset($params['is_email']) && $params['is_email'] === 'true' ? true : false);

        // apply view_filters
        $view_filter_param = array();
        $view_filter = '';
        $config_view_filter = ($collectionData->view_filter ? $collectionData->view_filter : null);
        if( !empty($params['view_filter']) ) {
            $view_filter_param = $params['view_filter'];
            $view_filter_array = array();
            // load view filter configuration
            $view_filter_json = file_get_contents(PATH_DATA . 'config/filter-' . $config_view_filter . '.json');
            $view_filter_config = json_decode($view_filter_json, true);

            foreach ($view_filter_param as $vf){
                $vf_data = explode("_", $vf);
                $vf_group = $vf_data[0];
                $vf_item = $vf_data[1];
                array_push($view_filter_array, $view_filter_config['filter'][$vf_group]['options'][$vf_item]['value']);
            }
            $view_filter = join(' OR ', $view_filter_array);
        }

        // wizard
        $config_wizard_list = $collectionData->wizard_list->wizard;

        // BOOKMARK SESSION

        $SESSION = $request->getSession();
        $bookmark = $SESSION->get('bookmark');
        $wizard_session = $SESSION->get('wizard_session');


        // initial filter (defined on configuration file)
        $initial_filter = html_entity_decode($collectionData->initial_filter);
        // user selected filters (cluster and where)
        $user_filter = array_merge($filter, $where);

        // if is send email, needs to change the from parameter, or my selection
        if( $is_email ) {
            $email_info = array();
            foreach(array('from_name', 'from_email', 'to_email', 'subject', 'comment', 'selection') as $field) {
                if((is_array($params[$field]) and !empty($params[$field])) or ($params[$field] != "")) {
                    $email_info[$field] = $params[$field];
                }
            }

            if(isset($email_info['selection'])) {
                if($email_info['selection'] == "my_selection") {
                    $from = 1;
                    $q = '+id:("' . join(array_keys($bookmark), '" OR "') . '")';
                }
                elseif($email_info['selection'] == "all_results") {
                    $from = 1;
                    $count = 300;
                }
            }
        }

        // adjusts parameters for export operation
        if (($output == 'ris' || $output == 'csv' || $output == 'citation' || $output == 'bibtex')){
            if ($count == '-1'){
                $from = 0;
                $count = $config->documents_per_page * 10;  //increase count for export
            }elseif ($count == 'selection'){
                $from = 0;
                $count = $config->documents_per_page * 100; // max for selection option
                $q = '+id:("' . join(array_keys($bookmark), '" OR "') . '")';
            }else{
                $export_total = $from + $count;
            }
        }

        // USER PREFERENCE FILTERS SESSION
        if(!$SESSION->has("user_preference_filter")) {
            $SESSION->set('user_preference_filter', array());
        }

        $user_preference_filter = $SESSION->get('user_preference_filter');

        // add to session filters from form
        if (  isset($params['config_filter_submit']) ) {
            $user_preference_filter = $params['u_filter'];
        }

        $config_cluster_list = $collectionData->cluster_list->cluster;
        $default_cluster_list = $this->auxFunctions->getDefaultClusterList($collectionData);
        $only_translated_items_clusters = $this->auxFunctions->getOnlyTranslatedItemsClusterList($collectionData);
        $query_info_clusters = $this->auxFunctions->getShowQueryInfoClusterList($collectionData);

        $SESSION->set('user_preference_filter', $user_preference_filter);

        // HISTORY SESSION
        if(!$SESSION->has("history")) {
            $SESSION->set('history', array());
        }

        $history = $SESSION->get('history');

        // check and replace for history mark at query string
        if (strpos($q, '#') !== false){
            $q = replace_history_mark($q, $history);
        }

        // Dia response
        $search = new SearchSolr($site, $col, $count, $output, $lang);
        $search->setParam('fb', $fb);
        $search->setParam('sort', $sort_value);
        $search->setParam('initial_filter', $initial_filter);
        if ($config->request_cluster_list && $config->request_cluster_list == 'true'){
            $search->setParam('filter_list', $config_cluster_list);
        }

        $search_response = $search->search($q, $index, $user_filter, $range_filter, $view_filter, $from);
        $result = json_decode($search_response, true);

        if ( isset($result['diaServerResponse'][0]) ){
            $srv_response = $result['diaServerResponse'][0]['responseHeader'];
            // detailed query
            $solr_param_q = isset($srv_response['params']['q']) ? $srv_response['params']['q'] : null;
            $solr_param_fq = isset($srv_response['params']['fq']) ? $srv_response['params']['fq'] : null;
        }else{
            $solr_param_q = null;
            $solr_param_fq = null;
        }

        // limpa initial filter da variavel solr_param_fq
        if ($initial_filter != ''){
            $solr_param_fq = preg_replace('~\('. $initial_filter . '\) AND | AND \('. $initial_filter . '\) |\('. $initial_filter . '\)~', '', $solr_param_fq);
        }
        $detailed_query = '';
        if ($solr_param_q != '*:*' && $solr_param_fq != ''){
            $detailed_query = $solr_param_q . " AND " . $solr_param_fq ;
        }elseif ($solr_param_q != '*:*'){
            $detailed_query = $solr_param_q;
        }elseif ($solr_param_fq != ''){
            $detailed_query = $solr_param_fq;
        }
        // replace NOT (returned from lucene parser) by AND NOT
        if ($detailed_query != ''){
            $detailed_query = preg_replace('/(?<!AND) NOT /', ' AND NOT ', $detailed_query);
        }

        // get texts used in template
        $texts = $this->cache->get_texts($instance, $lang);

        // pagination and template variables
        $pag = array();
        $template_vars = array();

        if ( isset($result['diaServerResponse'][0]['response']['docs']) )  {
            $pag['total'] = $result['diaServerResponse'][0]['response']['numFound'];
            $pag['total_formatted'] = number_format($pag['total'], 0, ',', '.');
            $pag['start'] = $result['diaServerResponse'][0]['response']['start'];
            $pag['total_pages'] = ($pag['total'] % $count == 0) ? (int)($pag['total']/$count) : (int)($pag['total']/$count+1);
            $pag['count'] = $count;

            $template_vars['detailed_query'] = $detailed_query;
            $template_vars['docs'] = $result['diaServerResponse'][0]['response']['docs'];
            $template_vars['clusters'] = $result['diaServerResponse'][0]['facet_counts']['facet_fields'];

        }else{
            $pag['total'] = 0;
            $pag['total_pages'] = 1;
            $pag['total_formatted'] = 1;

            $template_vars['detailed_query'] = '';
            $template_vars['docs'] = array();
            $template_vars['clusters'] = array();
        }

        $range_min = (($page-5) > 0) ? $page-5 : 1;
        $range_max = (($range_min+5) > $pag['total_pages']) ? $pag['total_pages'] : $range_min+5;
        $range_max_mobile = (($range_min+3) > $pag['total_pages']) ? $pag['total_pages'] : $range_min+3;
        $pag['pages'] = range($range_min, $range_max);
        $pag['pages_mobile'] = range($range_min, $range_max_mobile);

        // check if query alread register in session
        $query_id = md5($detailed_query);

        if($q != "" and $this->auxFunctions->find_in_array($history, 'id', $query_id) == false and $output == 'site') {
            $new_history['id'] = $query_id;
            $new_history['q'] = $params['q'];
            $new_history['detailed_query'] = $detailed_query;
            $new_history['filter'] = $filter;
            $new_history['total'] = ($pag['total_formatted'] > 0 ? $pag['total_formatted'] : 0);
            $new_history['time'] = time();

            array_push($history, $new_history);
            $SESSION->set('history', $history);
            $SESSION->save();
        }

        $template_vars['bookmark'] = $bookmark;
        $template_vars['user_preference_filter'] = (array) $user_preference_filter;
        $template_vars['filters'] = $filter;
        $template_vars['filters_formatted'] = $filter;
        $template_vars['config_view_filter'] = $config_view_filter;
        $template_vars['view_filter'] = $view_filter_param;
        $template_vars['lang'] = $lang;
        $template_vars['q'] = $q;
        $template_vars['fb'] = $fb;
        $template_vars['sort'] = $sort;
        $template_vars['format'] = $format;
        $template_vars['from'] = $from;
        $template_vars['count'] = $count;
        $template_vars['output'] = $output;
        $template_vars['collectionData'] = $collectionData;
        $template_vars['params'] = $params;
        $template_vars['pag'] = $pag;
        $template_vars['config'] = $config;
        $template_vars['texts'] = $texts;
        $template_vars['current_url'] = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
        $template_vars['display_file'] = "result-format-" . $format . ".html";
        $template_vars['debug'] = (isset($params['debug'])) ? $params['debug'] : false;
        $template_vars['config_cluster_list'] = $config_cluster_list;
        $template_vars['default_cluster_list'] = $default_cluster_list;
        $template_vars['only_translated_items_clusters'] = $only_translated_items_clusters;
        $template_vars['query_info_clusters'] = $query_info_clusters;
        $template_vars['history'] = $history;
        $template_vars['index'] = $index;
        $template_vars['parsing_filters'] = $solr_param_fq;
        $template_vars['page'] = $page;
        $template_vars['current_page'] = 'result';
        $template_vars['range_year_start'] = $range_year_start;
        $template_vars['range_year_end'] = $range_year_end;
        $template_vars['config_wizard_list'] = $config_wizard_list;
        $template_vars['wizard_session'] = $wizard_session;
        $template_vars['max_export_records'] = $config->max_export_records;

        // if is send email
        if( $is_email ) {

            $template_vars['email_info'] = $email_info;

            $email_content = $this->renderView(TEMPLATE_NAME . '/export-email.html', $template_vars);
            $from_email = isset($email_info['from_email']) ? $email_info['from_email'] : $_ENV['EMAIL_FROM'];
            $from_name = (isset($email_info['name']) ? $email_info['name'] : '') . ' (' . $texts['BVS_HOME'] . ')';
            $subject = (isset($email_info['subject']) ? $email_info['subject'] : $texts['SEARCH_HOME'] . ' | ' . $texts['BVS_TITLE']);

            # check if param email (to) is in the format of email list separated by ;
            if ( !is_array($email_info['to_email']) && strpos($email_info['to_email'], ';') !== false) {
                $to_email = explode(';', $email_info['to_email']);
            }else{
                $to_email = $email_info['to_email'];
            }

            $email = (new Email())
                ->from(new Address($_ENV['EMAIL_FROM'], $from_email))
                ->to($to_email)
                ->replyTo($from_email)
                ->priority(Email::PRIORITY_HIGH)
                ->subject($subject)
                ->html($email_content);

            try {
                $this->mailer->send($email);
                $template_vars['flash_message'] = 'MAIL_SUCCESS';
            } catch (TransportExceptionInterface $e) {
                $template_vars['flash_message'] = 'MAIL_FAIL';
            }
        }

        // Impact Measurement
        $im_api = 'https://im.bireme.org/api/main/?format=json&code=';
        $im_scope = strval($config->impact_measurement_cookie_domain_scope);
        $im_code = strval($config->impact_measurement_code);

        $cookie_im = $request->cookies->get('impact_measurement');
        if ( !$cookie_im ) {
            $impact_measurement_cookie = md5(uniqid(rand(), true));
            setcookie("impact_measurement", $impact_measurement_cookie, (time() + (10 * 365 * 24 * 60 * 60)), '/', $im_scope);

            $domains = array(
                '.bvs.br' => $config->impact_measurement_bvs_cookie_domain,
                '.bvsalud.org' => $config->impact_measurement_bvsalud_cookie_domain,
                '.bireme.org' => $config->impact_measurement_bireme_cookie_domain
            );

            if ( array_key_exists($im_scope, $domains) ) {
                $im_cookie = array();
                unset($domains[$im_scope]);

                foreach ($domains as $domain => $url) {
                    if ( ! empty($url) ) {
                        $im_cookie[] = $url.'/setcookie.php?im_cookie='.$impact_measurement_cookie.'&im_code='.$im_code.'&im_data='.base64_encode($im_api);
                    }
                }

                $template_vars['im_cookie'] = $im_cookie;
            }
        }

        //log_user_action($lang, $col, $site, $q, $index, $params['where'], $solr_param_fq,
        //                $page, $output, $SESSION->getId(), $format, $sort);


        // output
        switch($output) {

            case "xml":
                return new Response($search_response, 200, array("Content-type" => "text/xml"));
                break;

            case "json":
                return new Response($search_response, 200, array("Content-type" => "application/json"));
                break;

            case "print":
                return $this->render(TEMPLATE_NAME . '/print.html', $template_vars);
                break;

            case "rss":
                $template_vars['search_url'] = 'http://' . $_SERVER['HTTP_HOST'] . str_replace('output=rss', 'output=site', $_SERVER['REQUEST_URI']);
                $response = new Response($this->renderView(TEMPLATE_NAME . '/export-rss.html', $template_vars));
                $response->headers->set('Content-type', 'text/xml');
                return $response;
                break;

            case "metasearch":
                $response = new Response($this->renderView(TEMPLATE_NAME . '/export-metasearch.html', $template_vars));
                $response->headers->set('Content-type', 'text/xml');
                return $response;
                break;

            case "citation":
                $export_template = 'export-citation.html';
                $export_filename = 'export.txt';
                $content_type = 'text/plain';
            case "ris":
                if ( !isset($export_template) ){
                    $export_template = 'export-ris.html';
                    $export_filename = 'export.ris';
                    $content_type = 'text/plain';
                }
            case "bibtex":
                if ( !isset($export_template) ){
                    $export_template = 'export-bibtex.html';
                    $export_filename = 'export.bib';
                    $content_type = 'text/plain';
                }
            case "csv":
                if ( !isset($export_template) ){
                    $export_template = 'export-csv.txt';
                    $export_filename = 'export.csv';
                    $content_type = 'text/csv';
                }
                if ( !isset($export_total) ){
                    $config_max_export_records = intval($config->max_export_records);
                    $export_total = ( ($config_max_export_records > 0 && $config_max_export_records < $pag['total'] ) ? $config_max_export_records : $pag['total'] );
                }

                $export_content = "";
                $from++;  //set from to 1 to get corret next result set

                $response = new Response();
                if ($output_as_file == true){
                    $response->headers->set('Content-Encoding', 'UTF-8');
                    $response->headers->set('Content-Type', $content_type .'; charset=UTF-8');
                    header('Content-Disposition: attachment; filename=' . $export_filename);
                    echo "\xEF\xBB\xBF"; // UTF-8 BOM
                }

                $handle = fopen('php://output', 'w');
                ob_clean();

                while ($from <= $export_total){
                    // export results
                    $export_content_range = $this->render(TEMPLATE_NAME . '/' . $export_template, $template_vars);
                    // normalize line end
                    if ($output == 'csv' || $output == 'ris'){
                        $export_content_range = preg_replace("/\n/", "", $export_content_range);                 //Remove line end
                        $export_content_range = preg_replace("/#BR#/", "\r\n", $export_content_range);           //Windows Line end
                    }else{
                        $export_content_range = normalize_line_end($export_content_range);
                    }
                    // put current export range to output
                    fputs($handle, $export_content_range);

                    // set next from
                    $from = $from + $count;
                    // get next result set
                    $search = new SearchSolr($site, $col, $count, $output, $lang);
                    $search->setParam('fb', $fb);
                    $search->setParam('sort', $sort_value);
                    $search->setParam('initial_filter', $initial_filter );

                    $search_response = $search->search($q, $index, $user_filter, $range_filter, $view_filter, $from);
                    $result = json_decode($search_response, true);

                    $template_vars['from'] = $from;
                    $template_vars['config'] = $config;
                    $template_vars['texts'] = $texts;
                    $template_vars['current_url'] = $_SERVER['REQUEST_URI'];
                    $template_vars['display_file'] = "result-format-" . $format . ".html";
                    $template_vars['debug'] = (isset($params['debug'])) ? $params['debug'] : false;
                    $template_vars['docs'] = $result['diaServerResponse'][0]['response']['docs'];

                }
                ob_flush();
                fclose($handle);

                return $response->sendHeaders();
                break;

            default:
                $check_mobile = $config->mobile_version;
                $view = ( isset($params['view']) ? $params['view'] : '');

                if( $view == 'desktop' ) {   // forced by user desktop version
                    $view = '';              // use default view
                }else{
                    if ($check_mobile == 'true'){    // activate alternate template for mobile
                        $detect = new MobileDetect();
                        if ($view == 'mobile' || ($detect->isMobile() && !$detect->isTablet()) )   {
                            $view = 'mobile';
                        }
                    }
                }

                return $this->render(TEMPLATE_NAME . $view . '/index.html', $template_vars);
        }

    }
}