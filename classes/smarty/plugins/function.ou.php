<?php
/*
*     Smarty plugin
* -------------------------------------------------------------
* File:     	function.ou.php
* Type:     	function
* Name:     	ou
* Description: This TAG renders an Organizational Unit.
*
* -------------------------------------------------------------
* @license GNU Public License (GPL)
*
* -------------------------------------------------------------
* Parameter:
* - element     	= the element containing the OU data.
* - class
* -------------------------------------------------------------
* Example usage:
*
* <div>{ou element=$doc->inst_data_struc class=unit}</div>
*/

function smarty_function_ou($params, &$smarty)
{
  $output = "";
  $element = $params['element'];
  $text_transform = $params['text_transform'];

  if (!isset($element) || $element == '' || count($element) == 0 )
  {
    return;
  }

  $element = json_decode($params['element']);

  if ( isset($params['class']) )
  {
    $output .= "<div class=\"". $params['class'] ."\">\n";
  }
  
  if ( isset($params['label']) )
  {
    $output .= $params['label'] . ": ";
  }

  if ( isset($params['span']) )
  {
    $output .= "<span>";
  }

  if (is_array($element))
  {

    foreach($element as $i => $ou)
    {

      $preOut  = '<ul>';
      $preOut .= '<li>' . $ou->name . '</li>';
      $preOut .= '<li>' . $ou->description . '</li>';
      $preOut .= '<li>' . $ou->phone_number . '</li>';
      $preOut .= '<li>' . $ou->fax_number . '</li>';
      $preOut .= '<li>' . $ou->email_address . '</li>';
      $preOut .= '<li>' . $ou->web_site . '</li>';
      foreach($ou->contacts as $j => $contact)
      {
        $preOut .= '<ul>';
        $preOut .= '<li>' . $contact->name . '</li>';
        $preOut .= '<li>' . $contact->function . '</li>';
        $preOut .= '<li>' . $contact->phone_number . '</li>';
        $preOut .= '<li>' . $contact->cel_number . '</li>';
        $preOut .= '<li>' . $contact->email_address . '</li>';
        $preOut .= '</ul>';
      }
      $preOut .= '</ul>';
      
      $output .= $preOut;
    }
  }

  if ( isset($params['span']) )
  {
    $output .= "</span>";
  }

  if ( isset($params['class']) )
  {
    $output .= "</div>\n";
  }

  return $output;
}

?>