<?php

namespace Nitsan\BlogSystem\EventListener;

use Nitsan\BlogSystem\Event\CommentFilterationEvent;

class CommentFilterationListener
{
    public function __invoke(CommentFilterationEvent $event): void
    {
        $blockedWords = ['spam', 'badword', 'fake'];

        $comment = $event->getCommentData();

        foreach ($blockedWords as $word) {
            $comment = str_ireplace($word, '', $comment);
        }

        $event->setCommentData($comment);
    }
}
