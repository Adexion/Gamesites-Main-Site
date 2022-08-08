<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(): Response
    {
        return $this->render('client/page/index.html.twig');
    }

    /**
     * @Route("/rules", name="rules")
     */
    public function rules(): Response
    {
        return $this->render('client/page/rules.html.twig');
    }

    /**
     * @Route("/privacy-policy", name="privacy")
     */
    public function privacy(): Response
    {
        return $this->render('client/page/privacy.html.twig');
    }

    /**
     * @Route("/rodo", name="rodo")
     */
    public function rodo(): Response
    {
        return $this->render('client/page/rodo.html.twig');
    }
}