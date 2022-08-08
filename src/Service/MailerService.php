<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\User;
use App\Entity\Workspace;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

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

    public function sendCoupon(UserInterface $user, string $coupon)
    {
        $email = (new TemplatedEmail())
            ->from($this->mail)
            ->to($user->getUserIdentifier())
            ->subject('Potwierdzenie Email - Gamesites.pl')
            ->htmlTemplate("security/email/coupon.html.twig")
            ->context(['user' => $user, 'coupon' => $coupon]);

        $this->mailer->send($email);
    }

    public function sendNotification(Notification $notification)
    {
        $emails = array_map(fn(User $user) => $user->getEmail(), $notification->getUsers()->toArray());
        $emails = array_merge($emails, $notification->getRawMailList());

        $email = (new TemplatedEmail())
            ->from($this->mail)
            ->addBcc(...$emails)
            ->subject($notification->getTitle() . ' - Gamesites.pl')
            ->htmlTemplate("security/email/notification.html.twig")
            ->context(['notification' => $notification->getText()]);

        $this->mailer->send($email);
    }

    public function getProviderEmail(): ?string
    {
        return $this->mail;
    }
}