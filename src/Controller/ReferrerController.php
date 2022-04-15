<?php

namespace App\Controller;

use App\Entity\UserReferrer;
use App\Service\RandomReferrerGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReferrerController extends AbstractController
{
    /**
     * @Route("/dashboard/profile/referrer/create", name="app_referrer_create")
     */
    public function assign(RandomReferrerGenerator $generator, EntityManagerInterface $entityManager): Response
    {
        $referrer = (new UserReferrer())
            ->setCode($generator->generate())
            ->setClient($this->getUser());

        $entityManager->persist($referrer);
        $entityManager->flush();

        return $this->redirectToRoute('app_profile');
    }

    /**
     * @Route("/dashboard/profile/referrer", name="app_referrer")
     */
    public function referrer(): Response
    {
        return $this->render('dashboard/page/referrer/info.html.twig');
    }
}