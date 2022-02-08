<?php

namespace App\Controller;

use App\Entity\Price;
use App\Form\PaymentType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ItemController extends AbstractController
{
    /**
     * @Route("/form/{id}", name="form")
     */
    public function form(Request $request, Price $price): Response
    {
        $form = $this->createForm(PaymentType::class, ['price' => $price]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

        }

        return $this->render('page/form.html.twig', [
            'form' => $form->createView()
        ]);
    }
}