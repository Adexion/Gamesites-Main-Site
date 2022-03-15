<?php

namespace App\Controller;

use App\Entity\Server;
use App\Form\OrderType;
use App\Form\SetupType;
use App\Repository\OrderRepository;
use App\Repository\ServerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ServerController extends AbstractController
{
    /**
     * @Route("/dashboard/order", name="app_order")
     */
    public function order(Request $request, OrderRepository $repository, EntityManagerInterface $manager): Response
    {
        $server = new Server();
        $form = $this->createForm(OrderType::class, $server);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
           $order = $repository->findOneBy(['coupon' => $server->getCoupon()]);
           $server->setExpiryDate($order->getExpiryDate());

           if (!$manager->getRepository(Server::class)->findOneBy(['coupon' => $server->getCoupon()])) {
               $manager->persist($server);
               $manager->flush();
           }

           return $this->redirectToRoute('app_setup', [
               'coupon' => $server->getCoupon()
           ]);
        }

        return $this->render('dashboard/page/order/orderRealization.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/dashboard/setup/{coupon}", name="app_setup")
     */
    public function configuration(string $coupon, Request $request, ServerRepository $repository, EntityManagerInterface $manager): Response
    {
        $server = $repository->findOneBy(['coupon' => $coupon]);
        $form = $this->createForm(SetupType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $name = $form->getData()['name'];
            if (!$server->getName() || $server->getName() === $name) {
                $server->addClient($this->getUser());
                $server->setName($name);

                $manager->persist($server);
                $manager->flush();

                return $this->redirectToRoute('app_dashboard');
            }

            $form->get('name')->addError(new FormError('Podaj prawidłową nazwę serwera.'));
        }

        return $this->render('dashboard/page/order/orderSetup.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/dashboard/setting/{coupon}", name="app_setting")
     */
    public function setting(string $coupon, ServerRepository $repository): Response
    {
        $server = $repository->findOneBy(['coupon' => $coupon]);

        if(!$server->getInstalationFinish()) {
            return $this->redirectToRoute('app_install', [
                'coupon' => $coupon
            ]);
        }

        return $this->render('dashboard/page/settings.html.twig', [
            'server' => $server
        ]);
    }

    /**
     * @Route("/dashboard/setting/{coupon}/install", name="app_install")
     */
    public function install(string $coupon, ServerRepository $repository): Response
    {
        $template = 'dashboard/page/installation.html.twig';
        $server = $repository->findOneBy(['coupon' => $coupon]);

        return $this->render($template, [
            'server' => $server
        ]);
    }
}