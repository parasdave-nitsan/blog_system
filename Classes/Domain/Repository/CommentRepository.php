<?php

declare(strict_types=1);

namespace Nitsan\BlogSystem\Domain\Repository;

use Nitsan\BlogSystem\Domain\Model\Comment;
use Nitsan\BlogSystem\Domain\Model\Blog;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

class CommentRepository extends Repository
{
    /**
     * @var class-string<Comment>
     */
    protected $objectType = Comment::class;

    public function findTopLevelForBlog(Blog $blog): array
    {
        return $this->findByBlogAndParent($blog, null);
    }

    public function findRepliesForBlog(Blog $blog): array
    {
        $query = $this->createQuery();
        $query->matching($query->logicalAnd(
            $query->equals('blog', $blog),
            $query->logicalNot($query->equals('commentReply', 0))
        ));
        $query->setOrderings(['publishDate' => QueryInterface::ORDER_ASCENDING]);

        return $query->execute()->toArray();
    }

    private function findByBlogAndParent(Blog $blog, ?Comment $parent): array
    {
        $query = $this->createQuery();
        $constraints = [$query->equals('blog', $blog)];
        $constraints[] = $parent === null
            ? $query->equals('commentReply', 0)
            : $query->equals('commentReply', $parent);
        $query->matching($query->logicalAnd(...$constraints));
        $query->setOrderings(['publishDate' => QueryInterface::ORDER_ASCENDING]);

        return $query->execute()->toArray();
    }
}