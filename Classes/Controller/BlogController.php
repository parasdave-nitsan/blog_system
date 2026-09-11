<?php

declare(strict_types=1);

namespace Nitsan\BlogSystem\Controller;

use Nitsan\BlogSystem\Domain\Model\Blog;
use Nitsan\BlogSystem\Domain\Repository\BlogRepository;
use Nitsan\BlogSystem\Domain\Model\Comment;
use Nitsan\BlogSystem\Domain\Repository\CommentRepository;
use Nitsan\BlogSystem\Domain\Model\Category;
use Nitsan\BlogSystem\Domain\Repository\CategoryRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\IgnoreValidation;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Property\TypeConverter\DateTimeConverter;
use TYPO3\CMS\Extbase\Property\TypeConverter\PersistentObjectConverter;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;


class BlogController extends ActionController
{
    public function __construct(
        private readonly BlogRepository $blogRepository,
        private readonly CommentRepository $commentRepository,
        private readonly CategoryRepository $categoryRepository
    ) {}

    public function listAction(
        int $currentPage = 1,
        int $category = 0,
        string $sortBy = 'publishDate',
        string $direction = 'DESC'
    ): ResponseInterface
    {
        $limit = (int)($this->settings['limit'] ?? 10);

        if ($limit < 1) {
            $limit = 10;
        }

        $sortBy = in_array($sortBy, ['publishDate', 'views'], true) ? $sortBy : 'publishDate';
        $direction = strtoupper($direction) === 'ASC' ? 'ASC' : 'DESC';

        $blogs = $this->blogRepository->findFiltered($category, $sortBy, $direction);

        $currentPage = max(1, $currentPage);

        $paginator = new QueryResultPaginator(
            $blogs,
            $currentPage,
            $limit
        );

        $pagination = new SimplePagination($paginator);

        $this->view->assignMultiple([
            'blogs' => $paginator->getPaginatedItems(),
            'pagination' => $pagination,
            'paginator' => $paginator,
            'limit' => $limit,
            'currentPage' => $currentPage,
            'categories' => $this->categoryRepository->findAll(),
            'selectedCategory' => $category,
            'sortBy' => $sortBy,
            'direction' => $direction,
        ]);

        return $this->htmlResponse();
    }



    public function showAction(Blog $blog): ResponseInterface
    {

        $blog->setViews($blog->getViews() + 1);
        $this->blogRepository->update($blog);

        $this->view->assignMultiple([
            'blog' => $blog,
            'category' => $blog->getCategory() > 0
                ? $this->categoryRepository->findByUid($blog->getCategory())
                : null,
            'comments' => $this->commentRepository->findTopLevelForBlog($blog),
            'replies' => $this->commentRepository->findRepliesForBlog($blog),
        ]);

        return $this->htmlResponse();
    }

    public function createCommentAction(int $blog, string $user = '', string $content = '', int $parentComment = 0): ResponseInterface
    {
        $blogRecord = $this->blogRepository->findByUid($blog);
        $user = trim($user);
        $content = trim($content);

        if (!$blogRecord || $user === '' || $content === '') {
            $this->addFlashMessage('Please enter your name and a comment.');
            return $this->redirect('show', null, null, ['blog' => $blogRecord ?? $blog]);
        }

        $comment = new Comment();
        $comment->setBlog($blogRecord);
        $comment->setUser($user);
        $comment->setContent($content);
        $comment->setPublishDate(new \DateTime());

        if ($parentComment > 0) {
            $parent = $this->commentRepository->findByUid($parentComment);
            if ($parent && $parent->getBlog()?->getUid() === $blogRecord->getUid()) {
                $comment->setCommentReply($parent);
            }
        }

        $this->commentRepository->add($comment);
        $this->addFlashMessage('Comment added successfully.');

        return $this->redirect('show', null, null, ['blog' => $blogRecord]);
    }

    public function updateCommentAction(Comment $comment, string $user = '', string $content = ''): ResponseInterface
    {
        $user = trim($user);
        $content = trim($content);

        if ($user === '' || $content === '') {
            $this->addFlashMessage('Please enter your name and a comment.');
            return $this->redirect('show', null, null, ['blog' => $comment->getBlog()]);
        }

        $comment->setUser($user);
        $comment->setContent($content);
        $this->commentRepository->update($comment);

        $this->addFlashMessage('Comment updated successfully.');
        return $this->redirect('show', null, null, ['blog' => $comment->getBlog()]);
    }

    public function deleteCommentAction(int $comment): ResponseInterface
    {
        $commentRecord = $this->commentRepository->findByUid($comment);
        if ($commentRecord) {
            $blog = $commentRecord->getBlog();
            $this->commentRepository->remove($commentRecord);
            $this->addFlashMessage('Comment deleted successfully.');
            return $this->redirect('show', null, null, ['blog' => $blog]);
        }

        $this->addFlashMessage('Comment not found.');
        return $this->redirect('list');
    }

    public function searchAction(String $search = ''): ResponseInterface
    {
        $results = $this->blogRepository->search($search);

        $this->view->assignMultiple([
            'results' => $results,
            'search' => $search,
        ]);

        return $this->htmlResponse(); 
    }

    #[IgnoreValidation(['argumentName' => 'blog'])]
    public function newAction(?Blog $blog = null): ResponseInterface
    {
        if ($blog === null) {
            $blog = new Blog();
        }

        $categories = $this->categoryRepository->findAll();

        $this->view->assign('blog', $blog);
        $this->view->assign('categories', $categories);

        return $this->htmlResponse();
    }

    public function initializeCreateAction(): void
    {
        $propertyMappingConfiguration =
            $this->arguments->getArgument('blog')->getPropertyMappingConfiguration();

        $propertyMappingConfiguration
            ->forProperty('publishDate')
            ->setTypeConverterOption(
                DateTimeConverter::class,
                DateTimeConverter::CONFIGURATION_DATE_FORMAT,
                'Y-m-d\TH:i'
            );

    }

    public function createAction(?Blog $blog = null): ResponseInterface
    {
        if ($blog === null) {
            $this->addFlashMessage(
                'Blog data is missing. Please try again.'
            );

            return $this->redirect('new');
        }

        $this->blogRepository->add($blog);

        $this->addFlashMessage('Blog created successfully.');

        return $this->redirect('list');
    }

    public function deleteAction(Blog $blog): ResponseInterface
    {
        $this->blogRepository->remove($blog);
        $this->addFlashMessage('Blog deleted successfully.');
        return $this->redirect('list');
    }

    #[IgnoreValidation(['argumentName' => 'blog'])]
    public function editAction(Blog $blog): ResponseInterface
    {
        \TYPO3\CMS\Extbase\Utility\DebuggerUtility::var_dump($blog, __FILE__.''.__LINE__);
        $this->view->assignMultiple([
            'blog' => $blog,
            'categories' => $this->categoryRepository->findAll(),
        ]);
        return $this->htmlResponse();
    }

    public function initializeUpdateAction(): void
    {
        $propertyMappingConfiguration =
        $this->arguments->getArgument('blog')->getPropertyMappingConfiguration();
        
        $propertyMappingConfiguration
        ->forProperty('publishDate')
        ->setTypeConverterOption(
            DateTimeConverter::class,
            DateTimeConverter::CONFIGURATION_DATE_FORMAT,
            'Y-m-d\TH:i'
            );
    }

    public function updateAction(Blog $blog): ResponseInterface
    {
        $this->blogRepository->update($blog);
        $this->addFlashMessage('Blog updated successfully.');
        return $this->redirect('list');
    }
}