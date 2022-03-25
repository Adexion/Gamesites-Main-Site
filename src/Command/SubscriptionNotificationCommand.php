<?php

namespace App\Command;

use App\Entity\Application;
use App\Entity\Notification;
use App\Repository\ApplicationRepository;
use App\Service\MailerService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SubscriptionNotificationCommand extends Command
{
    protected static $defaultName = 'app:subscription-notification';

    private MailerService $mailerService;
    private ApplicationRepository $applicationRepository;
    private EntityManagerInterface $manager;

    public function __construct(MailerService $mailerService, ApplicationRepository $applicationRepository, EntityManagerInterface $manager)
    {
        $this->mailerService = $mailerService;
        $this->applicationRepository = $applicationRepository;
        $this->manager = $manager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Wysyłka powiadomień email na koniec czasu płatności za subskrypcje aplikacji');
    }

    /** @throws Exception */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln([
            'Subcription Notification',
            '============',
            '',
        ]);

        $group1 = $this->applicationRepository->getEndTimeApplication(new DateTime('+7 days'));
        $group2 = $this->applicationRepository->getEndTimeApplication(new DateTime('+3 days'));
        $group3 = $this->applicationRepository->getEndTimeApplication(new DateTime('+1 day'));

        $this->sendNotification($group1, 7);
        $this->sendNotification($group2, 3);
        $this->sendNotification($group3, 1);

        $output->writeln(['Messages was send!']);

        return Command::SUCCESS;
    }

    private function sendNotification(array $group, int $days)
    {
        /** @var Application $application */
        foreach ($group as $application) {
            $limitWorking = $days + 6;

            $notification = (new Notification())
                ->addUser($application->getCreator())
                ->setTitle("Aplikacja {$application->getName()} wkrótce wygaśnie")
                ->setIsEmail(true)
                ->setText("Czas opłacenia aplikacji <b>{$application->getName()}</b> dobiega końca. <br/>Zastało <b>{$days}</b> dni aktywności aplikacji. <br/>Jeżeli nic z tym nie zrobisz, to w przeciągu {$limitWorking} aplikacja zostanie całkowicie wyłączona.");

            $this->manager->persist($notification);
            $this->manager->flush();

            $this->mailerService->sendNotification($notification);
        }
    }
}