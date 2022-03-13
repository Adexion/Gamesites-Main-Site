<?php

namespace App\Controller;

use App\Repository\ServerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    /**
     * @Route("/dashboard", name="app_dashboard")
     */
    public function dashboard(Request $request): Response
    {
        return $this->render('dashboard/page/dashboard.html.twig');
    }

    /**
     * @Route("/dashboard/application", name="app_server")
     */
    public function server(ServerRepository $repository): Response
    {
        return $this->render('dashboard/page/server.html.twig', [
            'serverList' => $repository->getUserApplication($this->getUser()),
        ]);
    }
}