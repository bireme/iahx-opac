<?php

// OPAC's current version
define("VERSION", "2.0");



// ENVIRONMENT CONSTANTS
$PATH = str_replace("index.php", "", $_SERVER['PHP_SELF']);
$PATH_DATA = __DIR__ . "/";

$config["PATH_DATA"] = $PATH_DATA;
$config["DOCUMENT_ROOT"] = $_SERVER["DOCUMENT_ROOT"];
$config["SERVERNAME"] = $_SERVER["HTTP_HOST"];

define("SERVERNAME", $config["SERVERNAME"]);
define("PATH_DATA" , $config["PATH_DATA"]);
define("DOCUMENT_ROOT", $config["DOCUMENT_ROOT"]);
define("APP_PATH", $PATH_DATA . "/");

define("TEMPLATE_PATH", APP_PATH . "templates/");
define("VIEWS_PATH", APP_PATH . "views/");
define("CACHE_PATH", APP_PATH . "cache/");

// custom applications/interface
define("CUSTOM_TEMPLATE_PATH", TEMPLATE_PATH . "custom/");

// urls
define("SEARCH_URL",  "http://" . $_SERVER['HTTP_HOST'] . $PATH);
define("STATIC_URL",  SEARCH_URL . "static/");



// CONFIGURATION
$config = simplexml_load_file($PATH_DATA . 'config/config.xml');
$lang = $config->default_lang;

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

// log's configuration
$logDir = ( isset( $config->log_dir ) ? $config->log_dir : "logs/");
define('LOG_FILE',"log" . date('Ymd') . "_search.txt");
define('LOG_DIR', $logDir);



// DIRECTORIES
// create all directories that needs
if(!is_dir(CACHE_PATH)) {
    if(!mkdir(CACHE_PATH)) {
        die("ERROR: can't create cache's directory.");
    }
}



// FRAMEWORK
// Initiating Silex framework
require_once 'lib/silex/vendor/autoload.php';
$app = new Silex\Application();

// iniciando o twig, buscando templates em /template
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => TEMPLATE_PATH,
));

if($config->environment != "production")
    $app['debug'] = "true";

$app['twig.options'] = array('strict_variables' => false, 'cache' => CACHE_PATH);



require_once "lib/functions.php";
?>
