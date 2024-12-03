<?php

namespace App\Service;

class AuxFunctions
{
    // translation
    function translate($label, $group=NULL) {
        global $texts, $lang;

        // labels on texts.ini must be array key without spaces
        $label_norm = preg_replace('/[&,\'\s]+/', '_', $label);

        if($group == NULL) {
            if(isset($texts[$label_norm]) and $texts[$label_norm] != "") {
                return $texts[$label_norm];
            }
        } else {
            if(isset($texts[$group][$label_norm]) and $texts[$group][$label_norm] != "") {
                return $texts[$group][$label_norm];
            }
        }

        // case translation not found return original label ucfirst
        return ucfirst($label);
    }

    // return if a label has translation at texts.ini
    function has_translation($label, $group=NULL) {
        global $texts, $lang;

        // labels on texts.ini must be array key without spaces
        $label_norm = preg_replace('/[&,\'\s]+/', '_', $label);

        if($group == NULL) {
            return (isset($texts[$label_norm]) and $texts[$label_norm] != "");
        } else {
            return (isset($texts[$group][$label_norm]) and $texts[$group][$label_norm] != "");
        }
    }


    // funcao retirada da pagina http://www.php.net/utf8_encode
    function isUTF8($string){
        if (is_array($string)) {
            $enc = implode('', $string);
            return @!((ord($enc[0]) != 239) && (ord($enc[1]) != 187) && (ord($enc[2]) != 191));
        }else{
            return (utf8_encode(utf8_decode($string)) == $string);
        }
    }

    function getWhereFilter($colectionData, $where){
        $whereFilter = "";
        if (isset($colectionData->where_list)){
            foreach($colectionData->where_list->where as $whereOpt  ){
                if ($whereOpt->name == $where){
                    $whereFilter = $whereOpt->filter;
                    break;
                }
            }
        }
        return html_entity_decode($whereFilter);
    }

    function getSortValue($colectionData, $sort){
        $whereFilter = "";
        if ( isset($colectionData->sort_list) ){
            foreach( $colectionData->sort_list->sort as $sortItem  ){
                if ($sortItem->name == $sort || $sortItem->value == $sort){
                    $sortValue = $sortItem->value;
                    break;
                }
            }
        }
        return $sortValue;
    }

    function getDefaultSort($colectionData, $q){
        $sortValue = "";
        $count = 0;
        if ( isset($colectionData->sort_list) ){
            foreach( $colectionData->sort_list->sort as $sortItem  ){
                // seleciona primeito item do config como default
                if ( $count == 0){
                    $sortValue = $sortItem->value;
                }
                // caso a query esteja vazia verifica se o item possue default_for_empty_query
                if ( $q == '' && isset($sortItem->default_for_empty_query) ){
                    $sortValue = $sortItem->value;
                }
                $count++;
            }
        }
        return $sortValue;
    }

    function getDefaultWhere($colectionData, $q){
        $whereValue = '';
        $param_where = '';
        $where = array();
        $count = 0;
        if ( isset($colectionData->where_list) ){
            foreach( $colectionData->where_list->where as $whereItem  ){
                // seleciona primeito item do config como default
                if ( $count == 0 && $whereItem->filter != ''){
                    $whereValue = $whereItem->filter;
                    $param_where = $whereItem->name;
                }
                // caso a query esteja vazia verifica se o item possue default_for_empty_query
                if ( $q == '' && isset($whereItem->default_for_empty_query) && $whereItem->filter != '' ){
                    $whereValue = $whereItem->filter;
                    $param_where = $whereItem->name;
                }
                $count++;
            }
        }
        // always return where in array format
        if( !empty($whereValue) ) {
            $where = explode(":", $whereValue);
            $where[1] = str_replace('("', "", $where[1]);
            $where[1] = str_replace('")', "", $where[1]);
            $where = array($where[0] => array($where[1]));
        }else{
            $where = array();
        }
        return array( $param_where,$where ) ;
    }

    function getDefaultClusterList($colectionData){
        $default_clusters = array();
        if ( isset($colectionData->cluster_list) ){
            foreach( $colectionData->cluster_list->cluster as $cluster  ){
                if ($cluster['default'] == 'true'){
                    $default_clusters[] = (string)$cluster;
                }
            }
            // if not is set as default make all clusters of the list default
            if ( empty($default_clusters) ){
                foreach( $colectionData->cluster_list->cluster as $cluster  ){
                    $default_clusters[] = (string)$cluster;
                }
            }
        }

        return $default_clusters;
    }

    function getOnlyTranslatedItemsClusterList($colectionData){
        $only_translated_items_clusters = array();

        if ( isset($colectionData->cluster_list) ){
            foreach( $colectionData->cluster_list->cluster as $cluster  ){
                if ($cluster['show_only_translated_items'] == 'true'){
                    $only_translated_items_clusters[] = (string)$cluster;
                }
            }
        }

        return $only_translated_items_clusters;
    }

    function getShowQueryInfoClusterList($colectionData){
        $query_info_clusters = array();

        if ( isset($colectionData->cluster_list) ){
            foreach( $colectionData->cluster_list->cluster as $cluster  ){
                if ($cluster['query_info'] == 'true'){
                    $query_info_clusters[] = (string)$cluster;
                }
            }
        }

        return $query_info_clusters;
    }

    function getCollapsedClusterList($colectionData){
        $collapsed_cluster_list = array();

        if ( isset($colectionData->cluster_list) ){
            foreach( $colectionData->cluster_list->cluster as $cluster  ){
                if ($cluster['collapsed'] == 'true'){
                    $collapsed_cluster_list[] = (string)$cluster;
                }
            }
        }

        return $collapsed_cluster_list;
    }


    // function to work when PHP directive magic_quotes_gpc is OFF
    function addslashes_array($a){
        if(is_array($a)){
            foreach($a as $n=>$v){
                $b[$n]=addslashes_array($v);
            }
            return $b;
        }else{
            if ($a != ''){
                return addslashes($a);
            }
        }
    }

    /* Log User Actions */

    function log_user_action($lang, $col, $site, $query, $index, $where, $filter, $page, $output, $session_id, $format ='', $sort = ''){
        global $config, $DEFAULT_PARAMS;

        // set default values
        $col = ($col != '' ? $col : $DEFAULT_PARAMS['defaultCollection']);
        $site = ($site != '' ? $site : $DEFAULT_PARAMS['defaultSite']);
        $query = ($query != ''? $query : "*");
        $index = ($index != ''? $index : "*");
        $where = ($where != ''? $where : "*");
        $filter = ($filter != ''? $filter : "*");
        $page = ($page != ''? $page : "1");
        $output = ($output != ''? $output : "site");

        // log user action
        if ($config->log_user_search == 'true' ){
            $log = new Log();
            $log->fields['ip']   = $_SERVER["REMOTE_ADDR"];
            $log->fields['lang'] = $lang;
            $log->fields['col']  = $col;
            $log->fields['site'] = $site;
            $log->fields['query']= $query;
            $log->fields['index']= $index;
            $log->fields['where']= $where;
            $log->fields['filter'] = $filter;
            $log->fields['page'] = $page;
            $log->fields['output'] = $output;
            $log->fields['referer'] = (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '');
            $log->fields['session'] = $session_id;
            $log->fields['format'] = $format;
            $log->fields['sort'] = $sort;

            $log->writeLog();
        }


    }

    // remove accents of a UTF-8 string
    function remove_accents($string) {
        if ( !preg_match('/[\x80-\xff]/', $string) )
            return $string;

        $chars = array(
        // Decompositions for Latin-1 Supplement
        chr(195).chr(128) => 'A', chr(195).chr(129) => 'A',
        chr(195).chr(130) => 'A', chr(195).chr(131) => 'A',
        chr(195).chr(132) => 'A', chr(195).chr(133) => 'A',
        chr(195).chr(135) => 'C', chr(195).chr(136) => 'E',
        chr(195).chr(137) => 'E', chr(195).chr(138) => 'E',
        chr(195).chr(139) => 'E', chr(195).chr(140) => 'I',
        chr(195).chr(141) => 'I', chr(195).chr(142) => 'I',
        chr(195).chr(143) => 'I', chr(195).chr(145) => 'N',
        chr(195).chr(146) => 'O', chr(195).chr(147) => 'O',
        chr(195).chr(148) => 'O', chr(195).chr(149) => 'O',
        chr(195).chr(150) => 'O', chr(195).chr(153) => 'U',
        chr(195).chr(154) => 'U', chr(195).chr(155) => 'U',
        chr(195).chr(156) => 'U', chr(195).chr(157) => 'Y',
        chr(195).chr(159) => 's', chr(195).chr(160) => 'a',
        chr(195).chr(161) => 'a', chr(195).chr(162) => 'a',
        chr(195).chr(163) => 'a', chr(195).chr(164) => 'a',
        chr(195).chr(165) => 'a', chr(195).chr(167) => 'c',
        chr(195).chr(168) => 'e', chr(195).chr(169) => 'e',
        chr(195).chr(170) => 'e', chr(195).chr(171) => 'e',
        chr(195).chr(172) => 'i', chr(195).chr(173) => 'i',
        chr(195).chr(174) => 'i', chr(195).chr(175) => 'i',
        chr(195).chr(177) => 'n', chr(195).chr(178) => 'o',
        chr(195).chr(179) => 'o', chr(195).chr(180) => 'o',
        chr(195).chr(181) => 'o', chr(195).chr(182) => 'o',
        chr(195).chr(182) => 'o', chr(195).chr(185) => 'u',
        chr(195).chr(186) => 'u', chr(195).chr(187) => 'u',
        chr(195).chr(188) => 'u', chr(195).chr(189) => 'y',
        chr(195).chr(191) => 'y',
        // Decompositions for Latin Extended-A
        chr(196).chr(128) => 'A', chr(196).chr(129) => 'a',
        chr(196).chr(130) => 'A', chr(196).chr(131) => 'a',
        chr(196).chr(132) => 'A', chr(196).chr(133) => 'a',
        chr(196).chr(134) => 'C', chr(196).chr(135) => 'c',
        chr(196).chr(136) => 'C', chr(196).chr(137) => 'c',
        chr(196).chr(138) => 'C', chr(196).chr(139) => 'c',
        chr(196).chr(140) => 'C', chr(196).chr(141) => 'c',
        chr(196).chr(142) => 'D', chr(196).chr(143) => 'd',
        chr(196).chr(144) => 'D', chr(196).chr(145) => 'd',
        chr(196).chr(146) => 'E', chr(196).chr(147) => 'e',
        chr(196).chr(148) => 'E', chr(196).chr(149) => 'e',
        chr(196).chr(150) => 'E', chr(196).chr(151) => 'e',
        chr(196).chr(152) => 'E', chr(196).chr(153) => 'e',
        chr(196).chr(154) => 'E', chr(196).chr(155) => 'e',
        chr(196).chr(156) => 'G', chr(196).chr(157) => 'g',
        chr(196).chr(158) => 'G', chr(196).chr(159) => 'g',
        chr(196).chr(160) => 'G', chr(196).chr(161) => 'g',
        chr(196).chr(162) => 'G', chr(196).chr(163) => 'g',
        chr(196).chr(164) => 'H', chr(196).chr(165) => 'h',
        chr(196).chr(166) => 'H', chr(196).chr(167) => 'h',
        chr(196).chr(168) => 'I', chr(196).chr(169) => 'i',
        chr(196).chr(170) => 'I', chr(196).chr(171) => 'i',
        chr(196).chr(172) => 'I', chr(196).chr(173) => 'i',
        chr(196).chr(174) => 'I', chr(196).chr(175) => 'i',
        chr(196).chr(176) => 'I', chr(196).chr(177) => 'i',
        chr(196).chr(178) => 'IJ',chr(196).chr(179) => 'ij',
        chr(196).chr(180) => 'J', chr(196).chr(181) => 'j',
        chr(196).chr(182) => 'K', chr(196).chr(183) => 'k',
        chr(196).chr(184) => 'k', chr(196).chr(185) => 'L',
        chr(196).chr(186) => 'l', chr(196).chr(187) => 'L',
        chr(196).chr(188) => 'l', chr(196).chr(189) => 'L',
        chr(196).chr(190) => 'l', chr(196).chr(191) => 'L',
        chr(197).chr(128) => 'l', chr(197).chr(129) => 'L',
        chr(197).chr(130) => 'l', chr(197).chr(131) => 'N',
        chr(197).chr(132) => 'n', chr(197).chr(133) => 'N',
        chr(197).chr(134) => 'n', chr(197).chr(135) => 'N',
        chr(197).chr(136) => 'n', chr(197).chr(137) => 'N',
        chr(197).chr(138) => 'n', chr(197).chr(139) => 'N',
        chr(197).chr(140) => 'O', chr(197).chr(141) => 'o',
        chr(197).chr(142) => 'O', chr(197).chr(143) => 'o',
        chr(197).chr(144) => 'O', chr(197).chr(145) => 'o',
        chr(197).chr(146) => 'OE',chr(197).chr(147) => 'oe',
        chr(197).chr(148) => 'R',chr(197).chr(149) => 'r',
        chr(197).chr(150) => 'R',chr(197).chr(151) => 'r',
        chr(197).chr(152) => 'R',chr(197).chr(153) => 'r',
        chr(197).chr(154) => 'S',chr(197).chr(155) => 's',
        chr(197).chr(156) => 'S',chr(197).chr(157) => 's',
        chr(197).chr(158) => 'S',chr(197).chr(159) => 's',
        chr(197).chr(160) => 'S', chr(197).chr(161) => 's',
        chr(197).chr(162) => 'T', chr(197).chr(163) => 't',
        chr(197).chr(164) => 'T', chr(197).chr(165) => 't',
        chr(197).chr(166) => 'T', chr(197).chr(167) => 't',
        chr(197).chr(168) => 'U', chr(197).chr(169) => 'u',
        chr(197).chr(170) => 'U', chr(197).chr(171) => 'u',
        chr(197).chr(172) => 'U', chr(197).chr(173) => 'u',
        chr(197).chr(174) => 'U', chr(197).chr(175) => 'u',
        chr(197).chr(176) => 'U', chr(197).chr(177) => 'u',
        chr(197).chr(178) => 'U', chr(197).chr(179) => 'u',
        chr(197).chr(180) => 'W', chr(197).chr(181) => 'w',
        chr(197).chr(182) => 'Y', chr(197).chr(183) => 'y',
        chr(197).chr(184) => 'Y', chr(197).chr(185) => 'Z',
        chr(197).chr(186) => 'z', chr(197).chr(187) => 'Z',
        chr(197).chr(188) => 'z', chr(197).chr(189) => 'Z',
        chr(197).chr(190) => 'z', chr(197).chr(191) => 's',
        // Euro Sign
        chr(226).chr(130).chr(172) => 'E',
        // GBP (Pound) Sign
        chr(194).chr(163) => '');

        $string = strtr($string, $chars);

        return $string;
    }

    function normalize_line_end($s) {
        define('CR', "\r");          // Carriage Return: Mac
        define('LF', "\n");          // Line Feed: Unix
        define('CRLF', "\r\n");      // Carriage Return and Line Feed: Windows
        define('BR', '<br />' . LF); // HTML Break

        // Normalize line endings using Global
        // Convert all line-endings to Windows format
        $s = str_replace(CR, CRLF, $s);
        $s = str_replace(LF, CRLF, $s);

        // Don't allow out-of-control blank lines
        $s = preg_replace("/\n{2,}/", CRLF, $s);

        return $s;
    }

    /* Twig Extensions */
    function custom_template($filename) {
        if( file_exists(CUSTOM_TEMPLATE_PATH . $filename) ) {
            return str_replace(TEMPLATE_PATH, "", CUSTOM_TEMPLATE_PATH) . $filename;
        }

        return $filename;
    }

    function occ($params) {
        $separator = ', ';
        $translate = $output = '';

        extract($params);

        if ( is_array($element) ){
            for ($occ = 0; $occ <  count($element); $occ++) {
                if ($occ > 0){
                    $output .= $separator . " ";
                }
                if ( $translate == true ){
                $output .= translate($element[$occ], $group);
                }else{
                    $output .= $element[$occ];
                }
            }
        }else{
            if ( $translate == true ){
                $output = translate($element, $group);
            }else{
                $output = $element[$occ];
            }
        }

        return $output;

    }


    function get_preference_field_lang($element, $field, $lang_list) {
        $out_value = '';
        // start with more generic title
        if (isset($element[$field])){
            $out_value = is_array($element[$field]) ? $element[$field][0] : $element[$field];
        }
        // search for field in different languages
        foreach ($lang_list as $lang){
            $field_lang = $field . '_' . $lang;
            if (isset($element[$field_lang])){
                $out_value = $element[$field_lang];
                break;
            }
        }
        return $out_value;
    }


    function get_translated_label($target_lang,  $label, $lang, $translations, $default_lang = 'en'){
        $default_translated_label = $label;
        $translated_label = '';

        if ($lang == $target_lang){
            $translated_label = $label;
        }else{
            foreach ($translations as $translation){
                $translation_lang = substr($translation['language'], 0, 2);
                if ($translation_lang == $target_lang){
                    $translated_label = $translation['label'];
                }elseif ($translation_lang == $default_lang){
                    $default_translated_label = $translation['label'];
                }
            }
        }
        if ($translated_label == ''){
            $translated_label = $default_translated_label;
        }

        return $translated_label;

    }

    // ==============  FILTERS ===================

    function filter_substring_after($text, $needle = '-'){
        if (strpos($text, $needle) !== false){
            return substr($text, strpos($text, $needle)+strlen($needle));
        }else{
            return "";
        }
    }

    function filter_substring_before($text, $needle = '-'){
        if (strpos($text, $needle) !== false){
            return substr($text, 0,strpos($text, $needle));
        }else{
            return "";
        }
    }

    function filter_contains($text, $needle){
        return strpos($text, $needle) !== false;
    }

    function filter_starts_with($text, $needle){
        $text = trim($text);
        $needle = trim($needle);

        $length = strlen($needle);

        return (substr($text, 0, $length) === $needle);
    }


    function filter_truncate($text, $length = 30, $preserve = false, $separator = '...') {
        if (strlen($text) > $length) {
            if ($preserve) {
                if (false !== ($breakpoint = strpos($text, ' ', $length))) {
                    $length = $breakpoint;
                }
            }
            return substr($text, 0, $length) . $separator;
        }

        return $text;
    }

    function filter_slugify($text) {
        // replace non letter or digits by -
        $text = remove_accents($text);
        $text = preg_replace('/[^a-z0-9]/i', '-', $text);

        // trim
        $text = trim($text, '-');

        // lowercase
        $text = strtolower($text);

        return $text;
    }

    function filter_subfield($text, $id) {
        # case: pt-br^Collection / Subcollection|es^Collection / Subcollecion|en^Collection / Subcollection
        if (strpos($text, '|') !== false) {
            $occs = preg_split('/\|/', $text);
            foreach ($occs as $occ){
                $sv = preg_split('/\^/', $occ);
                if ($sv){
                    $subfield = substr($sv[0],0,2);
                    $value = $sv[1];
                    $subfield_value[$subfield] = $value;
                }
            }
            return $subfield_value[$id];
        }else{
            # case: ^pCollection^eCollection^iCollection

            # check for old language code compatibility (pt=p, es=e, en=i)
            if (strlen($id) == 2){
                $id = ( $id == 'en' ? 'i' : substr($id,0,1) );
            }
            $subfields = array();
            $sub_list = preg_split('/\^/', $text);
            foreach($sub_list as $sub){
                $sub_id = substr($sub,0,1);
                $subfields[$sub_id] = substr($sub,1);
            }
        }

        return $subfields[$id];
    }


    function filter_subfield_value($value, $subfield){
        $subfield = substr($subfield,0,2);
        $out = '';
        if ( is_array($value) ){
            foreach($value as $current_value){
                $print_values[] = extract_sub_value($current_value, $subfield);
            }
            $out = implode(', ', $print_values);
        }else{
            $out = extract_sub_value($value, $subfield);
        }
        return $out;
    }

    function extract_sub_value($string, $subfield, $default_subfield = 'en'){
        $subfield_value = array();
        $occs = preg_split('/\|/', $string);

        foreach ($occs as $occ){
            $re_sep = (strpos($occ, '~') !== false ? '/\~/' : '/\^/');
            $lv = preg_split($re_sep, $occ);
            $lang = substr($lv[0],0,2);
            $value = $lv[1];
            $subfield_value[$lang] = trim($value);
        }
        if ( isset($subfield_value[$subfield]) ){
            $out = $subfield_value[$subfield];
        }else{
            $out = $subfield_value[$default_subfield];
        }

        return $out;
    }


    function replace_history_mark($text, $history){
        $result = preg_replace_callback('/#([0-9]+)/',
                                        function($match) use ($history) {
                                            return $history[strval($match[1])-1]['detailed_query'];
                                        }, $text);
        return $result;
    }


    function find_in_array($array, $key, $val) {
        foreach ($array as $item){
            if (isset($item[$key]) && $item[$key] == $val){
                return true;
            }
        }
        return false;
    }

    // convert filters array to search string
    function filters_to_string($filters){
        if(is_array($filters)){
            $expr = array();
            foreach($filters as $key => $value){
                $value  = array_map(function($val) { return '"'.$val.'"'; }, $value);
                $value  = implode(' OR ', $value);
                $expr[] = $key.':('.$value.')';
            }
            $filters = ( !empty($expr) ) ? implode(' AND ', $expr) : '';
        }
        return $filters;
    }

    function date_to_text($lang, $month) {
        $months['pt'] = array('Jan', 'Fev', 'Mar', 'Abr', 'Maio', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez');
        $months['es'] = array('Ene', 'Feb', 'Mar', 'Abr', 'Mayo', 'Jun', 'Jul', 'Ago', 'Sept', 'Oct', 'Nov', 'Dic');
        $months['en'] = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'June', 'July', 'Aug', 'Sept', 'Oct', 'Nov', 'Dec');
        return $months["$lang"][intval($month)-1];
    }

    function read_more($text) {
        if (strlen($text) > 500) {
            $text = substr($text, 0, 500);
            $pos = strrpos($text, ' ');
            $text = substr($text, 0, $pos) . ' [...]';
        }
        return $text;
    }

    function get_page_type($ptype, $slug=false) {
        $type = 'iahx-unknown';

        $pages = array(
            'iahx-document' => 'Document',
            'iahx-search' => 'Search',
            'iahx-search-skfp-true' => 'Search skfp=true',
            'iahx-search-skfp-false' => 'Search skfp=false',
            'iahx-advanced-search' => 'Advanced Search',
            'iahx-decs-locator' => 'DeCS Locator',
            'iahx-unknown' => 'Unknown'
        );

        if ( 'result' == $ptype ) {
            $type = 'iahx-search';
        } elseif ( 'result_skfp_true' == $ptype ) {
            $type = 'iahx-search-skfp-true';
        } elseif ( 'result_skfp_false' == $ptype ) {
            $type = 'iahx-search-skfp-false';
        } elseif ( 'advanced_form' == $ptype ) {
            $type = 'iahx-advanced-search';
        } elseif ( 'detail' == $ptype ) {
            $type = 'iahx-document';
        } elseif ( 'decs_lookup' == $ptype ) {
            $type = 'iahx-decs-locator';
        }

        $page = ( $slug ) ? $type : $pages[$type];
        return $page;
    }

    function hasBalancedQuotesAndParentheses($input) {
        $quoteCount = 0;
        $parenthesisCount = 0;

        for ($i = 0; $i < strlen($input); $i++) {
            $char = $input[$i];

            // Check for double quotes
            if ($char === '"') {
                $quoteCount++;
            }

            // Check for parentheses
            if ($char === '(') {
                $parenthesisCount++;
            } elseif ($char === ')') {
                $parenthesisCount--;
            }

            // If parentheses are unbalanced at any point, return false
            if ($parenthesisCount < 0) {
                return false;
            }
        }

        // Quotes must be even, parentheses must be balanced
        return ($quoteCount % 2 === 0) && ($parenthesisCount === 0);
    }

    function fixDoubleQuotes($input) {
        // Replace curly double quotes with standard double quotes
        $input = str_replace(['“', '”'], '"', $input);

        // Check if the string has an odd number of double quotes and does not end with one
        $quoteCount = substr_count($input, '"');
        if ($quoteCount % 2 != 0 && substr($input, -1) !== '"') {
            return $input . '"';
        }

        return $input;
    }


}
?>
