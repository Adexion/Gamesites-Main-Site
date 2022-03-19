<?php

namespace App\Controller;

use App\Form\UserProfileType;
use App\Service\ApplicationService;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    /**
     * @Route("/dashboard/profile" , name="app_profile")
     * @throws Exception
     */
    public function profile(Request $request, ApplicationService $applicationService, EntityManagerInterface $manager): Response
    {
        $form = $this->createForm(UserProfileType::class, [
            'address' => $this->getUser()->getAddress(),
            'email' => $this->getUser()->getUserIdentifier()
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $applicationService->setPasswordForAllApplications($this->getUser(), $form->getData()['password'], $form->getData()['each']);
            $user->setAddress($form->getData()['address']);

            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', 'Pomyślnie zapisano twoje dane.');
        }

        return $this->render('dashboard/page/user/profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}