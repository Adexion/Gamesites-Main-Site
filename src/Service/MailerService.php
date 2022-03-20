<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Workspace;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

class MailerService
{
    private MailerInterface $mailer;
    /**
     * @var array|bool|float|int|string|null
     */
    private string $mail;

    public function __construct(MailerInterface $mailer, ParameterBagInterface $parameterBag)
    {
        $this->mailer = $mailer;
        $this->mail = $parameterBag->get('mail');
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function sendVerificationToken(User $user)
    {
        $email = (new TemplatedEmail())
            ->from($this->mail)
            ->to($user->getEmail())
            ->subject('Potwierdzenie Email - Gamesites.pl')
            ->htmlTemplate("security/email/verification.html.twig")
            ->context(['user' => $user]);

        $this->mailer->send($email);
    }

    public function sendTemporaryPassword(User $user, Workspace $workspace, string $password)
    {
        $email = (new TemplatedEmail())
            ->from($this->mail)
            ->to($user->getEmail())
            ->subject('Potwierdzenie Email - Gamesites.pl')
            ->htmlTemplate("security/email/tempPassword.html.twig")
            ->context(['user' => $user, 'workspace' => $workspace, 'password' => $password]);

        $this->mailer->send($email);
    }
}