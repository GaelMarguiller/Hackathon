<?php

namespace HackathonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\BrowserKit\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        return $this->render('HackathonBundle:Default:index.html.twig');
    }

    /**
     * @Route("/get", name="getAnswer")
     */

    public function answer(Request $request){

        require '../Resources/cleaverBot/chatterbotapi.php';

        $factory = new ChatterBotFactory();

        $bot1 = $factory->create(ChatterBotType::CLEVERBOT);
        $bot1session = $bot1->createSession('fr');

        $s = $request->get('question');

        $s = $bot1session->think($s);
        return $s;
    }
}
