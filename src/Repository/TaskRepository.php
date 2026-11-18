<?php

namespace App\Repository;

use App\Entity\Task;
use App\Entity\User;
use App\Enum\TaskPriority;
use App\Enum\TaskSortMode;
use App\Utils\TaskSearchCriteria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    /**
     * @return Task[] Returns an array of Task objects
     */
    public function findByCriteria(TaskSearchCriteria $taskSearchCriteria, User $currentUser): array
    {
        $keyWord = '%' . $taskSearchCriteria->getKeyWord() . '%';
        $isCompleted = $taskSearchCriteria->isCompleted();
        $sortMode = $taskSearchCriteria->getSortMode();
        $isLate = $taskSearchCriteria->isLate();

        if ($isLate) {
            $isCompleted = false;
        }

        $qb = $this->createQueryBuilder('t')
            ->andWhere('t.title LIKE :val')
            ->andWhere('t.user = :user')
            ->andWhere('t.completed = :completed')
            ->setParameter('val', $keyWord)
            ->setParameter('user', $currentUser)
            ->setParameter('completed', $isCompleted);


        if ($isLate) {
            $today = new \DateTimeImmutable()->setTime(0,0,0);
            $qb->setParameter('today', $today)
                ->andWhere('t.deadline IS NOT NULL')
                ->andWhere('t.deadline < :today');
        }

        $this->applySorting($qb, $sortMode);

        return $qb->getQuery()->getResult();
    }

    private function applySorting(QueryBuilder $qb, TaskSortMode $sortMode): void
    {
        if (in_array($sortMode, [
            TaskSortMode::PRIORITY_ASC,
            TaskSortMode::PRIORITY_DESC,
        ], true)) {
            $qb->addSelect("
        CASE
            WHEN t.priority = :high THEN 1
            WHEN t.priority = :normal THEN 2
            WHEN t.priority = :low THEN 3
            ELSE 4
        END AS HIDDEN priorityOrder
    ")
                ->setParameter('high', TaskPriority::HIGH->value)
                ->setParameter('normal', TaskPriority::NORMAL->value)
                ->setParameter('low', TaskPriority::LOW->value)
                ->orderBy('priorityOrder', $sortMode->getDirection());
        } else {
            $qb->orderBy('t.'.$sortMode->getField(), $sortMode->getDirection());
        }
    }
    public function findUserCompletedTasks(User $currentUser): array {
        return $this->createQueryBuilder('t')
            ->andWhere('t.user = :user')
            ->andWhere('t.completed = true')
            ->setParameter('user', $currentUser)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param User $user
     * @param int $taskId
     * @return Task|null
     */
    public function findOneByUserAndId(User $user, int $taskId): Task|null
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.user = :user')
            ->andWhere('t.id = :id')
            ->setParameter('user', $user)
            ->setParameter('id', $taskId)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
}
