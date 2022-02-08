<?php

namespace App\Controller;

use App\Repository\PriceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="index")
     */
    public function index(PriceRepository $priceRepository): Response
    {
        return $this->render('page/index.html.twig', [
//            'prices' => $priceRepository->findBy([], ['id' => 'ASC'])
        ]);
    }
}