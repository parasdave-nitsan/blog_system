<?php

declare(strict_types=1);

namespace Nitsan\BlogSystem\Controller;

use Nitsan\BlogSystem\Domain\Repository\BlogRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use Nitsan\BlogSystem\Domain\Model\Blog;

#[AsController]
final class ReportController extends ActionController
{
    public function __construct(
        private readonly ModuleTemplateFactory $moduleTemplateFactory,
        private readonly BlogRepository $blogRepository
    ) {
    }

    public function indexAction(): ResponseInterface
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);

        $totalBlogs = $this->blogRepository->countAll();
        $draftBlogs = $this->blogRepository->findDrafts();

        $moduleTemplate->assignMultiple([
            'totalBlogs' => $totalBlogs,
            'draftBlogs' => $draftBlogs,
        ]);

        return $moduleTemplate->renderResponse('Report/Index');
    }

    public function showAction(Blog $blog)
    {
        $moduleTemplate = $this->moduleTemplateFactory->create($this->request);
        $moduleTemplate->assignMultiple([
            'blog' => $blog,
        ]);

        return $moduleTemplate->renderResponse('Report/Show');
    }

    public function publishedAction(Blog $blog)
    {
        $blog->setPublishStatus('published');
        $this->blogRepository->update($blog);
        return $this->redirect('index');
    }
}
