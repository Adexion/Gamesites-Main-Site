<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Form\NotificationType;
use App\Repository\NotificationRepository;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NotificationController extends AbstractController
{
    /**
     * @Route("/admin/notification", name="app_admin_notification_list")
     */
    public function list(NotificationRepository $repository): Response
    {
        return $this->render('dashboard/page/admin/notification/list.html.twig', [
            'notifications' => $repository->findBy([], ['datetime' => 'DESC']),
        ]);
    }

    /**
     * @Route("/admin/notification/add", name="app_admin_notification_add")
     */
    public function add(Request $request, EntityManagerInterface $manager, MailerService $service): Response
    {
        $notification = new Notification();
        $form = $this->createForm(NotificationType::class, $notification);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($notification->getIsEmail() && empty($notification->getUsers()->toArray())) {
                $form->get('users')->addError(New FormError('Wybierz przynajmniej jedno konto by móc wysłać email.'));

                return $this->render('dashboard/page/admin/notification/add.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $manager->persist($notification);
            $manager->flush();

            if ($notification->getIsEmail()) {
                $service->sendNotification($notification);
            }

            return $this->redirectToRoute('app_admin_notification_list');
        }

        return $this->render('dashboard/page/admin/notification/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/api/notification", name="app_notification_list")
     */
    public function api(NotificationRepository $repository): Response
    {
        $notifications = array_map(fn(Notification $n) => [
            'title' => $n->getTitle(),
            'text' => nl2br($n->getText()),
            'date' => $n->getDatetime()->format('d.m.Y H:i'),
        ], $repository->getCurrentUser($this->getUser()));

        return new JsonResponse([
            'notifications' => $notifications,
        ]);
    }
}