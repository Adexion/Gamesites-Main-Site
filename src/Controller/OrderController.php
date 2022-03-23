<?php

namespace App\Controller;

use App\Entity\Application;
use App\Entity\Order;
use App\Form\CreateOrderType;
use App\Form\RealizeOrderType;
use App\Repository\OrderRepository;
use App\Service\MailerService;
use App\Service\RandomCouponGenerator;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    /**
     * @Route("/dashboard/order/realize", name="app_order_realize")
     * @throws Exception
     */
    public function realizeOrder(Request $request, OrderRepository $repository, EntityManagerInterface $manager): Response
    {
        if (!$this->getUser()->getAddress()) {
            $this->addFlash('error', 'Dane adresowe są wymagany w celu złożenia zamówienia.');

            return $this->redirectToRoute('app_profile');
        }

        $application = new Application();
        $form = $this->createForm(RealizeOrderType::class, $application);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $order = $repository->findOneBy(['coupon' => $application->getCoupon()]);
            $application->setExpiryDate(new DateTime($order->getExpiryDate()));

            if (!$manager->getRepository(Application::class)->findOneBy(['coupon' => $application->getCoupon()])) {
                $application->setCreator($this->getUser());
                $application->setInvoice($order->getInvoice());
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
     * @Route("/dashboard/order/create", name="app_order_create")
     */
    public function generateOrder(
        Request $request,
        RandomCouponGenerator $couponGenerator,
        EntityManagerInterface $entityManager,
        MailerService $mailerService
    ): Response {
        if (!$this->getUser()->getAddress()) {
            $this->addFlash('error', 'Dane adresowe są wymagany w celu złożenia zamówienia.');

            return $this->redirectToRoute('app_profile');
        }

        $order = new Order();
        $form = $this->createForm(CreateOrderType::class, $order);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $order->setCoupon($couponGenerator->generate());
            $order->setIsActive($order->getInvoice() && $this->getUser()->getAddress()->getTin());

            $entityManager->persist($order);
            $entityManager->flush();

            $mailerService->sendCoupon($this->getUser(), $order->getCoupon());
            //ToDo: add payment mechanism here
            return $this->render('dashboard/page/order/orderConfirmation.html.twig', [
                'order' => $order,
            ]);
        }

        return $this->render('dashboard/page/order/orderCreate.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/admin/order/list", name="app_admin_order_list")
     */
    public function adminOrderList(OrderRepository $repository): Response
    {
        return $this->render('dashboard/page/admin/order/list.html.twig', [
            'orders' => $repository->findAll(),
        ]);
    }

    /**
     * @Route("/admin/order/toggle/{id}", name="app_admin_order_toggle")
     */
    public function adminOrderToggle(Order $order, EntityManagerInterface $manager): RedirectResponse
    {
        $order->setIsActive(!$order->getIsActive());
        $manager->persist($order);
        $manager->flush();

        return $this->redirectToRoute('app_admin_order_list');
    }

    /**
     * @Route("/admin/order/remove/{id}", name="app_admin_order_remove")
     */
    public function adminOrderRemove(Order $order, EntityManagerInterface $manager): RedirectResponse
    {
        $manager->remove($order);
        $manager->flush();

        return $this->redirectToRoute('app_admin_order_list');
    }
}