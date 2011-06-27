<?PHP
    session_start();
    require_once('config.php');
    require_once('./classes/Page.php');
    require_once('./classes/smarty/Smarty.class.php');

    $col = $_REQUEST["col"];
    $site = $_REQUEST["site"];
    $printMode = $_REQUEST["printMode"];
    $count = ( isset($_REQUEST["count"]) ? $_REQUEST["count"] : $config->documents_per_page );
    $sort = $_REQUEST['sort'];
    
    $booleans = array(
        array("E", "and"),
        array("OU", "or")
    );

    if ( !isset($col) )
        $col = $defaultCollection;              // valor default criada no script de configuracao do sistema

    if ( !isset($site) )
        $site= $defaultSite;                    // valor default criada no script de configuracao do sistema

    if ( isset($_REQUEST["search"]) ){          // search = form submit

        if ($_REQUEST["q"] != '') {             // process detailed search box

            $q = preg_replace('/#([0-9]+)/e', "replace_by_history('\$1')", $_REQUEST["q"]);

        }else{
            //process multiple fields query
            for ($i = 0; $i < 3; $i++){
                $query = $_REQUEST["q".$i];
                if (isset($query) and $query){
                    $campo = $_REQUEST["field".$i];
                    if ($q){
                        $boolean = $_REQUEST["boolean".$i];
                        if ($campo){
                            $q .= " " . $boolean . " " . $campo . ':(' . $query . ')';
                        } else {
                            $q .= " " . $boolean . " " . $query;
                        }
                    } else {
                        if ($campo){
                            $q = $campo . ':(' . $query . ')';
                        } else {
                            $q = $query;
                        }
                    }
                }
            }
        }
        header('Location: ./?lang=' . $lang . '&sort=' . $sort . '&count=' . $count . '&q=' . $q);
    }else{

        if ( isset($_REQUEST["clear_history"]) )
            unset($_SESSION['search_history']);
        
        $page= new Page();
        $page->form_advanced();
    }

    function replace_by_history($number){
       $index = intval($number)-1;
       $exp = "#$number";

       if ($_SESSION['search_history'][$index] != ''){
           
            $search_parts = explode("|", $_SESSION['search_history'][$index]);
            $exp = "(" . trim($search_parts[0]);
            if ($search_parts[1] != '')
                $exp .= " AND (" . $search_parts[1] . ")";

            $exp .= ")";
       }
                
       return $exp;
    }
?>
