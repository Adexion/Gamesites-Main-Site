<?php

namespace App\Controller;

use App\Entity\Application;
use App\Form\ApplicationEditType;
use App\Form\ApplicationResetPasswordType;
use App\Form\ApplicationSetupType;
use App\Form\RealizeOrderType;
use App\Repository\ApplicationRepository;
use App\Repository\OrderRepository;
use App\Repository\RemoteRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApplicationController extends AbstractController
{
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


    /**
     * @Route("/dashboard/setup/{coupon}", name="app_setup")
     */
    public function configuration(string $coupon, Request $request, ApplicationRepository $repository, EntityManagerInterface $manager): Response
    {
        $application = $repository->findOneBy(['coupon' => $coupon]);
        $form = $this->createForm(ApplicationSetupType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $name = $form->getData()['name'];
            if (!$application->getName() || $application->getName() === $name) {
                $application->setCreator($this->getUser());
                $application->setName($name);

                $manager->persist($application);
                $manager->flush();

                return $this->redirectToRoute('app_application_list');
            }

            $form->get('name')->addError(new FormError('Podaj prawidłową nazwę aplikacji.'));
        }

        return $this->render('dashboard/page/order/orderSetup.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/dashboard/setting/{coupon}", name="app_setting")
     * @throws Exception
     */
    public function setting(string $coupon, ApplicationRepository $repository, Request $request): Response
    {
        $application = $repository->findOneBy(['coupon' => $coupon]);
        if (!$application->getInstallationFinish()) {
            return $this->redirectToRoute('app_install', [
                'coupon' => $coupon,
            ]);
        }

        $resetPasswordForm = $this->createForm(ApplicationResetPasswordType::class);
        $resetPasswordForm->handleRequest($request);
        if ($resetPasswordForm->isSubmitted() && $resetPasswordForm->isValid()) {
            $remoteRepository = new RemoteRepository($application->getDir());
            $remoteRepository->updateUserPassword($this->getUser(), $resetPasswordForm->getData()['password']);
        }

        return $this->render('dashboard/page/application/settings.html.twig', [
            'application' => $application,
            'resetPasswordForm' => $resetPasswordForm->createView(),
            'editForm' => $this->createForm(ApplicationEditType::class, $application, ['user' => $this->getUser()])->createView(),
        ]);
    }

    /**
     * @Route("/dashboard/setting/{coupon}/edit", name="app_setting_edit")
     */
    public function edit(string $coupon, ApplicationRepository $repository, Request $request, EntityManagerInterface $manager): Response
    {
        $application = $repository->findOneBy(['coupon' => $coupon]);
        $editForm = $this->createForm(ApplicationEditType::class, $application, ['user' => $this->getUser(), 'name' => $application->getName()]);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $manager->persist($application);
            $manager->flush();
        }

        return $this->render('dashboard/page/application/settings.html.twig', [
            'application' => $application,
            'resetPasswordForm' => $this->createForm(ApplicationResetPasswordType::class)->createView(),
            'editForm' => $editForm->createView(),
        ]);
    }

    /**
     * @Route("/dashboard/setting/{coupon}/install", name="app_install")
     */
    public function install(string $coupon, ApplicationRepository $repository): Response
    {
        $application = $repository->findOneBy(['coupon' => $coupon]);
        if ($application->getWasInstallerRun() || $application->getInstallationFinish()) {
            $this->redirectToRoute('app_application_list');
        }

        return $this->render('dashboard/page/application/installation.html.twig', [
            'application' => $application,
        ]);
    }
}