<?php

namespace Nitsan\BlogSystem\EventListener;

use Nitsan\BlogSystem\Controller\BlogController;
use TYPO3\CMS\Extbase\Event\Mvc\BeforeActionCallEvent;

class BeforeActionCallListener
{
    public function __invoke(BeforeActionCallEvent $event): void
    {
        if (!$event->getControllerClassName() instanceof BlogController) {
            return;
        }

        if (!in_array($event->getActionMethodName(), ['createAction', 'updateAction'], true)) {
            return;
        }

        $blog = $event->getPreparedArguments()->getArgument('blog')->getValue();

        if (!$blog) {
            return;
        }

        // Filter title and description
        foreach (['title', 'description'] as $property) {
            $value = $blog->{'get' . ucfirst($property)}();

            foreach (['spam', 'badword', 'fake'] as $word) {
                $value = str_ireplace($word, '', $value);
            }

            $blog->{'set' . ucfirst($property)}(trim($value));
        }

        // Future date → today
        $publishDate = $blog->getPublishDate();

        if ($publishDate && $publishDate > new \DateTime()) {
            $blog->setPublishDate(new \DateTime());
        }
    }
}
