<?php

namespace App\Service;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CacheService
{
    public function __construct(
        private string $projectDir,
        private CacheInterface $cache,
    ){}

    function get_texts($instance, $lang) {
        // cache key id
        $texts_cache_key = "texts_" . $lang . "_" . $instance;

        // Fetch the texts from cache or generate them if not cached
        $texts = $this->cache->get($texts_cache_key, function (ItemInterface $item) use ($instance, $lang): array {
            $translation_path = $this->projectDir . '/instances/' . $instance . '/translations/' . $lang;
            $texts_ini = parse_ini_file($translation_path . "/texts.ini", true);
            return $texts_ini;
        });

        return $texts;
    }

    function get_texts_decs_locator($lang) {
        // Create the cache key
        $texts_cache_key = "texts_" . $lang . "_decslocator";

        // Fetch the texts from cache or generate them if not cached
        $texts = $this->cache->get($texts_cache_key, function (ItemInterface $item) use ($lang): array {
            $translation_path = $this->projectDir . '/translations/' . $lang;
            $texts_ini = parse_ini_file($translation_path . "/decs-locator.ini", true);
            return $texts_ini;
        });

        return $texts;
    }

    function get_config($instance) {
        // cache key id
        $config_cache_key = "config_" . $instance;

        // Fetch the config from cache or generate them if not cached
        $config_str = $this->cache->get($config_cache_key, function (ItemInterface $item) use ($instance) {
            $config_file = $this->projectDir . '/instances/' . $instance . '/config/config.xml';

            $config_xml = @simplexml_load_file($config_file, 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);
            if ($config_xml == false){
                throw new NotFoundHttpException('The resource does not exist');
            }
            // Convert SimpleXMLElement to string for serialization
            $config_xml_str = $config_xml->asXML();
            return $config_xml_str;
        });

        // Convert string to SimpleXMLElement
        $config_xml = simplexml_load_string($config_str);

        return $config_xml;
    }


}
?>