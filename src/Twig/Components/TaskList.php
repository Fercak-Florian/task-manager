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

    #[LiveProp(writable: true, url: true)]
    public string $keyWord = '';

    #[LiveProp(writable: false, url: true)]
    public TaskSortMode $sortMode = TaskSortMode::CREATED_ASC;

    public function __construct(private TaskRepository $taskRepository,
                                private Security $security,
                                private EntityManagerInterface $entityManager)
    {
    }

    #[LiveAction]
    public function sortByCreatedAt(): void {
        if ($this->sortMode === TaskSortMode::CREATED_ASC) {
            $direction = 'desc';
        } else {
            $direction = 'asc';
        }
        $this->updateSortMode('created', $direction);
    }

    #[LiveAction]
    public function sortByDeadline(): void {
        if ($this->sortMode === TaskSortMode::DEADLINE_ASC) {
            $direction = 'desc';
        } else {
            $direction = 'asc';
        }
        $this->updateSortMode('deadline', $direction);
    }

    #[LiveAction]
    public function sortByPriority(): void {
        if ($this->sortMode === TaskSortMode::PRIORITY_ASC) {
            $direction = 'desc';
        } else {
            $direction = 'asc';
        }
        $this->updateSortMode('priority', $direction);
    }

    private function toggleSortDirection(): string {
        // TO DO -> factoriser pour éviter la répétition des 3 if/else
        return "";
    }

    private function updateSortMode(string $criteriaName, string $direction): void {
        match ($criteriaName.'_'.$direction) {
            'created_asc' => $this->sortMode = TaskSortMode::CREATED_ASC,
            'created_desc' => $this->sortMode = TaskSortMode::CREATED_DESC,
            'deadline_asc' => $this->sortMode = TaskSortMode::DEADLINE_ASC,
            'deadline_desc' => $this->sortMode = TaskSortMode::DEADLINE_DESC,
            'priority_asc' => $this->sortMode = TaskSortMode::PRIORITY_ASC,
            'priority_desc' => $this->sortMode = TaskSortMode::PRIORITY_DESC,
        };
    }

    #[LiveAction]
    public function resetFilters(): void
    {
        $this->keyWord = '';
        $this->sortMode = TaskSortMode::CREATED_ASC;
    }

    public function getTasksByCriteria(): array
    {
        $currentUser = $this->security->getUser();
        if(!$currentUser instanceof User) {
            return [];
        }

        $taskSearchCriteria = new TaskSearchCriteria($this->keyWord, $this->sortMode, false);
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
