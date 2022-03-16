<?php

namespace App\Controller;

use App\Entity\Workspace;
use App\Form\WorkspaceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WorkspaceController extends AbstractController
{

    /**
     * @Route("/workspace/create", name="app_workspace_create")
     */
    public function create(Request $request, EntityManagerInterface $manager): Response
    {

        $workspace = new Workspace();
        $form = $this->createForm(WorkspaceType::class, $workspace);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->persist($workspace);
            $manager->flush();

            return $this->redirectToRoute('app_server_list');
        }

        return $this->render('dashboard/page/workspace/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}