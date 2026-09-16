<?php

declare(strict_types=1);

namespace Nitsan\BlogSystem\EventListener;

use TYPO3\CMS\Extbase\Event\Persistence\EntityAddedToPersistenceEvent;
use Nitsan\BlogSystem\Domain\Model\Blog;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use Nitsan\BlogSystem\Domain\Repository\BlogRepository;


#[AsEventListener(
    identifier: 'blog-system/generate-slug-on-blog-add',
)]
final class GenerateSlugOnBlogAddListener
{
    public function __construct(
        private readonly BlogRepository $blogRepository,
    ) {
    }

    public function __invoke(EntityAddedToPersistenceEvent $event): void
    {
        $entity = $event->getObject();

        if (!$entity instanceof Blog) {
            return;
        }

        if (empty($entity->getSlug())) {
            $entity->setSlug($this->generateSlug($entity->getTitle()));
        }
    }

    private function generateSlug(string $title): string
    {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        $originalSlug = $slug;

        while ($this->blogRepository->slugExists($slug)) {
            $slug = $originalSlug . '-' . substr(bin2hex(random_bytes(3)), 0, 5);
        }

        return $slug;
    }

}