<?php

namespace App\Controller;

use App\Entity\Workspace;
use App\Form\WorkspaceType;
use App\Repository\WorkspaceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WorkspaceController extends AbstractController
{
    /**
     * @Route("/workspace", name="app_workspace_list")
     */
    public function workspaces(WorkspaceRepository $repository): Response
    {
        return $this->render('dashboard/page/workspace/list.html.twig',[
            'workspaceList' => $repository->getUserWorkspaces($this->getUser())
        ]);
    }

    /**
     * @Route("/workspace/create", name="app_workspace_create")
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
}