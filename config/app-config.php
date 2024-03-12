<?php

// ENVIRONMENT CONSTANTS
$PATH = str_replace("/index.php/", "/", $_SERVER['PHP_SELF']);
$PATH_DATA = "/app/";

// CONFIGURATION
$config = simplexml_load_file(__DIR__ . '/config.xml', 'SimpleXMLElement', LIBXML_NOCDATA | LIBXML_NOBLANKS);
$lang = $config->default_lang;

$config["PATH_DATA"] = $PATH_DATA;
$config["DOCUMENT_ROOT"] = $_SERVER["DOCUMENT_ROOT"];
$config["SERVERNAME"] = $_SERVER["HTTP_HOST"];

define("APP_PATH", $PATH_DATA);

define("TEMPLATE_PATH", $config->template_name);
define("VIEWS_PATH", APP_PATH . "views/");
define("TRANSLATE_PATH", APP_PATH . "translations/");
define("CACHE_PATH", APP_PATH . "cache/");

// custom applications/interface
define("CUSTOM_TEMPLATE_PATH", TEMPLATE_PATH . "/custom/");

$DEFAULT_PARAMS = array();
$DEFAULT_PARAMS['lang'] = $lang;
$DEFAULT_PARAMS['defaultCollectionData'] = $config->search_collection_list->collection[0];

// verifica se existe apenas uma colecao definida no config.xml
if ( !is_array($DEFAULT_PARAMS['defaultCollectionData']) ){
    $DEFAULT_PARAMS['defaultCollectionData'] = $config->search_collection_list->collection;
}
$DEFAULT_PARAMS['defaultCollection'] = $DEFAULT_PARAMS['defaultCollectionData']->name;
$DEFAULT_PARAMS['defaultSite'] = $DEFAULT_PARAMS['defaultCollectionData']->site;

if ($DEFAULT_PARAMS['defaultSite'] == ""){
    $DEFAULT_PARAMS['defaultSite'] = $config->site;
}

$DEFAULT_PARAMS['defaultDisplayFormat'] = (string) $DEFAULT_PARAMS['defaultCollectionData']->format_list[0]->format[0]->name;

// urls
$protocol = ( (isset($config->use_https) && $config->use_https == 'true') ? 'https' : 'http');

define("SEARCH_URL",  $protocol . "://" . $_SERVER['HTTP_HOST'] . $PATH);
define("STATIC_URL",  SEARCH_URL . "static/" . $config->template_name);

// log's configuration
$logDir = ( isset( $config->log_dir ) ? $config->log_dir : "logs/");
define('LOG_FILE',"log" . date('Ymd') . "_search.txt");
define('LOG_DIR', $logDir);

?>