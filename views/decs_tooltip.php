<?php

use Symfony\Component\HttpFoundation\Request;

$app->match('decs/{lang}/{term}', function (Request $request, $lang, $term) use ($app, $DEFAULT_PARAMS, $config) {

    $texts = parse_ini_file(TRANSLATE_PATH . $lang . "/texts.ini", true);
    $lang_one_letter = ($lang == "en") ? "i" : substr($lang, 0, 1);

    $bool = array(
        "101", // Termo autorizado
        "102", // Sinônimo
        "104"  // Termo histórico
    );

    $term = stripcslashes($term);
    $term = strtoupper($term);
    $term = remove_accents($term);

    $concept = 0;
    $concept_id = 0;
    for( $i = 0; !$concept && ($i < sizeof($bool)); $i = $i + 1 ){
        $query = "https://decs.bvsalud.org/cgi-bin/mx/cgi=@vmx/decs/?bool=".$bool[$i]."%20$term&lang=$lang";
        $decs = @simplexml_load_file($query);
        if ($decs){
            $concept = (String) @$decs->decsws_response->record_list->record->definition->occ['n'];
            $concept_id = (String) @$decs->decsws_response->record_list->record['mfn'];
        }
    }

    $decs_url = "https://decs.bvsalud.org/";
    $decs_url_lang = ($lang != "pt" ? $lang : '');

    $href = $decs_url . $decs_url_lang . "/ths/resource/?id=" . $concept_id . "&filter=ths_exact_term&q=" . $term;

    $output_array = array();
    $output_array['texts'] = $texts;
    $output_array['concept'] = $concept;
    $output_array['decs'] = $decs;
    $output_array['href'] = $href;

    return $app['twig']->render('decs.html', $output_array);

});

?>
