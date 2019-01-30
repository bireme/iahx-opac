<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {date_extended} function plugin
 *
 * Type:     function<br>
 * Name:     date_extended<br>
 * @return string
 */
function smarty_function_date_extended($params, &$smarty)
{
    $output = "";
    
    if (!isset($params['isodate'])) {
        return;
    }

    $iso = $params['isodate'];
    $lang = (string)$params['language'];
    $before = $params['before'];
    $after = $params['after'];
    
    if (strlen($iso)==4) {
        $output = ', ' . $iso;
        return $output;
    } 

    $month_name['pt'] = array('Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Augosto','Setembro','Outubro','Novembro','Dezembro');
    $month_name['es'] = array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio​​', 'Julio', 'Agosto', 'Septiembre ', 'Octubre ', 'Noviembre', 'Diciembre');
    $month_name['en'] = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'Augosto', 'September', 'October', 'November', 'December');

    $month = intval(substr($iso,4,2)) - 1;    

    if (strlen($iso)==6) {
        if (substr($iso,4,2)!= '00'){
            if ($lang == 'pt' || $lang == 'es'){
                $output = $month_name[$lang][$month] . ' de ' . substr($iso,0,4);
            }else{
                $output = $month_name['en'][$month] . ', ' . substr($iso,0,4);
            }
        }
        $output = $before . $output . $after;
        return $output;
    }

    if (substr($iso,4,4)!= '0000'){
        if ($lang == 'pt' || $lang == 'es'){
            $output = substr($iso,6,2) . ' de ' . $month_name[$lang][$month] . ' de ' . substr($iso,0,4);
        }else{
            $output = $month_name['en'][$month] . ' ' . substr($iso,6,2) . ', ' . substr($iso,0,4);
        }
    }else{
        $output = ', ' . substr($iso,0,4);
    }    
    
    $output = $before . $output . $after;
    return $output;
}
?>
