<?php

$lang = $_GET['lang'];
$term = utf8_decode($_GET['term']);

if ($lang == 'pt'){
    $langOneLetter = 'p';
}else if ($lang == 'es'){
    $langOneLetter = 'e';
}else{
    $langOneLetter = 'i';
}

$decs_url = "http://decs.bvs.br/cgi-bin/wxis1660.exe/decsserver/?IsisScript=../cgi-bin/decsserver/decsserver.xis&previous_page=homepage&task=exact_term&interface_language=" .  $langOneLetter ."&search_language=" . $searchOneLetter . "&search_exp=" . $term;

header("Location: " . $decs_url);

?>
