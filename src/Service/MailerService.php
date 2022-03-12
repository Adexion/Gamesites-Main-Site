<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

class MailerService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendVerificationToken(User $user)
    {

        $email = (new TemplatedEmail())
            ->from('noreply@gamesites.pl')
            ->to($user->getEmail())
            ->subject('Potwierdzenie Email - Gamesites.pl')
            ->htmlTemplate("security/email/verification.html.twig")
            ->context(['user' => $user]);

        $this->mailer->send($email);
    }
}