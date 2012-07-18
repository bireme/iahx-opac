<?php

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
    return urlencode($sortValue);
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
    return urlencode($sortValue);
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

?>