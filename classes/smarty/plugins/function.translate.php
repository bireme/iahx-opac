<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {translate} function plugin
 *
 * Type:     function<br>
 * Name:     translate<br>
 * Date:     Feb 17, 2003<br>
 * Purpose:  return a <br>
 * Input:<br>
 *         - text
 *         - suffix
 *         - translation
 *
 * Examples:
 * <pre>
 * {translate text=MEDLINE suffix=DB_ translation=$texts}
 * </pre>
 * @author   Vinicius Andrade <viniciusdeandrade at gmail dot com>
 * @version  0.1
 * @param array
 * @param Smarty
 * @return string
 */
function smarty_function_translate($params, &$smarty)
{
	$output = "";
	$find = "";
    if (!isset($params['text'])) {
        return;
    }

	if ( isset($params['suffix']) ){
		$find .= $params['suffix'];
	}
	$find .= $params['text'];

	$output = $params['translation'][$find];
	
	return $output;

}
?>