<?php
namespace App\Service;

use App\Service\CacheService;

class InstanceConfigService
{
    private string $projectDir;

    public function __construct(
        private CacheService $cache,
    ){}

    public function loadInstanceConfiguration(string $instanceName)
    {

        $config = $this->cache->get_config($instanceName);

        $defaults['lang'] = $config->default_lang;
        $defaults['defaultCollectionData'] = $config->search_collection_list->collection[0];

        // verifica se existe apenas uma colecao definida no config.xml
        if ( !is_array($defaults['defaultCollectionData']) ){
            $defaults['defaultCollectionData'] = $config->search_collection_list->collection;
        }
        $defaults['defaultCollection'] = $defaults['defaultCollectionData']->name;
        $defaults['defaultSite'] = $defaults['defaultCollectionData']->site;

        if ($defaults['defaultSite'] == ""){
            $defaults['defaultSite'] = $config->site;
        }
        $defaults['defaultDisplayFormat'] = (string)$defaults['defaultCollectionData']->format_list[0]->format[0]->name;

        // Use https as default protocol unless defined in config or in local mode (localhost)
        $protocol = ( (isset($config->use_https) && $config->use_https == 'false') || $_SERVER['SERVER_NAME'] == 'localhost' ? 'http' : 'https');

        // Define constants for the instance
        define('SEARCH_URL',  $protocol . "://" . $_SERVER['HTTP_HOST'] . '/' . $instanceName . '/');
        define('STATIC_URL',  SEARCH_URL . "static/" . $config->template_name);
        define('TEMPLATE_NAME', $config->template_name);


        return [$config, $defaults];
    }
}
?>