<?php

require_once "lib/functions.php";
require_once 'lib/silex/vendor/autoload.php';

$lang = "pt";
$tag = "1.3.2";

// define constants
define("VERSION", $tag);
define("USE_SERVER_PATH", false);

if (USE_SERVER_PATH == true){
    $PATH = dirname($_SERVER["PATH_TRANSLATED"]);
} else {
    $PATH = dirname(__FILE__).'/';
}

$PATH_DATA = substr($PATH,strlen($_SERVER["DOCUMENT_ROOT"]));
$PATH_DATA = str_replace('\\','/',$PATH_DATA);

$config = simplexml_load_file('config/dia-config.xml');

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

//security check
if (preg_match('/^[a-zA-Z\-]{2,5}$/',$lang) == 0)
    die("invalid parameter lang" . $lang);

$texts = parse_ini_file("./languages/" . $lang . "/texts.ini", true);                   // interface texts
$logDir = ( isset( $config->log_dir ) ? $config->log_dir : "logs/");

//environment variables
$config["PATH_DATA"] = $PATH_DATA;
$config["DOCUMENT_ROOT"] = $_SERVER["DOCUMENT_ROOT"];
$config["SERVERNAME"] = $_SERVER["HTTP_HOST"];

define("SERVERNAME", $config["SERVERNAME"]);
define("PATH_DATA" , $config["PATH_DATA"]);
define("DOCUMENT_ROOT", $config["DOCUMENT_ROOT"]);
define("APP_PATH", $config["DOCUMENT_ROOT"] . $config["PATH_DATA"]);

define("TEMPLATE_PATH", DOCUMENT_ROOT . "/template/");
define("VIEWS_PATH", DOCUMENT_ROOT . "/views/");

define('LOG_DIR', $logDir);
define('LOG_FILE',"log" . date('Ymd') . "_search.txt");

// verifica se exitem acentos codificados em ISO nos parâmetros de entrada (q, filter e filterLabel)
// if (!isUTF8($_REQUEST["q"])){
//     $_REQUEST["q"] = utf8_encode($_REQUEST["q"]);
// }
// if (!isUTF8($_REQUEST["filter"])){
//     $_REQUEST["filter"] = utf8_encode($_REQUEST["filter"]);
// }
// if (!isUTF8($_REQUEST["filterLabel"])){
//     $_REQUEST["filterLabel"] = utf8_encode($_REQUEST["filterLabel"]);
// }

// seta variavel colectionData com a configuracao da colecao atual
// if ($_REQUEST['col'] != ''){
//     for ($c = 0; $c < count($config->search_collection_list->collection); $c++){
//         $colName = $config->search_collection_list->collection[$c]->name;
//         $colSite = $config->search_collection_list->collection[$c]->site;
//         if (!isset($colSite) || $colSite == ''){
//             $colSite = $DEFAULT_PARAMS['defaultSite'];
//         }
//         if ($_REQUEST['col'] == $colName ){
//             if ( isset($_REQUEST['site']) ) {
//                 if ($_REQUEST['site'] == $colSite ){
//                     $colectionData = $config->search_collection_list->collection[$c];
//                     break;
//                 }
//             }else if ($colSite == $DEFAULT_PARAMS['defaultSite']){
//                 $colectionData = $config->search_collection_list->collection[$c];
//                 break;
//             }
//         }
//     }
// }

$app = new Silex\Application();
// iniciando o twig, buscando templates em /template
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => TEMPLATE_PATH,
));
?>
