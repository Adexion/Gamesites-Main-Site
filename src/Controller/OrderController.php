<?php

namespace App\Controller;

use DateTime;
use Exception;
use App\Entity\Order;
use App\Entity\Application;
use App\Entity\Notification;
use App\Form\CreateOrderType;
use App\Entity\ReferrerPoint;
use App\Form\RealizeOrderType;
use App\Service\MailerService;
use App\Enum\ReferrerPointType;
use App\Repository\OrderRepository;
use App\Service\RandomCouponGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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
        MailerService $service
    ): Response {
        if (!$this->getUser()->getAddress()) {
            $this->addFlash('error', 'Dane adresowe są wymagany w celu złożenia zamówienia.');

            return $this->redirectToRoute('app_profile');
        }

        $order = new Order();
        $form = $this->createForm(CreateOrderType::class, $order);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $order->setCreator($this->getUser());
            $order->setCoupon($couponGenerator->generate());
            $order->setIsActive($order->getInvoice() && $this->getUser()->getAddress()->getTin());

            $entityManager->persist($order);
            $entityManager->flush();

            $notification = (new Notification())
                ->setText('Nowe zamówienie w systemie')
                ->setTitle("Użytkownik {$this->getUser()->getEmail()} utworzył nowe zamówienie o numerze - {$order->getCoupon()}")
                ->addRawMail($service->getProviderEmail());

            $service->sendCoupon($this->getUser(), $order->getCoupon());
            $service->sendNotification($notification);

            return $this->redirectToRoute('app_order_confirmation', ['id' => $order->getId()]);
        }

        return $this->render('dashboard/page/order/orderCreate.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/order/confirmation/{id}", name="app_order_confirmation")
     */
    public function adminOrderConfirmation(Order $order): Response
    {
        return $this->render('dashboard/page/order/orderConfirmation.html.twig', [
            'order' => $order,
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
    public function adminOrderToggle(Order $order, EntityManagerInterface $manager, MailerService $service): RedirectResponse
    {
        $order->setIsActive(!$order->getIsActive());
        $manager->persist($order);
        $manager->flush();

        if (!$order->getIsActive()) {
            return $this->redirectToRoute('app_admin_order_list');
        }

        if ($referrer = $order->getCreator()->getInviting()) {
            $point = (new ReferrerPoint())
                ->setClient($order->getCreator())
                ->setPoint(15)
                ->setDate(new DateTime())
                ->setType(ReferrerPointType::INFLOW);
            $manager->persist($point);
            $manager->flush();

            $referrer->addPoint($point);
            $manager->persist($referrer);
            $manager->flush();
        }

        if ($order->getPaymentNotification()) {
            $notification = (new Notification())
                ->setText("Twój kupon {$order->getCoupon()} został aktywowany. Możesz teraz założyć aplikacje!")
                ->setTitle('Potwierdzenie zamówienia')
                ->addUser($order->getCreator());

            $service->sendNotification($notification);
        }


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