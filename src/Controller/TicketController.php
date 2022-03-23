<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\TicketMessage;
use App\Enum\TicketStatusEnum;
use App\Form\TicketMessageType;
use App\Form\TicketType;
use App\Repository\TicketRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TicketController extends AbstractController
{

    /**
     * @Route("/dashboard/ticket/list", name="app_ticket_list")
     */
    public function list(TicketRepository $repository): Response
    {
        return $this->render('dashboard/page/ticket/list.html.twig', [
            'tickets' => $repository->findBy(['creator' => $this->getUser()]),
        ]);
    }

    /**
     * @Route("/dashboard/ticket/create", name="app_ticket_create")
     */
    public function create(Request $request, EntityManagerInterface $manager)
    {
        $ticket = new Ticket();
        $form = $this->createForm(TicketType::class, $ticket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $ticket->setCreator($this->getUser());
            $ticket->setStatus(TicketStatusEnum::PENDING);
            $manager->persist($ticket);
            $manager->flush();

            return $this->redirectToRoute('app_ticket_list');
        }

        return $this->render('dashboard/page/ticket/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/dashboard/ticket/chat/{id}")
     */
    public function chat(Ticket $ticket, Request $request, EntityManagerInterface $manager): Response
    {
        if (!$this->isGranted('ROLE_ADMIN') && $ticket->getCreator() !== $this->getUser()) {
            return $this->redirectToRoute('app_ticket_list');
        }

        $message = new TicketMessage();
        $form = $this->createForm(TicketMessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->isGranted('ROLE_ADMIN')) {
                $ticket->setStatus(TicketStatusEnum::WORKING);
            }

            $message->setCreator($this->getUser());
            $message->setTicket($ticket);
            $manager->persist($message);
            $manager->flush();

            return $this->redirectToRoute('app_ticket_chat', ['id' => $ticket->getId()]);
        }

        return $this->render('dashboard/page/ticket/chat.html.twig', [
            'ticket' => $ticket,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/ticket/close/{id}", name="app_ticket_close")
     */
    public function close(Ticket $ticket, EntityManagerInterface $entityManager)
    {
        if ($this->getUser() !== $ticket->getCreator() && !$this->isGranted("ROLE_ADMIN")) {
            return $this->redirectToRoute('app_ticket_list');
        }

        $ticket->setStatus(TicketStatusEnum::CLOSED);
        $entityManager->persist($ticket);
        $entityManager->flush();
    }

    /**
     * @Route("/admin/ticket/list", name="app_admin_ticket_list")
     */
    public function adminList(TicketRepository $repository): Response
    {
        return $this->render('dashboard/page/admin/ticket/list.html.twig', [
            'tickets' => $repository->findAll(),
        ]);
    }
}