<?php

require_once 'lib/class/dia.class.php';
include 'lib/class/log.class.php';
include 'lib/Mobile_Detect.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->match('/', function (Request $request) use ($app, $DEFAULT_PARAMS, $config) {

    global $lang, $texts;

    $params = array_merge(
        $app['request']->request->all(),
        $app['request']->query->all()
    );

    // if magic quotes gpc is on, this function clean all parameters and
    // results that was modified by the directive
    if (get_magic_quotes_gpc()) {
        $process = array(&$params);
        while (list($key, $val) = each($process)) {
            foreach ($val as $k => $v) {
                unset($process[$key][$k]);
                if (is_array($v)) {
                    $process[$key][stripslashes($k)] = $v;
                    $process[] = &$process[$key][stripslashes($k)];
                } else {
                    $process[$key][stripslashes($k)] = stripslashes($v);
                }
            }
        }
        unset($process);
    }

    $collectionData = $DEFAULT_PARAMS['defaultCollectionData'];
    $site = $DEFAULT_PARAMS['defaultSite'];
    $col = $DEFAULT_PARAMS['defaultCollection'];

    $fb = "";
    if(isset($params['fb']) and $params['fb'] != "") {
        $fb = $params['fb'];
    }

    $count = $config->documents_per_page;
    if(isset($params['count'])and $params['count'] != "") {
        $count = $params['count'];
    }

    $output = "site";
    if(isset($params['output']) and $params['output'] != "") {
        $output = $params['output'];
    }

    $lang = $DEFAULT_PARAMS['lang'];
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
    }

    $sort = "";
    if(isset($params['sort']) and $params['sort'] != "Array") {
        
        $sort = $params['sort'];
        
        $exists = false;
        foreach($collectionData->sort_list->sort as $item) {
            if($sort == $item->name) {
                $exists = true;
                $sort = (string) urlencode($item->value);
                break;
            }
        }

        if(!$exists)                    
            $sort = "";
        
    }
    if ($sort == ""){
        $sort = getDefaultSort($collectionData, $q);
    }

    $format = $DEFAULT_PARAMS['defaultDisplayFormat'];
    if(isset($params['format'])and $params['format'] != "") {
        $format = $params['format'];
    }

    $filter = array();
    if(isset($params['filter']) and $params['filter'] != "Array") {
        $filter = $params['filter'];
    }

    foreach($filter as $key => $value) {
        if($value == "Array" or $value == "Array#" or $value == "") {
            unset($filter[$key]);
        }

        $filter[$key] = str_replace("#", "", $value);
    }


    // bookmark
    $SESSION = $app['session'];
    $SESSION->start();
    $bookmark = $SESSION->get('bookmark');  

    // initial filter (defined on configuration file)
    $initial_filter = html_entity_decode($collectionData->initial_filter);
    // user selected filters (cluster and where)
    $user_filter = array_merge($filter, $where);

    // if is send email, needs to change the from parameter, or my selection
    if(isset($params['is_email'])) {

        $email = array();
        foreach(array('name', 'your_email', 'email', 'subject', 'comment', 'selection') as $field) {
            if((is_array($params[$field]) and !empty($params[$field])) or ($params[$field] != "")) {
                $email[$field] = $params[$field];
            }
        }

        if(isset($email['selection'])) {
            if($email['selection'] == "my_selection") {
                
                $from = 1;
                $q = '+id:("' . join(array_keys($bookmark), '" OR "') . '")';
            } 
            elseif($email['selection'] == "all_results") {
                $from = 1;
                $count = 300;
            }
        }
    }
    
    // Dia response
    $dia = new Dia($site, $col, $count, $output, $lang);
    $dia->setParam('fb', $fb);
    $dia->setParam('sort', $sort);
    $dia->setParam('initial_filter', $initial_filter );

    $dia_response = $dia->search($q, $index, $user_filter, $from);
    $result = json_decode($dia_response, true);
   
    // detailed query
    $solr_param_q = $result['diaServerResponse'][0]['responseHeader']['params']['q'];
    $solr_param_fq = $result['diaServerResponse'][0]['responseHeader']['params']['fq'];
    if ($solr_param_q != '*:*' && $solr_param_fq != ''){
        $detailed_query = $solr_param_q . " AND " . $solr_param_fq ;
    }elseif ($solr_param_q != '*:*'){
        $detailed_query = $solr_param_q;
    }elseif ($solr_param_fq != ''){
        $detailed_query = $solr_param_fq;    
    }

    // translate
    $texts = parse_ini_file(TRANSLATE_PATH . $lang . "/texts.ini", true);
    
    // pagination
    $pag = array();
    if ( isset($result['diaServerResponse'][0]['response']['docs']) )  {
        $pag['total'] = $result['diaServerResponse'][0]['response']['numFound'];
        $pag['total_formatted'] = number_format($pag['total'], 0, ',', '.');
        $pag['start'] = $result['diaServerResponse'][0]['response']['start'];    
        $pag['total_pages'] = (($pag['total']/$count) % 10 == 0) ? (int)($pag['total']/$count) : (int)($pag['total']/$count+1);
        $pag['count'] = $count;
    }
    $range_min = (($page-5) > 0) ? $page-5 : 1;
    $range_max = (($range_min+10) > $pag['total_pages']) ? $pag['total_pages'] : $range_min+10;
    $range_max_mobile = (($range_min+3) > $pag['total_pages']) ? $pag['total_pages'] : $range_min+3;
    $pag['pages'] = range($range_min, $range_max);
    $pag['pages_mobile'] = range($range_min, $range_max_mobile);

    // HISTORY APP
    $SESSION->start();
    if(!$SESSION->has("history")) {
        $SESSION->set('history', array());   
    }
    
    $history = $SESSION->get('history');

    // if doesn't exists a search history with this query, was created
    if($q != "" and !isset($history[$q])) {
        $history[$q]['url'] = $_SERVER['REQUEST_URI'];
        $history[$q]['id'] = md5($q . $_SERVER['REQUEST_URI']);
        $history[$q]['time'] = time();
    } 

    // if exists a search history with this query, but the paramaters are different,
    // update this parameters
    else if(isset($history[$q]) and $history[$q] != $q) {
        $history[$q]['url'] = $_SERVER['REQUEST_URI'];
        $history[$q]['id'] = md5($q . $_SERVER['REQUEST_URI']);
        $history[$q]['time'] = time();    
    }
    
    $SESSION->set('history', $history);   
    $SESSION->save();
    

    // output vars
    $output_array = array();
    $output_array['bookmark'] = $bookmark;
    $output_array['filters'] = $filter;
    $output_array['filters_formatted'] = $filter;
    $output_array['lang'] = $lang;
    $output_array['q'] = $q;
    $output_array['sort'] = $sort;
    $output_array['format'] = $format;
    $output_array['from'] = $from;
    $output_array['count'] = $count;    
    $output_array['output'] = $output;
    $output_array['collectionData'] = $collectionData;
    $output_array['params'] = $params;
    $output_array['pag'] = $pag;
    $output_array['config'] = $config;
    $output_array['texts'] = $texts;
    $output_array['current_url'] = $_SERVER['REQUEST_URI'];
    $output_array['display_file'] = "result-format-" . $format . ".html";
    $output_array['debug'] = (isset($params['debug'])) ? $params['debug'] : false;
    if ( isset($result['diaServerResponse'][0]['response']['docs']) )  {
        $output_array['detailed_query'] = $detailed_query;
        $output_array['docs'] = $result['diaServerResponse'][0]['response']['docs'];
        $output_array['clusters'] = $result['diaServerResponse'][0]['facet_counts']['facet_fields'];
    }

    // if is send email
    if(isset($params['is_email'])) {

        $output_array['email'] = $email;

        $render = $app['twig']->render('export-email.html', $output_array);        
        $subject = ($email['subject'] != '' ? $email['subject'] : $texts['SEARCH_HOME'] . ' | ' . $texts['BVS_TITLE']);

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom(array(FROM_MAIL => $email['name'] . ' (' . $texts['BVS_HOME'] . ')') )
            ->setTo($email['email'])
            ->setBody($render, 'text/html');

        if ( $app['mailer']->send($message) ){
            $output_array['flash_message'] = 'MAIL_SUCCESS';
        }else{
            $output_array['flash_message'] = 'MAIL_FAIL';
        }

    }  

    log_user_action($lang, $col, $site, $q, $index, $params['where'], $solr_param_fq, 
                    $page, $output, $SESSION->getId(), $format, $params['sort']);

    // output
    switch($output) {
        
        case "xml": case "sol":
            return new Response($dia_response, 200, array("Content-type" => "text/xml"));
            break;
            
        case "print":
            return $app['twig']->render('print.html', $output_array); 
            break;

        case "rss":
            $response = new Response($app['twig']->render('export-rss.html', $output_array));
            $response->headers->set('Content-type', 'text/xml');
            return $response->sendHeaders();
            break;

        case "metasearch":
            $response = new Response($app['twig']->render('export-metasearch.html', $output_array));
            $response->headers->set('Content-type', 'text/xml');
            return $response->sendHeaders();
            break;

        case "ris":
            $response = new Response($app['twig']->render('export-ris.html', $output_array));
            $response->headers->set('Content-Type', 'application/force-download');
            header('Content-Disposition: attachment; filename=export.ris');             
            return $response->sendHeaders();
            break;

        case "csv":
            $csv = $app['twig']->render('export-csv.txt', $output_array);
            $csv = preg_replace("/\n/", " ", $csv);
            $csv = preg_replace("/#BR#/", "\n", $csv);
            $response = new Response($csv);
            $response->headers->set('Content-Type', 'text/csv; charset=utf-8');            
            header('Content-Disposition: attachment; filename=export.csv');             
            return $response->sendHeaders();
            break;

        case "citation":
            $response = new Response($app['twig']->render('export-citation.html', $output_array));
            $response->headers->set('Content-type', 'application/force-download');
            header('Content-Disposition: attachment; filename=export.txt');             
            return $response->sendHeaders();
            break;

        default: 
            $check_mobile = (bool)$config->mobile_version;
            $view = $params['view'];
            if( !isset($view) || $view == 'desktop' ) {
                $view = ''; // default view desktop
            }    

            if ($check_mobile){                
                $detect = new Mobile_Detect();
                if ($view == 'mobile' || $detect->isMobile())   {
                    $view = 'mobile';
                }
            }

            return $app['twig']->render($view . '/index.html', $output_array);

            break;
    }

});

?>