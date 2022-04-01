<?php

namespace App\Controller;

use App\Form\ApplicationEditType;
use App\Form\ApplicationSetupType;
use App\Repository\ApplicationRepository;
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

        return $this->render('dashboard/page/application/list.html.twig', [
            'userApplicationList' => $repository->getCurrentApplications($workspace, $this->getUser()),
        ]);
    }

    /**
     * @Route("/dashboard/setup/{coupon}", name="app_setup")
     */
    public function configuration(string $coupon, Request $request, ApplicationRepository $repository, EntityManagerInterface $manager): Response
    {
        $application = $repository->findOneBy(['coupon' => $coupon]);
        if (!$application) {
            return $this->redirectToRoute('app_application_list');
        }

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
    public function setting(string $coupon, ApplicationRepository $repository, Request $request, EntityManagerInterface $manager): Response
    {
        $application = $repository->findOneBy(['coupon' => $coupon]);
        if (!$application->getWasInstallerRun() && !$application->getInstallationFinish()) {
            return $this->redirectToRoute('app_install', [
                'coupon' => $coupon,
            ]);
        }

        $form = $this->createForm(ApplicationEditType::class, null, ['user' => $this->getUser()]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->getData()['password']) {
                $remoteRepository = new RemoteRepository($application->getDir());
                $remoteRepository->updateUserPassword($this->getUser(), $form->getData()['password']);
            }

            $application->setWorkspace($form->getData()['workspace']);
            $manager->persist($application);
            $manager->flush();

            $this->addFlash('success', 'Pomyślnie zapisano dane!');
        }

        return $this->render('dashboard/page/application/settings.html.twig', [
            'application' => $application,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/dashboard/setting/{coupon}/install", name="app_install")
     */
    public function install(string $coupon, ApplicationRepository $repository): Response
    {
        $application = $repository->findOneBy(['coupon' => $coupon]);
        if ($application->getWasInstallerRun() || $application->getInstallationFinish()) {
            return $this->redirectToRoute('app_application_list');
        }

        return $this->render('dashboard/page/application/installation.html.twig', [
            'application' => $application,
        ]);
    }

    /**
     * @Route("/dashboard/setting/{coupon}/reinstall", name="app_reinstall")
     */
    public function reinstall(string $coupon, ApplicationRepository $repository, EntityManagerInterface $entityManager): Response
    {
        $application = $repository->findOneBy(['coupon' => $coupon]);
        if (!$application->getWasInstallerRun() && !$application->getInstallationFinish()) {
            return $this->redirectToRoute('app_application_list');
        }
        if ($application->getWasInstallerRun() && !$application->getInstallationFinish()) {
            $this->addFlash(
                'warning',
                'Instalacja może być w toku. Upewnij się że nikt inny jej nie uruchomił zanim uruchomisz ją ponownie!'
            );
        }

        $application->setInstallationFinish(false);
        $application->setWasInstallerRun(false);
        $entityManager->persist($application);
        $entityManager->flush();

        return $this->render('dashboard/page/application/installation.html.twig', [
            'application' => $application,
        ]);
    }
}