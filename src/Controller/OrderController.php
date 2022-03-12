<?php

namespace App\Controller;

use App\Entity\Server;
use App\Form\OrderType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    /**
     * @Route("/dashboard/order", name="app_order")
     */
    public function order(Request $request): Response
    {
        $server = new Server();
        $form = $this->createForm(OrderType::class, $server);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

        }

        return $this->render('dashboard/page/dashboard.html.twig', [
            'form' => $form->createView()
        ]);
    }
}