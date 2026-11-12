<?php

namespace App\Twig\Components;

use App\Entity\User;
use App\Enum\TaskSortMode;
use App\Repository\TaskRepository;
use App\Utils\TaskSearchCriteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class TaskList extends AbstractController
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $keyWord = '';

    #[LiveProp]
    public string $sortMode = 'created_desc';

    #[LiveProp]
    public string $createdAtDirection = 'desc';

    #[LiveProp]
    public string $deadlineDirection = 'asc';

    #[LiveProp]
    public string $priorityDirection = 'desc';

    public function __construct(private TaskRepository $taskRepository,
                                private Security $security,
                                private EntityManagerInterface $entityManager)
    {
    }

    #[LiveAction]
    public function sortByCreatedAt() {
        // inverser le sens
        if ($this->createdAtDirection === 'desc') {
            $this->createdAtDirection = 'asc';
            $this->sortMode = 'created_asc';
        } else {
            $this->createdAtDirection = 'desc';
            $this->sortMode = 'created_desc';
        }
    }

    #[LiveAction]
    public function sortByDeadline() {
        // inverser le sens
        if ($this->deadlineDirection === 'desc') {
            $this->deadlineDirection = 'asc';
            $this->sortMode = 'deadline_asc';
        } else {
            $this->deadlineDirection = 'desc';
            $this->sortMode = 'deadline_desc';
        }
    }

    #[LiveAction]
    public function sortByPriority() {
        // inverser le sens
        if ($this->priorityDirection === 'desc') {
            $this->priorityDirection = 'asc';
            $this->sortMode = 'a definir';
        } else {
            $this->priorityDirection = 'desc';
            $this->sortMode = 'a definir';
        }
    }

    public function getTasksByCriteria(): array
    {
        $currentUser = $this->security->getUser();
        if(!$currentUser instanceof User) {
            return [];
        }

        $taskSortMode = TaskSortMode::tryFrom($this->sortMode) ?? TaskSortMode::CREATED_DESC;

        $taskSearchCriteria = new TaskSearchCriteria($this->keyWord, $taskSortMode, false);
        return $this->taskRepository->findByCriteria($taskSearchCriteria, $currentUser);
    }

    public function getCompletedTasks(): array
    {
        $currentUser = $this->security->getUser();
        if(!$currentUser instanceof User) {
            return [];
        }
        return $this->taskRepository->findUserCompletedTasks($currentUser);
    }

    #[LiveAction]
    public function toggleCompletedStatus(#[LiveArg] int $id): void
    {
        $currentUser = $this->security->getUser();
        if (!$currentUser instanceof User) {
            $this->addFlash('error', 'Vous devez être connecté.');
            return;
        }

        $task = $this->taskRepository->findOneByUserAndId($currentUser, $id);
        if($task === null) {
            $this->addFlash('error', 'Tâche introuvable.');
            return;
        }

        $task->toggleStatus();
        $this->entityManager->flush();

        $this->addFlash('success', $task->isCompleted() ? 'Tâche complétée !' : 'Tâche rouverte.');
    }
}
