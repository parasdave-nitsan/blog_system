<?php

declare(strict_types=1);

namespace Nitsan\BlogSystem\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class Category extends AbstractEntity
{
    protected string $name = '';

    protected ObjectStorage $blogs;

    public function getName(): string
    {
         return $this->name;
    }

    public function getBlogs(): ObjectStorage
    {
        return $this->blogs;
    }

}