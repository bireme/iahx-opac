<?php

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CacheService
{
    public function __construct(
        private string $projectDir,
        private CacheInterface $cache,
    ){}

    function get_texts($instance, $lang) {
        // Cache key id
        $texts_cache_key = "texts_" . $lang . "_" . $instance;

        // Fetch the texts from cache or generate them if not cached
        $texts = $this->cache->get($texts_cache_key, function (ItemInterface $item) use ($instance, $lang): array {
            $translation_path = $this->projectDir . '/instances/' . $instance . '/translations/';
            $texts_ini = @parse_ini_file($translation_path . $lang . '/texts.ini', true);
            // if fail to load texts from instance, load from english texts.ini
            if (!$texts_ini) {
                $texts_ini = parse_ini_file($translation_path . 'en/texts.ini', true);
            }
            return $texts_ini;
        });

        return $texts;
    }

    function get_texts_decs_locator($lang) {
        // Cache key id
        $texts_cache_key = "texts_" . $lang . "_decslocator";

        // Fetch the texts from cache or generate them if not cached
        $texts = $this->cache->get($texts_cache_key, function (ItemInterface $item) use ($lang): array {
            $translation_path = $this->projectDir . '/translations/';
            $texts_ini = @parse_ini_file($translation_path . $lang . '/decs-locator.ini', true);
            // if fail to load texts from lang, load from english
            if (!$texts_ini) {
                $texts_ini = @parse_ini_file($translation_path . 'en/decs-locator.ini', true);
            }
            return $texts_ini;
        });

        return $texts;
    }

    function get_config($instance) {
        // Cache key id
        $config_cache_key = "config_" . $instance;

        // Fetch the config from cache or generate them if not cached
        $config_str = $this->cache->get($config_cache_key, function (ItemInterface $item) use ($instance) {
            $config_file = $this->projectDir . '/instances/' . $instance . '/config/config.xml';
            $config_xml_str = '';

            if (file_exists($config_file)) {
                $config_xml = simplexml_load_file($config_file, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);
                if ($config_xml === false){
                    throw new HttpException(500, 'Invalid config file');
                }
                // Convert SimpleXMLElement to string for serialization
                $config_xml_str = $config_xml->asXML();
            }else{
                throw new NotFoundHttpException('The instance does not exist');
            }

            return $config_xml_str;
        });

        // Convert string to SimpleXMLElement
        $config_xml = simplexml_load_string($config_str);

        return $config_xml;
    }


    function get_decs_first_level($lang, $api_key) {
        // Cache key id
        $cache_key = "decs_" . $lang . "_firstlevel";

        // Fetch the first level of decs or generate them if not cached
        $first_level_str = $this->cache->get($cache_key, function (ItemInterface $item) use ($lang, $api_key) {
            $api_url = "https://api.bvsalud.org/decs/v2/get-tree?lang=" . $lang . "&tree_id=";
            $opts = array(
                'http'=>array(
                  'method' => "GET",
                  'header' =>' apikey: ' . $api_key
                )
            );
            $context = stream_context_create($opts);
            $api_response = @file_get_contents($api_url, false, $context);

            return $api_response;
        });

        // Convert string to SimpleXMLElement
        $first_level_xml = simplexml_load_string($first_level_str);

        return $first_level_xml;
    }


    function get_first_page_result($instance, $lang, $search, $tab) {
        // Cache key id
        $cache_key = "first_page_result_" . $instance . '_' . $lang . '_' . $tab;

        // Get from cache or execute the first page result query
        $first_page_result = $this->cache->get($cache_key, function (ItemInterface $item) use ($search) {
            $item->expiresAfter(86400);  // 3600*24 = 1 day
            $search_response = $search->search();

            return $search_response;
        });

        // If the query returns no results, delete the cache entry
        if (!str_contains($first_page_result, 'numFound')) {
            $this->cache->deleteItem($cache_key);
        }

        return $first_page_result;
    }


}
?>