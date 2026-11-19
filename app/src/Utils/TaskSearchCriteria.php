<?php

namespace App\Utils;

use App\Enum\TaskSortMode;

class TaskSearchCriteria
{
    private string $keyWord;
    private TaskSortMode $sortMode;
    private bool $isCompleted = false;
    private bool $isLate;

    /**
     * @param string $keyWord
     * @param TaskSortMode $sortMode
     * @param bool $isCompleted
     */
    public function __construct(string $keyWord, TaskSortMode $sortMode, bool $isCompleted, bool $isLate)
    {
        $this->keyWord = $keyWord;
        $this->sortMode = $sortMode;
        $this->isCompleted = $isCompleted;
        $this->isLate = $isLate;
    }

    public function isCompleted(): bool
    {
        return $this->isCompleted;
    }

    public function setIsCompleted(bool $isCompleted): void
    {
        $this->isCompleted = $isCompleted;
    }

    public function getSortMode(): TaskSortMode
    {
        return $this->sortMode;
    }

    public function setSortMode(TaskSortMode $sortMode): void
    {
        $this->sortMode = $sortMode;
    }

    public function getKeyWord(): string
    {
        return $this->keyWord;
    }

    public function setKeyWord(string $keyWord): void
    {
        $this->keyWord = $keyWord;
    }
    public function isLate(): bool
    {
        return $this->isLate;
    }

    public function setIsLate(bool $isLate): void
    {
        $this->isLate = $isLate;
    }
}
