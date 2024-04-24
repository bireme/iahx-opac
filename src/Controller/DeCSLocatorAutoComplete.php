<?php

namespace App\Controller;

use App\Service\AuxFunctions;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DeCSLocatorAutoComplete extends AbstractController
{

    public function __construct(
        private AuxFunctions $auxFunctions,
    ){}


    #[Route('{instance}/decs-locator-autocomplete/{lang}/')]
    public function index(Request $request, string $lang): Response
    {

        $params = [];
        foreach($request->query as $key => $value) {
          $params[$key] = $value;
        }

        $query = $request->get('query');
        $count = $request->get('count');
        $query = str_replace(" ","+",$query);
        $query = $this->auxFunctions->remove_accents($query);
        $query = $query . "+OR+" . $query ."*";

        $jsoncallback = $request->get('callback');

        $service_url = "http://srv.bvsalud.org/decsQuickTerm/search?query=" . $query . "&count=" . $count . "&lang=" . $lang;

        $service_response = file_get_contents($service_url);
        $xml = simplexml_load_string($service_response);


        $descriptors = array();
        $term_list = array();

        foreach ($xml->Result->item as $item ) {
            $attr = $item->attributes();
            $term_name = (string)$attr['term'];
            $term_id = (string)$attr['id'];

            // remove duplications of term name (when term has same name in other languages)
            if ( !in_array($term_name, $term_list)) {
                $descriptors[] = array('name' => $term_name, 'id' => $term_id);
                $term_list[] = $term_name;
            }
        }

        $result = array(
                    'query' => $_REQUEST['query'],
                    'descriptors' => $descriptors,
                    );
        $result_json = json_encode($result);

        if (isset($jsoncallback) &&  $jsoncallback != ''){
            $result_json = $jsoncallback . "(" . $result_json . ")";
        }

        $response = new Response();
        $response->setContent($result_json);
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}