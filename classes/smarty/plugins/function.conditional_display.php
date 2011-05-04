<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {conditional_display} function plugin
 *
 * Type:     function<br>
 * Name:     date_extended<br>
 * @return string
 */
function smarty_function_conditional_display($params, &$smarty)
{
    $output = "";
    $element = $params['element'];
    
    if (!isset($element) || $element == '') {
        return;
    }
    
   
    $before = $params['before'];
    $after = $params['after'];
    
    $output = $before . $element . $after;

    return $output;

}
?>
