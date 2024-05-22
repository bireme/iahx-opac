<?php

namespace App\Controller;

use App\Service\AuxFunctions;
use App\Service\CacheService;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BrowseIndex extends AbstractController
{

    public function __construct(
        private AuxFunctions $auxFunctions,
        private CacheService $cache,
    ){}

    #[Route('{instance}/browse-index/{index}/')]
    public function index(Request $request, string $instance, string $index): Response
    {

        $app_dir = $this->getParameter('kernel.project_dir');
        require($app_dir . '/config/load-instance-definitions.php');

        $init = $request->get('init', '"');
        $direction = $request->get('dir', 'next');

        $index_name = $config->site;

        $service_url = $_ENV['BROWSE_INDEX_URL'] . "/PreviousTermServlet?index=" . $index_name . "&fields=" . $index . "&init=" . $init . "&maxTerms=200&direction=" . $direction;

        try {
            $result = @file_get_contents($service_url);

            $response = new Response();
            $response->setContent($result);
            $response->headers->set('Content-Type', 'application/json');
        } catch (Exception $e) {
            // Handle the error
            $errorMessage = $e->getMessage();
            // Log the error or handle it in some other way
            error_log("Error fetching content from $service_url: $errorMessage");

            // Return an appropriate response, e.g., a 500 Internal Server Error
            $response = new Response('Error fetching content', 500);
            $response->headers->set('Content-Type', 'application/json');
        }

        return $response;
    }
}