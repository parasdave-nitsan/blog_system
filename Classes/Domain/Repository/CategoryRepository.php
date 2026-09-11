<?php

declare(strict_types=1);

namespace Nitsan\BlogSystem\Domain\Repository;

use TYPO3\CMS\Extbase\Persistence\Repository;

class CategoryRepository extends Repository
{
    /**
     * @var class-string<Category>
     */
    protected $objectType = Category::class;
}