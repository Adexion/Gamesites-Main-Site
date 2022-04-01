<?php

namespace App\Controller;

use App\Entity\Company;
use App\Entity\Agreements;
use Doctrine\DBAL\Exception;
use App\Form\AgreementsType;
use App\Form\UserAddressType;
use App\Form\UserCompanyType;
use App\Form\UserPasswordType;
use App\Service\ApplicationService;
use Doctrine\ORM\EntityManagerInterface;
use App\Form\ApplicationResetPasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    /**
     * @Route("/dashboard/profile" , name="app_profile")
     */
    public function profile(Request $request, EntityManagerInterface $manager): Response
    {
        $agreements = $this->getUser()->getAgreements() ?? new Agreements();

        $form = $this->createForm(AgreementsType::class, $agreements);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($agreements);
            $manager->flush();

            $user = $this->getUser();
            $user->setAgreements($agreements);

            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', 'Pomyślnie zapisano zmiany.');

        }

        return $this->render('dashboard/page/user/profile.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/dashboard/profile/address" , name="app_profile_address")
     * @throws Exception
     */
    public function address(Request $request, EntityManagerInterface $manager): Response
    {
        $address = $this->getUser()->getAddress();

        $form = $this->createForm(UserAddressType::class, $address);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($address);
            $manager->flush();

            $this->addFlash('success', 'Pomyślnie zapisano twoje dane.');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('dashboard/page/user/address.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/dashboard/profile/company" , name="app_profile_company")
     * @throws Exception
     */
    public function company(Request $request, EntityManagerInterface $manager): Response
    {
        $company = $this->getUser()->getCompany() ?? new Company();

        $form = $this->createForm(UserCompanyType::class, $company);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($company);
            $manager->flush();

            $user = $this->getUser();
            $user->setCompany($company);

            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', 'Pomyślnie zapisano twoje dane.');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('dashboard/page/user/company.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/dashboard/profile/password" , name="app_profile_password")
     * @throws Exception
     */
    public function password(Request $request, ApplicationService $applicationService, EntityManagerInterface $manager)
    {
        $form = $this->createForm(UserPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $applicationService->setPasswordForAllApplications(
                $this->getUser(),
                $form->getData()['password'],
                $form->getData()['each']
            );

            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', 'Pomyślnie zapisano twoje dane.');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('dashboard/page/user/password.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/dashboard/profile/first" , name="app_profile_first")
     * @throws Exception
     */
    public function firstLogin(Request $request, ApplicationService $applicationService, EntityManagerInterface $manager)
    {
        $form = $this->createForm(ApplicationResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $applicationService->setPasswordForAllApplications(
                $this->getUser(),
                $form->getData()['password'],
                true
            );
            $user->setForceChangePassword(false);

            $manager->persist($user);
            $manager->flush();

            return $this->redirectToRoute('app_profile');
        }

        return $this->render('dashboard/page/user/password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}