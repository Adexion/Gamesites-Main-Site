<?php

namespace App\Controller;

use App\Repository\RemoteRepository;
use App\Repository\ServerRepository;
use App\Service\DomainService;
use Doctrine\DBAL\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use Symfony\Component\Routing\Annotation\Route;

class ServerAPIController extends AbstractController
{
    /**
     * @Route("/v1/setup/initialize", name="app_api_initial")
     */
    public function initialize(Request $request, ServerRepository $serverRepository): Response
    {
        $commandList = [
            "cd /var/www/ && git clone git@github.com:Adexion/GameSitesSell.git {{ dir }}",
            'echo "APP_ENV=prod \nDATABASE_URL=\"mysql://symfony:8bb725a4w3K*@127.0.0.1:3306/{{ dir }}?serverVersion=5.7\" \nAPP_SECRET=3bb3538a0b014d635d8380564a84e48b" > /var/www/{{ dir }}/.env'
        ];

        $response = [
            'title' => 'Tworzenie instancji bazy danych ...',
            'percentage' => 15,
        ];

        sleep(2);
        return $this->runner($commandList, $response, $request, $serverRepository);
    }

    /**
     * @Route("/v1/setup/database", name="app_api_database")
     */
    public function database(Request $request, ServerRepository $serverRepository): Response
    {
        $commandList = [
            "sudo -S mysql DROP DATABASE IF EXIST {{ dir }}",
            "sudo -S mysql CREATE DATABASE {{ dir }}",
            "sudo -S mysql {{ dir }} \"INSERT INTO user (email, roles, password, googleAuthenticatorSecret) VALUES ('biuro@gamesites.pl', '[\"ROLE_ADMIN\"]', '\$2y\$13\$qGrP.kZHAj0zXXVj5E9ASereKEtXl25ii0ofqJ41jduB2clDKaA9y', NULL);\"",
            "sudo -S mysql {{ dir }} \"INSERT INTO user (email, roles, password, googleAuthenticatorSecret) VALUES ('{$this->getUser()->getUserIdentifier()}', '[\"ROLE_ADMIN\"]', '{$this->getUser()->getPassword()}', NULL)\""
        ];

        $response = [
            'title' => 'Aktualizowanie bibliotek aplikacji (To może chwilę potrwać) ...',
            'percentage' => 30,
        ];

        sleep(2);
        return $this->runner($commandList, $response, $request, $serverRepository);
    }

    /**
     * @Route("/v1/setup/install", name="app_api_install")
     */
    public function install(Request $request, ServerRepository $serverRepository): Response
    {
        $commandList = [
            "cd /var/www/{{ dir }} && sudo -S composer install",
            "cd /var/www/{{ dir }} && sudo -S composer dump-autoload --no-dev --classmap-authoritative",
            "cd /var/www/{{ dir }} && php bin/console doctrine:schema:update --force",
            "cd /var/www/{{ dir }} && chmod 777 var -R",
            "cd /var/www/{{ dir }} && chmod 777 public/assets -R",
            "cd /var/www/{{ dir }} && sudo -S yarn install",
            "cd /var/www/{{ dir }} && sudo -S yarn build",
        ];

        $response = [
            'title' => 'Aktualizacja wpisu w rejestrze domen ...',
            'percentage' => 70,
        ];

        return $this->runner($commandList, $response, $request, $serverRepository);
    }

    /**
     * @Route("/v1/setup/domain", name="app_api_domain")
     */
    public function domain(Request $request, ServerRepository $serverRepository): Response
    {
        $commandList = [
            "sudo -S cat > /etc/nginx/sites-available/{{ dir }}.conf <<EOF{{ string }}EOF",
            "sudo -S ln -s /etc/nginx/sites-available/{{ dir }}.conf /etc/nginx/sites-enabled/{{ dir }}.conf &> /dev/null",
            "sudo -S certbot --nginx -d {{ domain }} -d www.{{ domain }}  --redirect -n &> /dev/null"
        ];

        $response = [
            'title' => 'Pre-konfiguracja aplikacji ...',
            'percentage' => 95,
        ];

        return $this->runner($commandList, $response, $request, $serverRepository);
    }

    /**
     * @Route("/v1/setup/configure", name="app_api_configure")
     * @throws Exception
     */
    public function configure(Request $request, ServerRepository $serverRepository): Response
    {
        $content = json_decode($request->getContent(), true);
        $server = $serverRepository->findOneBy(['coupon' => $content['token']]);
        $dir = join('', array_map(fn($value) => ucfirst(strtolower($value)), explode(' ', $server->getName())));

//        $repository = new RemoteRepository($dir);
//        $repository->insertConfiguration($server);

        sleep(2);
        return new JsonResponse([
            'title' => 'Zainstalowano pomyślnie!',
            'percentage' => 100,
            'style' => 'success'
        ]);
    }

    private function runner(array $commandList, array $response, Request $request, ServerRepository $serverRepository): JsonResponse
    {
        $content = json_decode($request->getContent(), true);
        $server = $serverRepository->findOneBy(['coupon' => $content['token']]);

        $dir = join('', array_map(fn($value) => ucfirst(strtolower($value)), explode(' ', $server->getName())));
        foreach ($commandList as $command) {
            $replaced =  str_replace(
                ['{{ dir }}', '{{ string }}', '{{ domain }}'],
                [$dir, DomainService::getFileContent($server->getDomain(), $dir), $server->getDomain()],
                $command
            );

            Process::fromShellCommandline($replaced, null, null, null,3600)
                ->run(function($type, $buffer) {
                    '';
                });
        }

        return new JsonResponse($response);
    }
}