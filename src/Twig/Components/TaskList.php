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
    public function sortByCreatedAt(): void {
        $this->createdAtDirection = $this->toggleSortDirection($this->createdAtDirection);
        $this->updateSortMode('created', $this->createdAtDirection);
    }

    #[LiveAction]
    public function sortByDeadline(): void {
        $this->deadlineDirection = $this->toggleSortDirection($this->deadlineDirection);
        $this->updateSortMode('deadline', $this->deadlineDirection);
    }

    #[LiveAction]
    public function sortByPriority(): void {
        $this->priorityDirection = $this->toggleSortDirection($this->priorityDirection);
        $this->updateSortMode('priority', $this->priorityDirection);
    }

    private function toggleSortDirection(string $direction): string {
        if ($direction === 'desc') {
            return 'asc';
        } else {
            return 'desc';
        }
    }

    private function updateSortMode(string $criteriaName, string $direction): void {
        $this->sortMode = $criteriaName.'_'.$direction;
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
