<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BookmarkController extends AbstractController
{

    #[Route('{instance}/bookmark/{action}/{id}')]
    public function index(Request $request, string $instance, string $action, string $id = null): Response
    {
        $session = $request->getSession();
        $bookmark = $session->get('bookmark', []);

        if($action == 'a') {
            $item = array('id' => $id, 'timestamp' => time());
            $bookmark[$id] = $item;
        }

        if($action == 'c') {
            $bookmark = array();
        }

        if($action == 'd') {
            if(isset($bookmark[$id])) {
                unset($bookmark[$id]);
            }
        }

        $count = count($bookmark);
        $session->set('bookmark', $bookmark);

        if($action == 'list') {
            if ($count > 0){
                $q = '+id:("' . implode('" OR "', array_keys($bookmark)) . '")';
            }else{
                $q = '';
            }
            return new Response($q);
        }

        return new Response($count);

    }
}

?>