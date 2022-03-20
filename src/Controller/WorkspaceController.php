<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Workspace;
use App\Form\WorkspaceType;
use App\Form\WorkspaceUserAdd;
use App\Repository\UserRepository;
use App\Repository\WorkspaceRepository;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;


class WorkspaceController extends AbstractController
{

    /**
     * @Route("/dashboard/workspace", name="app_workspace_list")
     */
    public function workspaces(WorkspaceRepository $repository): Response
    {
        return $this->render('dashboard/page/workspace/list.html.twig', [
            'workspaceList' => $repository->getUserWorkspaces($this->getUser()),
        ]);
    }

    /**
     * @Route("/dashboard/workspace/select/{id}", name="app_workspace_select")
     */
    public function select(Workspace $workspace, RequestStack $requestStack): RedirectResponse
    {
        $requestStack->getSession()->set('workspace', $workspace);

        return $this->redirectToRoute('app_workspace_list');
    }

    /**
     * @Route("/dashboard/workspace/create", name="app_workspace_create")
     */
    public function create(Request $request, EntityManagerInterface $manager): Response
    {
        $workspace = new Workspace();
        $form = $this->createForm(WorkspaceType::class, $workspace);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $workspace->addUser($this->getUser());
            $manager->persist($workspace);
            $manager->flush();

            return $this->redirectToRoute('app_workspace_list');
        }

        return $this->render('dashboard/page/workspace/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/dashboard/workspace/{id}/user/add", name="app_workspace_user_add")
     */
    public function addUser(
        Workspace $workspace,
        Request $request,
        UserRepository $userRepository,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $manager,
        MailerService $mailerService
    ): Response {
        $form = $this->createForm(WorkspaceUserAdd::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$user = $userRepository->findOneBy(['email' => $form->getData()['email']])) {
                $password = hash('md5', date('Y-m-d'));

                $user = (new User())
                    ->setEmail($form->getData()['email'])
                    ->setRoles(['ROLE_USER'])
                    ->setIsActive(true)
                    ->setForceChangePassword(true);

                $user->setPassword($hasher->hashPassword($user, $password));

                $mailerService->sendTemporaryPassword($user, $workspace, $password);
            }

            $user->addWorkspace($workspace);

            $manager->persist($user);
            $manager->flush();

            return $this->redirectToRoute('app_workspace_list');
        }

        return $this->render('dashboard/page/workspace/add.user.html.twig', [
            'workspace' => $workspace,
            'form' => $form->createView(),
        ]);
    }
}