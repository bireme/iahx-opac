<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PubMedLinkOut extends AbstractController
{

    #[Route('{instance}/pubmed_linkout/{lang}/{id}')]
    public function index(Request $request, string $instance, string $lang, string $id): Response
    {
        $pmid = str_replace('mdl-','',$id);
        $linkout_url = "http://eutils.ncbi.nlm.nih.gov/entrez/eutils/elink.fcgi?dbfrom=pubmed&id=" . $pmid ."&cmd=llinks";

        $linkout_xml = @simplexml_load_file($linkout_url);

        if ($linkout_xml){
            $template_vars = array();
            $template_vars['lang'] = $lang;
            $template_vars['set'] = $linkout_xml->LinkSet->IdUrlList->IdUrlSet->ObjUrl;

            $response = $this->render('regional/pubmed_linkout.html', $template_vars);
        }else{
            $response = new Response('', 200);
        }

        return $response;
    }
}