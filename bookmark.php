<?php
session_start();
require("classes/Bookmark.php");

$action = $_POST['action'];
$id = $_POST['id'];

if( !isset($_SESSION["bookmark"])){
	$bm = new ArrayList();
	$_SESSION["bookmark"] = serialize($bm);
}

$bookmark = unserialize($_SESSION["bookmark"]);
//$bookmark = new Bookmark();

switch($action){
	case 'a': $bookmark->addElement($id);
			  break;
	case 'r': $bookmark->removeElement($id);
			  break;
	case 'c': $bookmark->removeAll();
			  break;
	case 'l': $javascriptArray = $bookmark->list[0];
			  $len = sizeOf($bookmark->list);
			  for($i = 1; $i < $len; $i=$i+1){
				$javascriptArray .= ";".$bookmark->list[$i]; 
			  }
			  print $javascriptArray;
			  break;
	case 's': print(sizeOf($bookmark->list));
			  break;
}
//$bookmark2 = $bookmark;
$_SESSION["bookmark"] = serialize($bookmark);

//print_r($javascriptArray);

?>