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
            $workspace->setCreator($this->getUser());
            $manager->persist($workspace);
            $manager->flush();

            return $this->redirectToRoute('app_workspace_list');
        }

        return $this->render('dashboard/page/workspace/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/dashboard/workspace/{id}/user/list", name="app_workspace_user_list")
     */
    public function userList(Workspace $workspace): Response
    {
        return $this->render('dashboard/page/workspace/list.user.html.twig', [
            'workspace' => $workspace,
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
                $password = hash('md5', date('Y-m-d H:i:s'));

                $user = (new User())
                    ->setEmail($form->getData()['email'])
                    ->setRoles(['ROLE_USER'])
                    ->setIsActive(true)
                    ->setForceChangePassword(true);

                $user->setPassword(password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]));
                $mailerService->sendTemporaryPassword($user, $workspace, $password);
            }

            $user->addWorkspace($workspace);

            $manager->persist($user);
            $manager->flush();

            return $this->redirectToRoute('app_workspace_user_list', ['id' => $workspace->getId()]);
        }

        return $this->render('dashboard/page/workspace/add.user.html.twig', [
            'workspace' => $workspace,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/dashboard/workspace/{id}/user/delete/{userId}", name="app_workspace_user_delete")
     */
    public function deleteUser(Workspace $workspace, string $userId, UserRepository $repository, EntityManagerInterface $manager): RedirectResponse
    {
        if ($workspace->getCreator() !== $this->getUser()) {
            return $this->redirectToRoute('app_workspace_user_list', ['id' => $workspace->getId()]);
        }

        $workspace->removeUser($repository->find($userId));
        $manager->persist($workspace);
        $manager->flush();

        return $this->redirectToRoute('app_workspace_user_list', ['id' => $workspace->getId()]);
    }

    /**
     * @Route("/dashboard/workspace/{id}/user/creator/{userId}", name="app_workspace_user_creator")
     */
    public function giveCreator(Workspace $workspace, string $userId, UserRepository $repository, EntityManagerInterface $manager): RedirectResponse
    {
        if ($workspace->getCreator() !== $this->getUser()) {
            return $this->redirectToRoute('app_workspace_user_list', ['id' => $workspace->getId()]);
        }

        $workspace->setCreator($repository->find($userId));
        $manager->persist($workspace);
        $manager->flush();

        return $this->redirectToRoute('app_workspace_user_list', ['id' => $workspace->getId()]);
    }

    /**
     * @Route("/dashboard/workspace/{id}/leave", name="app_workspace_leave")
     */
    public function leaveWorkspace(Workspace $workspace, UserRepository $repository, EntityManagerInterface $manager): RedirectResponse
    {
        if ($workspace->getCreator() === $this->getUser()) {
            return $this->redirectToRoute('app_workspace_user_list', ['id' => $workspace->getId()]);
        }

        /** @var User $user */
        $user =  $this->getUser();

        $user->removeWorkspace($workspace);
        $manager->persist($user);
        $manager->flush();

        return $this->redirectToRoute('app_workspace_list');
    }

    /**
     * @Route("/dashboard/workspce/checkout", name="app_workspace_check_out")
     */
    public function checkout(Request $request):Response
    {
        $request->getSession()->remove('workspace');

        return $this->redirectToRoute('app_workspace_list');
    }
}