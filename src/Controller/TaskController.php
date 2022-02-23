<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route as Route;

class TaskController extends AbstractController
{

    #[Route('/tasks', name: 'task_list')]
    public function listAction(ManagerRegistry $managerRegistry): Response
    {
        return $this->render('task/list.html.twig',
            ['tasks' => $managerRegistry->getRepository('App:Task')->findBy(array('isDone' => false)),
                'isDone' => false]);
    }

    #[Route('/tasks/done', name: 'task_done_list')]
    public function listDoneAction(ManagerRegistry $managerRegistry): Response
    {
        return $this->render('task/list.html.twig',
            ['tasks' => $managerRegistry->getRepository('App:Task')->findBy(array('isDone' => true)),
                'isDone' => true]);
    }

    #[Route('/tasks/create', name: 'task_create')]
    public function createAction(ManagerRegistry $managerRegistry, Request $request): RedirectResponse|Response
    {
        $task = new Task();
        $user = $this->getUser();
        $task->setUser($user);

        $this->denyAccessUnlessGranted('create', $task);

        $form = $this->createForm(TaskType::class, $task, [
            'validation_groups' => ['create']
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $managerRegistry->getManager();

            $task->setUser($user);
            $entityManager->persist($task);
            $entityManager->flush();

            $this->addFlash('success', 'La tâche a bien été ajoutée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/tasks/{id}/edit', name: 'task_edit')]
    public function editAction(Task $task, Request $request, ManagerRegistry $managerRegistry,): RedirectResponse|Response
    {
        $this->denyAccessUnlessGranted('edit', $task);

        $form = $this->createForm(TaskType::class, $task, [
            'validation_groups' => ['edit']
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $managerRegistry->getManager()->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    #[Route('/tasks/{id}/toggle', name: 'task_toggle')]
    public function toggleTaskAction(Task $task, ManagerRegistry $managerRegistry): RedirectResponse
    {
        $this->denyAccessUnlessGranted('toggle', $task);

        $status = !$task->isDone() ? 'faite' : 'à faire';

        $task->toggle(!$task->isDone());
        $managerRegistry->getManager()->flush();

        $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme %s.', $task->getTitle(), $status));

        return $this->redirectToRoute('task_list');
    }

    #[Route('/tasks/{id}/delete', name: 'task_delete')]
    public function deleteTaskAction(Task $task, ManagerRegistry $managerRegistry): RedirectResponse
    {
        $this->denyAccessUnlessGranted('delete', $task);

        $entityManager = $managerRegistry->getManager();
        $entityManager->remove($task);
        $entityManager->flush();

        $this->addFlash('success', 'La tâche a bien été supprimée.');

        return $this->redirectToRoute('task_list');
    }
}
