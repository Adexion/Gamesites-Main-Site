<?php

namespace App\Controller;

use App\Entity\Address;
use App\Entity\Agreements;
use App\Entity\Company;
use App\Entity\User;
use App\Form\AgreementsType;
use App\Form\UserAddressType;
use App\Form\UserCompanyType;
use App\Form\UserPasswordType;
use App\Form\SetFirstPasswordType;
use App\Repository\UserRepository;
use App\Service\ApplicationService;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/dashboard/profile/address" , name="app_profile_address")
     * @throws Exception
     */
    public function address(Request $request, EntityManagerInterface $manager): Response
    {
        $address = $this->getUser()->getAddress() ?? new Address();

        $form = $this->createForm(UserAddressType::class, $address);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $address->setClient($this->getUser());
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
        $form = $this->createForm(SetFirstPasswordType::class);
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

    /**
     * @Route("/admin/user/list", name="app_admin_user_list")
     */
    public function userList(UserRepository $repository): Response
    {
        return $this->render('dashboard/page/admin/user/list.html.twig', [
            'users' => $repository->findBy(['removed' => false], ['isActive' => 'DESC', 'roles' => 'DESC', 'registrationDate' => 'DESC', 'email' => 'ASC']),
        ]);
    }

    /**
     * @Route("/admin/user/details/{id}", name="app_admin_user_details")
     */
    public function userDetails(User $user): Response
    {
        return $this->render('dashboard/page/admin/user/profile.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/admin/user/delete/{id}", name="app_admin_user_delete")
     */
    public function userDelete(User $user, EntityManagerInterface $manager): Response
    {
        $user
            ->setEmail(date('YmdHis') . $user->getEmail() . '-archived')
            ->setRemoved();

        $manager->persist($user);
        $manager->flush();

        return $this->redirectToRoute('app_admin_user_list');
    }

    /**
     * @Route("/admin/user/role/toggle/{id}", name="app_admin_user_role_toggle")
     */
    public function userRoleToggle(User $user, EntityManagerInterface $entityManager): RedirectResponse
    {
        $user->toggleAdmin();
        $entityManager->persist($user);
        $entityManager->flush();
        $this->addFlash("success", "Pomyślnie przełączono role");

        return $this->redirectToRoute('app_admin_user_details', ["id" => $user->getId()]);
    }
}