<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;

class AppExtension extends \Twig\Extension\AbstractExtension
{

    public function getFunctions(): array
    {
        return [
            new \Twig\TwigFunction('custom_template', [$this,  'custom_template']),
            new \Twig\TwigFunction('has_translation', [$this, 'has_translation']),
            new \Twig\TwigFunction('translate', [$this, 'translate']),
            new \Twig\TwigFunction('get_translated_label', [$this, 'get_translated_label']),
            new \Twig\TwigFunction('occ', [$this, 'occ']),
        ];
    }


    public function getFilters(): array
    {
        return [
            new \Twig\TwigFilter('substring_before', [$this, 'filter_substring_before']),
            new \Twig\TwigFilter('substring_after', [$this, 'filter_substring_after']),
            new \Twig\TwigFilter('contains', [$this, 'filter_contains']),
            new \Twig\TwigFilter('starts_with', [$this, 'filter_starts_with']),
            new \Twig\TwigFilter('truncate', [$this, 'filter_truncate']),
            new \Twig\TwigFilter('slugify', [$this, 'filter_slugify']),
            new \Twig\TwigFilter('subfield', [$this, 'filter_subfield']),
            new \Twig\TwigFilter('subfield_value', [$this, 'filter_subfield_value']),
            new \Twig\TwigFilter('subfield_value', [$this, 'filter_subfield_value']),
            new \Twig\TwigFilter('filters_to_string', [$this, 'filters_to_string']),
            new \Twig\TwigFilter('md5', [$this, 'filter_md5']),
            new \Twig\TwigFilter('json2array', [$this, 'filter_json2array']),
        ];
    }

    // Twig Functions
    function has_translation($label, $group=NULL)
    {
        global $texts, $lang;

        // labels on texts.ini must be array key without spaces
        $label_norm = preg_replace('/[&,\'\s]+/', '_', $label);

        if($group == NULL) {
            return (isset($texts[$label_norm]) and $texts[$label_norm] != "");
        } else {
            return (isset($texts[$group][$label_norm]) and $texts[$group][$label_norm] != "");
        }
    }

    function translate($label, $group=NULL)
    {
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


    function get_translated_label($target_lang,  $label, $lang, $translations, $default_lang = 'en')
    {
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

    public function custom_template($filename)
    {
        if( file_exists(CUSTOM_TEMPLATE_PATH . $filename) ) {
            return str_replace(TEMPLATE_PATH, "", CUSTOM_TEMPLATE_PATH) . $filename;
        }

        return $filename;

    }

    public function occ($params)
    {
        $separator = ', ';
        $translate = $output = '';

        extract($params);

        if ( is_array($element) ){
            for ($occ = 0; $occ <  count($element); $occ++) {
                if ($occ > 0){
                    $output .= $separator . " ";
                }
                if ( $translate == true ){
                   $output .= $this->translate($element[$occ], $group);
                }else{
                    $output .= $element[$occ];
                }
            }
        }else{
            if ( $translate == true ){
                $output = $this->translate($element, $group);
            }else{
                $output = $element[$occ];
            }
        }

        return $output;

    }

    function remove_accents($string)
    {
        $no_accents = preg_replace('/[^p{L}p{N}s]/u', '', $string);
        return $no_accents;
    }

    // Twig Filters
    public function filter_substring_after($text, $needle = '-'){
        if (strpos($text, $needle) !== false){
            return substr($text, strpos($text, $needle)+strlen($needle));
        }else{
            return "";
        }
    }

    public function filter_substring_before($text, $needle = '-'){
        if (strpos($text, $needle) !== false){
            return substr($text, 0,strpos($text, $needle));
        }else{
            return "";
        }
    }

    public function filter_contains($text, $needle)
    {
        return strpos($text, $needle) !== false;
    }

    public function filter_starts_with($text, $needle)
    {
        $text = trim($text);
        $needle = trim($needle);

        $length = strlen($needle);

        return (substr($text, 0, $length) === $needle);
    }


    public function filter_truncate($text, $length = 30, $preserve = false, $separator = '...')
    {
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

    public function filter_slugify($text)
    {
        // replace non letter or digits by -
        $text = $this->remove_accents($text);
        $text = preg_replace('/[^a-z0-9]/i', '-', $text);

        // trim
        $text = trim($text, '-');

        // lowercase
        $text = strtolower($text);

        return $text;
    }

    public function filter_subfield($text, $id)
    {
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


    public function filter_subfield_value($value, $subfield)
    {
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

    public function extract_sub_value($string, $subfield, $default_subfield = 'en')
    {
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

    public function filters_to_string($filters)
    {
        // convert filters array to search string
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

    public function filter_md5($text)
    {
        return md5($text);
    }

    public function filter_json2array($string)
    {
        $string_json = str_replace("'", "\"", $string);
        $json_array = json_decode($string_json, true);

        return json_decode($string_json, true);
    }

}

?>