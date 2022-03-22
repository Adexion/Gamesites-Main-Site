<?php

namespace App\Controller;

set_time_limit(0);

use App\Repository\RemoteRepository;
use App\Repository\ApplicationRepository;
use App\Service\DomainService;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMException;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;

class ApplicationAPIController extends AbstractController
{
    /**
     * @Route("/v1/setup/initialize", name="app_api_initial")
     */
    public function initialize(Request $request, ApplicationRepository $repository, EntityManagerInterface $manager): Response
    {
        $content = json_decode($request->getContent(), true);
        $application = $repository->findOneBy(['coupon' => $content['token']]);

        $commandList = [
            "cd /var/www/ && git clone git@github.com:Adexion/GameSitesSell.git {{ dir }}",
            'echo "APP_ENV=prod \nDATABASE_URL=\"mysql://symfony:8bb725a4w3K*@127.0.0.1:3306/{{ dir }}?serverVersion=5.7\" \nAPP_SECRET=3bb3538a0b014d635d8380564a84e48b" > /var/www/{{ dir }}/.env',
        ];

        $response = [
            'title' => 'Tworzenie instancji bazy danych ...',
            'percentage' => 15,
        ];

        sleep(2);

        $application->setWasInstallerRun(true);
        $manager->persist($application);
        $manager->flush();

        return $this->runner($commandList, $response, $request, $repository);
    }

    /**
     * @Route("/v1/setup/database", name="app_api_database")
     */
    public function database(Request $request, ApplicationRepository $repository): Response
    {
        $commandList = [
            "sudo -S mysql -e \"DROP DATABASE {{ dir }}\"",
            "sudo -S mysql -e \"CREATE DATABASE {{ dir }}\"",
        ];

        $response = [
            'title' => 'Aktualizowanie bibliotek aplikacji (To może chwilę potrwać) ...',
            'percentage' => 30,
        ];

        sleep(2);

        return $this->runner($commandList, $response, $request, $repository);
    }

    /**
     * @Route("/v1/setup/install", name="app_api_install")
     * @throws Exception
     */
    public function install(Request $request, ApplicationRepository $applicationRepository): Response
    {
        $commandList = [
            "cd /var/www/{{ dir }} && sudo -S composer install",
            "cd /var/www/{{ dir }} && sudo -S composer dump-autoload --no-dev --classmap-authoritative",
            "cd /var/www/{{ dir }} && sudo -S bin/console doctrine:schema:update --force",
        ];

        $content = json_decode($request->getContent(), true);
        $application = $applicationRepository->findOneBy(['coupon' => $content['token']]);

        $response = [
            'title' => 'Budowanie pakietów webowych (To może chwilę potrwać) ...',
            'percentage' => 50,
        ];

        $response = $this->runner($commandList, $response, $request, $applicationRepository);

        $repository = new RemoteRepository($application->getDir());
        $repository->insertUsers($this->getUser());

        return $response;
    }

    /**
     * @Route("/v1/setup/webpack", name="app_api_webpack")
     */
    public function webpack(Request $request, ApplicationRepository $applicationRepository): Response
    {
        $commandList = [
            "cd /var/www/{{ dir }} && sudo -S chmod 777 var -R",
            "cd /var/www/{{ dir }} && sudo -S chmod 777 public/assets -R",
            "cd /var/www/{{ dir }} && sudo -S yarn install",
            "cd /var/www/{{ dir }} && sudo -S yarn build",
        ];

        $response = [
            'title' => 'Aktualizacja wpisu w rejestrze domen ...',
            'percentage' => 70,
        ];

        return $this->runner($commandList, $response, $request, $applicationRepository);
    }

    /**
     * @Route("/v1/setup/domain", name="app_api_domain")
     */
    public function domain(Request $request, ApplicationRepository $repository): Response
    {
        $commandList = [
            "sudo -S rm /etc/nginx/sites-available/{{ dir }}.conf",
            "sudo -S printf '{{ string }}' >> /etc/nginx/sites-available/{{ dir }}.conf",
            "sudo -S ln -s /etc/nginx/sites-available/{{ dir }}.conf /etc/nginx/sites-enabled/{{ dir }}.conf &> /dev/null",
            "sudo -S certbot --nginx -d {{ domain }} -d www.{{ domain }}  --redirect -n &> /dev/null",
        ];

        $response = [
            'title' => 'Pre-konfigurowanie aplikacji ...',
            'percentage' => 95,
        ];

        return $this->runner($commandList, $response, $request, $repository);
    }

    /**
     * @Route("/v1/setup/configure", name="app_api_configure")
     * @throws ORMException|Exception
     */
    public function configure(Request $request, ApplicationRepository $applicationRepository, EntityManagerInterface $manager): Response
    {
        $content = json_decode($request->getContent(), true);
        $application = $applicationRepository->findOneBy(['coupon' => $content['token']]);

        $repository = new RemoteRepository($application->getDir());
        $repository->insertConfiguration($application);

        sleep(2);

        $application->setInstallationFinish(true);
        $manager->persist($application);
        $manager->flush();

        return new JsonResponse([
            'title' => 'Zainstalowano pomyślnie!',
            'percentage' => 100,
            'style' => 'success',
        ]);
    }

    private function runner(array $commandList, array $response, Request $request, ApplicationRepository $repository): JsonResponse
    {
        $content = json_decode($request->getContent(), true);
        $application = $repository->findOneBy(['coupon' => $content['token']]);

        $logs = new stdClass();
        $logs->err = [];
        foreach ($commandList as $command) {
            $replaced = str_replace(
                ['{{ dir }}', '{{ string }}', '{{ domain }}'],
                [$application->getDir(), DomainService::getFileContent($application->getDomain(), $application->getDir()), $application->getDomain()],
                $command
            );

            Process::fromShellCommandline($replaced, null, null, null, 3600)
                ->run(function ($type, $buffer) use ($logs) {
                    if ($type == 'err') {
                        $logs->err[] = $buffer;
                    }
                });
        }

        return new JsonResponse($response + ['err' => $logs->err]);
    }
}