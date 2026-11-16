<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/task')]
#[IsGranted('ROLE_USER')]
final class TaskController extends AbstractController
{
    #[Route(name: 'app_task_index', methods: ['GET'])]
    public function index(TaskRepository $taskRepository, #[CurrentUser] User $currentUser): Response
    {
        $tasks = $taskRepository->findBy(['user' => $currentUser->getId()]);
        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    #[Route('/new', name: 'app_task_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, #[CurrentUser] User $currentUser): Response
    {
        $queryParams = $request->query->all();
        $keyWord = $queryParams['keyWord'] ?? '';
        $sortMode = $queryParams['sortMode'] ?? '';

        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setUser($currentUser);
            $task->setCreatedAt(new \DateTimeImmutable('now'));
            $entityManager->persist($task);
            $entityManager->flush();

            return $this->redirectToRoute('app_task_index', [
                'keyWord' => $keyWord,
                'sortMode' => $sortMode,
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/new.html.twig', [
            'task' => $task,
            'form' => $form,
            'keyWord' => $keyWord,
            'sortMode' => $sortMode,
        ]);
    }

    #[Route('/{id}', name: 'app_task_show', methods: ['GET'])]
    public function show(Task $task, Request $request): Response
    {
        $queryParams = $request->query->all();
        $keyWord = $queryParams['keyWord'] ?? '';
        $sortMode = $queryParams['sortMode'] ?? '';

        return $this->render('task/show.html.twig', [
            'task' => $task,
            'keyWord' => $keyWord,
            'sortMode' => $sortMode,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_task_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        $queryParams = $request->query->all();
        $keyWord = $queryParams['keyWord'] ?? '';
        $sortMode = $queryParams['sortMode'] ?? '';

        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_task_index', [
                'keyWord' => $keyWord,
                'sortMode' => $sortMode,
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'form' => $form,
            'keyWord' => $keyWord,
            'sortMode' => $sortMode,
        ]);
    }

    #[Route('/{id}', name: 'app_task_delete', methods: ['POST'])]
    public function delete(Request $request, Task $task, EntityManagerInterface $entityManager): Response
    {
        $queryParams = $request->query->all();
        $keyWord = $queryParams['keyWord'] ?? '';
        $sortMode = $queryParams['sortMode'] ?? '';

        if ($this->isCsrfTokenValid('delete'.$task->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($task);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_task_index', [
            'keyWord' => $keyWord,
            'sortMode' => $sortMode,
        ], Response::HTTP_SEE_OTHER);
    }
}
