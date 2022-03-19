<?php

namespace App\Controller;

use App\Entity\Application;
use App\Form\ApplicationResetPasswordType;
use App\Form\OrderType;
use App\Form\ApplicationSetupType;
use App\Repository\ApplicationRepository;
use App\Repository\OrderRepository;
use App\Repository\RemoteRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ApplicationController extends AbstractController
{
    /**
     * @Route("/dashboard/order", name="app_order")
     */
    public function order(Request $request, OrderRepository $repository, EntityManagerInterface $manager): Response
    {
        $application = new Application();
        $form = $this->createForm(OrderType::class, $application);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $order = $repository->findOneBy(['coupon' => $application->getCoupon()]);
            $application->setExpiryDate($order->getExpiryDate());

            if (!$manager->getRepository(Application::class)->findOneBy(['coupon' => $application->getCoupon()])) {
                $manager->persist($application);
                $manager->flush();
            }

            return $this->redirectToRoute('app_setup', [
                'coupon' => $application->getCoupon(),
            ]);
        }

        return $this->render('dashboard/page/order/orderRealization.html.twig', [
            'form' => $form->createView(),
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
                $application->addClient($this->getUser());
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

        $form = $this->createForm(ApplicationResetPasswordType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $remoteRepository = new RemoteRepository($application->getDir());
            $remoteRepository->updateUserPassword($this->getUser(), $form->getData()['password']);
        }

        return $this->render('dashboard/page/application/settings.html.twig', [
            'application' => $application,
            'form' => $form->createView(),
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