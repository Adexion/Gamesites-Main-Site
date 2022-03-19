<?php

namespace App\Controller;

use App\Repository\ApplicationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    /**
     * @Route("/dashboard", name="app_dashboard")
     */
    public function dashboard(): Response
    {
        return $this->render('dashboard/page/dashboard.html.twig');
    }

    /**
     * @Route("/dashboard/application", name="app_application_list")
     */
    public function application(ApplicationRepository $repository, RequestStack $requestStack): Response
    {
        $workspace = $requestStack->getSession()->get('workspace');

        return $this->render('dashboard/page/application.html.twig', [
            'userApplicationList' => $repository->getCurrentApplications($workspace, $this->getUser()),
        ]);
    }
}