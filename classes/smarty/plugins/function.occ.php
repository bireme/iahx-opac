<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {occ} function plugin
 *
 * Type:     function<br>
 * Name:     occ<br>
 * Date:     Feb 17, 2003<br>
 * Purpose:  return a <br>
 * Input:<br>
 *         - label
 *         - element
 *         - separator
 *         - class
 *
 * Examples:
 * <pre>
 * {occ label=AUTOR element=$doc->au separator=; class=author}
 * </pre>
 * @author   Vinicius Andrade <viniciusdeandrade at gmail dot com>
 * @version  0.1
 * @param array
 * @param Smarty
 * @return string
 */
function smarty_function_occ($params, &$smarty)
{
	$output = "";
    $element = $params['element'];

    if (!isset($element) || $element == '' || count($element) == 0 ) {
        return;
    }

	$element = $params['element'];

	if ( isset($params['class']) ){
		$output .= "<div class=\"". $params['class'] ."\">\n";
	}
	if ( isset($params['label']) ){
		$output .= $params['label'] . ": ";
	}

    if ( isset($params['span']) ){
        $output .= "<span>";
    }

	if ( is_array($element) ){
		for ($occ = 0; $occ <  count($element); $occ++) {
			if ($occ > 0){
				$output .= $params['separator'] . " ";
			}
			if ( isset($params['translation']) ){
				$output .= smarty_function_occ_translate($element[$occ], $params['suffix'], $params['translation']);
			}else{
 				$output .= $element[$occ];
			}
		}
	}else{
		$output .= $element;
	}

    if ( isset($params['span']) ){
        $output .= "</span>";
    }

	if ( isset($params['class']) ){
		$output .= "</div>\n";
	}

    return $output;
}

function smarty_function_occ_translate($text, $suffix, $translation)
{
	if ( isset($suffix) ){
		$find = $suffix;
	}
	$find .= $text;

	$output = $translation[$find];

	return $output;

}

?>