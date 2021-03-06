<?php

class Dia
{
    var $DIASERVER = "";
    var $param = array();

    function __construct($site, $collection, $count, $output, $lang ){
        global $config;

        $this->param["site"]  = $site;
        $this->param["col"]   = $collection;
        $this->param["count"] = $count;
        $this->param["output"]= $output;
        $this->param["lang"]  = $lang;
        $this->param["initial_filter"]  = "";
        $this->setDiaServer( $config->search_server );

        return;
    }


    function setDiaServer($server){
        $this->DIASERVER = $server;
        return;
    }

    function setParam($param, $value){
        if ($value != null && $value != ""){
            $this->param[$param] = $value;
        }
        return;
    }

    function search($query, $index = '', $user_filter = array(), $range_filter = '', $view_filter = '', $from = 0){
        $this->param["op"] = "search";
        $this->param["q"] = $query;
        $this->param["index"] = $index;

        $initial_filter = $this->param['initial_filter'];

        if ($from != "" && $from > 0){
            $this->param["start"] = ($from - 1);
        }

        //remove empty or null values on user_filter array
        $user_filter = array_filter($user_filter);

        if ( isset($initial_filter) && $initial_filter != '' ){
            if ( !empty($user_filter) ){
                $user_filter_parsed = $this->mountFilterParam($user_filter);
                $filter = $initial_filter . " AND ( " . $user_filter_parsed . ")";
            }else{
                $filter = $initial_filter;
            }
        }else{
            $filter = $this->mountFilterParam($user_filter);
        }

        if ( $range_filter != '' ){
            $filter = ($filter != '' ? $filter . " AND " : '');
            $filter.= "(" . $range_filter . ")";
        }

        if ( $view_filter != '' ){
            $filter = ($filter != '' ? $filter . " AND " : '');
            $filter.= "(" . $view_filter . ")";
        }

        $this->param["fq"] = $filter;

        $filter_list = $this->param["filter_list"];
        if (isset($filter_list) && !empty($filter_list)){
            foreach ($filter_list as $filter){
                $filter_name = (string) $filter;
                $this->param["facet.field"][] = (string) $filter;
                if (isset($filter['sort']) && $filter['sort'] != ''){
                    $this->param['f.' . $filter_name . '.facet.sort'] = $filter['sort'];
                }
                if (isset($filter['limit']) && $filter['limit'] != ''){
                    $this->param['f.' . $filter_name . '.facet.limit'] = $filter['limit'];
                }

            }
        }

        $searchUrl = $this->requestUrl();

        $result = $this->documentPost( $searchUrl );
        return trim($result);
    }

    function related($id){
        $this->param["op"] = "related";
        $this->param["id"] = $id;

        $searchUrl = $this->requestUrl();

        $result = $this->documentPost( $searchUrl );
        return trim($result);
    }

    function morelikethat($service_url){

        $result = $this->documentPost( $service_url );
        return trim($result);
    }



    function mountFilterParam($filter){

        //remove valores vazios do array
        $filter = $this->cleanArray($filter);

        $new_filter = array();
        foreach(array_keys($filter) as $name) {
            ## FIX: actually mh_cluster and pt_cluster facets are DeCS codified that are decodified by the controller
            ## its necessary use the normal prefix mh or pt when do a search
            $search_prefix  = $name;
            if ( $name == 'pt_cluster' || $name == 'mh_cluster' || $name == 'mj_cluster' ){
                $search_prefix = str_replace('_cluster', '', $name);
            }
            if ($filter[$name][0] == '*'){
                $new_filter[] = $search_prefix . ':*';
            }else{
                $new_filter[] = $search_prefix . ':("' . join('" OR "', $filter[$name]) . '")';
            }
        }

        $filter_query = join(" AND ",$new_filter);

        return stripslashes($filter_query);
    }


    function requestUrl()   {
        $urlParam = "";
        reset($this->param);

        foreach ($this->param as $key => $value){
            if (is_array($this->param[$key])){
                foreach ($this->param[$key] as $value_param){
                    $urlParam .= "&" . $key . "=" . urlencode($value_param);
                }

            }else{
                if ($value != ""){
                    $urlParam .= "&" . $key . "=" . urlencode($value);
                }
            }
        }
        $protocol = (substr($this->DIASERVER, 0, 4) != "http" ? "http://" : "");
        $requestUrl = $protocol . $this->DIASERVER . "/iahx-controller/?" . substr($urlParam,1);

        return $requestUrl;
    }

    function documentPost( $url )
    {
        global $debug;
        // Strip URL
        $url_parts = parse_url($url);
        $host = $url_parts["host"];
        $port = (isset($url_parts["port"]) ? $url_parts["port"] : "80");
        $path = $url_parts["path"];
        $query = $url_parts["query"];
        $result = "";
        $timeout = 10;
        $contentLength = strlen($query);

        if (isset($_REQUEST['debug'])){
            print "<b>request:</b> " . $url . "<br/>";
        }
        // Generate the request header
        $ReqHeader =
            "POST $path HTTP/1.0\r\n".
            "Host: $host\r\n".
            "User-Agent: PostIt\r\n".
            "Content-Type: application/x-www-form-urlencoded; charset=UTF-8\r\n".
            "Content-Length: $contentLength\r\n\r\n".
            "$query\n";
        // Open the connection to the host

        if (substr($url, 0, 5) === "https"){
            $fp = fsockopen("ssl://{$host}", 443, $errno, $errstr, $timeout);
        }else{
            $fp = fsockopen($host, $port, $errno, $errstr, $timeout);
        }

        fputs( $fp, $ReqHeader );
        if ($fp) {
            while (!feof($fp)){
                $result .= fgets($fp, 4096);
            }
        }

        return substr($result,strpos($result,"\r\n\r\n"));
    }

    function cleanArray($array) {
        foreach ($array as $index => $value) {
            if (empty($value)) unset($array[$index]);
        }
        return $array;
    }

    function getFilterParam(){
        return $this->param["fq"];
    }

}

?>
