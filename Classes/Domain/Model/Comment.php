<?php

declare(strict_types=1);

namespace Nitsan\BlogSystem\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Comment extends AbstractEntity
{
    protected string $user = '';

    protected string $content = '';

    protected ?\DateTime $publishDate = null;

    protected ?Blog $blog = null;

    protected ?Comment $commentReply = null;

    public function getUser(): string
    {
        return $this->user;
    }

    public function setUser(string $user): void
    {
        $this->user = $user;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getPublishDate(): ?\DateTime
    {
        return $this->publishDate;
    }

    public function setPublishDate(?\DateTime $publishDate): void
    {
        $this->publishDate = $publishDate;
    }

    public function getBlog(): ?Blog
    {
        return $this->blog;
    }

    public function setBlog(?Blog $blog): void
    {
        $this->blog = $blog;
    }

    public function getCommentReply(): ?Comment
    {
        return $this->commentReply;
    }

    public function setCommentReply(?Comment $commentReply): void
    {
        $this->commentReply = $commentReply;
    }
}