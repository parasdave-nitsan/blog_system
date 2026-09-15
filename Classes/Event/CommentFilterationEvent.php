<?php

namespace Nitsan\BlogSystem\Event;

class CommentFilterationEvent
{
    protected string $commentData;

    public function __construct(string $commentData)
    {
        $this->commentData = $commentData;
    }

    public function getCommentData(): string
    {
        return $this->commentData;
    }

    public function setCommentData(string $commentData): void
    {
        $this->commentData = $commentData;
    }
}
