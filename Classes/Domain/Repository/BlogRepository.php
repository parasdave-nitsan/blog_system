<?php

declare(strict_types=1);

namespace Nitsan\BlogSystem\Domain\Repository;

use Nitsan\BlogSystem\Domain\Model\Blog;
use TYPO3\CMS\Extbase\Persistence\Repository;

class BlogRepository extends Repository
{
    /**
     * @var class-string<Blog>
     */
    protected $objectType = Blog::class;

    public function findFiltered(int $category, string $sortBy, string $direction)
    {
        $query = $this->createQuery();

        if ($category > 0) {
            $query->matching($query->equals('category', $category));
        }

        $sortBy = in_array($sortBy, ['publishDate', 'views'], true) ? $sortBy : 'publishDate';
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        $query->setOrderings([$sortBy => $direction]);

        return $query->execute();
    }


    public function search(string $search): array
    {
        $search = trim($search);
        
        if ($search === '') {
            return [];
        }

        $query = $this->createQuery();

        $querySettings = $query->getQuerySettings();
        $querySettings->setRespectStoragePage(false);

        $constraints = [
            $query->like('title', '%' . $search . '%')
        ];

        $query->matching(
            $query->logicalOr(...$constraints)
        );

        return $query->execute()->toArray();
    }
}
