<?php

declare(strict_types=1);

namespace Nitsan\BlogSystem\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Core\Resource\Enum\DuplicationBehavior;
use TYPO3\CMS\Extbase\Annotation\FileUpload;
use TYPO3\CMS\Extbase\Domain\Model\FileReference;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use Nitsan\BlogSystem\Domain\Model\Category;

class Blog extends AbstractEntity
{
    protected string $title = '';

    protected string $description = '';

    protected string $author = '';

    protected int $category = 0;    

    protected ?\DateTime $publishDate = null;

    protected ObjectStorage $comments;

    // #[FileUpload([
    //     'validation' => [
    //         'required' => false,
    //         'maxFiles' => 1,
    //         'fileSize' => [
    //             'minimum' => '0K',
    //             'maximum' => '2M',
    //         ],
    //         'mimeType' => [
    //             'allowedMimeTypes' => [
    //                 'image/jpeg',
    //                 'image/png',
    //                 'image/webp',
    //             ],
    //         ],
    //         'imageDimensions' => ['maxWidth' => 4096, 'maxHeight' => 4096]
    //     ],
    //     'uploadFolder' => '1:/user_upload/blog/',
    //     'addRandomSuffix' => false,
    //     'duplicationBehavior' => DuplicationBehavior::RENAME,
    // ])]
    protected ?FileReference $thumbnail = null;


    protected int $views = 0;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author): void
    {
        $this->author = $author;
    }

    public function getPublishDate(): ?\DateTime
    {
        return $this->publishDate;
    }

    public function setPublishDate(?\DateTime $publishDate): void
    {
        $this->publishDate = $publishDate;
    }

    public function getThumbnail(): ?FileReference
    {
        return $this->thumbnail;
    }

    public function setThumbnail(?FileReference $thumbnail): void
    {
        $this->thumbnail = $thumbnail;
    }

    public function getViews(): int
    {
        return $this->views;
    }

    public function setViews(int $views): void
    {
        $this->views = $views;
    }

    public function getCategory(): int
    {
        return $this->category;
    }

    public function setCategory(int $category): void
    {
        $this->category = $category;
    }

    // Relationship with Comment model

    public function addComment(Comment $comment): void
    {
        $this->comments?->attach($comment);
    }

    public function removeComment(Comment $comment): void
    {
        $this->comments?->detach($comment);
    }

    public function getComments(): ObjectStorage
    {
        return $this->comments;
    }
}
