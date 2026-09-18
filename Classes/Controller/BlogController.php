<?php

declare(strict_types=1);

namespace Nitsan\BlogSystem\Controller;

use Nitsan\BlogSystem\Domain\Model\Blog;
use Nitsan\BlogSystem\Domain\Repository\BlogRepository;
use Nitsan\BlogSystem\Domain\Model\Comment;
use Nitsan\BlogSystem\Domain\Repository\CommentRepository;
use Nitsan\BlogSystem\Domain\Repository\CategoryRepository;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Messaging\AbstractMessage;
use TYPO3\CMS\Core\Resource\Enum\DuplicationBehavior;
use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Annotation\IgnoreValidation;
use TYPO3\CMS\Extbase\Domain\Model\FileReference as ExtbaseFileReference;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Property\TypeConverter\DateTimeConverter;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use TYPO3\CMS\Core\Resource\Security\FileNameValidator;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use Nitsan\BlogSystem\Event\CommentFilterationEvent;
use TYPO3\CMS\Core\Resource\StorageRepository;


class BlogController extends ActionController
{
    public function __construct(
        private readonly BlogRepository $blogRepository,
        private readonly CommentRepository $commentRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly ResourceFactory $resourceFactory,
        private readonly PersistenceManagerInterface $persistenceManager
    ) {
    }

    public function listAction(
        int $currentPage = 1,
        int $category = 0,
        string $sortBy = 'publishDate',
        string $direction = 'DESC'
    ): ResponseInterface {
        $limit = (int) ($this->settings['limit'] ?? 10);

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

        $event = $this->eventDispatcher->dispatch(
            new CommentFilterationEvent($content)
        );

        $content = $event->getCommentData();

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

    public function searchAction(string $search = ''): ResponseInterface
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

        $this->view->assign('blog', $blog);
        $this->view->assign('categories', $this->categoryRepository->findAll());

        return $this->htmlResponse();
    }

    public function initializeCreateAction(): void
    {
        $this->arguments->getArgument('blog')
            ->getPropertyMappingConfiguration()
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
            $this->addFlashMessage('Blog data is missing. Please try again.');
            return $this->redirect('new');
        }

        $uploadedFile = $_FILES['thumbnailUpload'] ?? null;

        if ($uploadedFile && $uploadedFile['error'] !== \UPLOAD_ERR_NO_FILE) {
            $fileReference = $this->handleThumbnailUpload($uploadedFile);
            if ($fileReference !== null) {
                $blog->setThumbnail($fileReference);
            }
        }

        $blog->setPublishStatus('draft');

        $this->blogRepository->add($blog);
        $this->addFlashMessage('Blog created successfully.');

        return $this->redirect('list');
    }

    #[IgnoreValidation(['argumentName' => 'blog'])]
    public function editAction(Blog $blog): ResponseInterface
    {
        $this->view->assignMultiple([
            'blog' => $blog,
            'categories' => $this->categoryRepository->findAll(),
        ]);
        return $this->htmlResponse();
    }

    public function initializeUpdateAction(): void
    {
        $this->arguments->getArgument('blog')
            ->getPropertyMappingConfiguration()
            ->forProperty('publishDate')
            ->setTypeConverterOption(
                DateTimeConverter::class,
                DateTimeConverter::CONFIGURATION_DATE_FORMAT,
                'Y-m-d\TH:i'
            );
    }

    public function updateAction(Blog $blog): ResponseInterface
    {
        $uploadedFile = $_FILES['thumbnailUpload'] ?? null;

        if ($uploadedFile && $uploadedFile['error'] !== \UPLOAD_ERR_NO_FILE) {
            $newFileReference = $this->handleThumbnailUpload($uploadedFile);

            if ($newFileReference !== null) {
                $oldThumbnail = $blog->getThumbnail();

                $blog->setThumbnail($newFileReference);

                if ($oldThumbnail !== null) {
                    $this->persistenceManager->remove($oldThumbnail);
                }
            }
        }

        $blog->setPublishStatus('draft');

        $this->blogRepository->update($blog);
        $this->addFlashMessage('Blog updated successfully.');

        return $this->redirect('list');
    }

    protected function handleThumbnailUpload(array $uploadedFile): ?ExtbaseFileReference
    {
        if ($uploadedFile['error'] !== \UPLOAD_ERR_OK) {
            $this->addFlashMessage('The file upload failed (error code ' . $uploadedFile['error'] . ').', '', AbstractMessage::ERROR);
            return null;
        }

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($uploadedFile['type'], $allowedMimeTypes, true)) {
            $this->addFlashMessage('Only JPEG, PNG or WEBP images are allowed.', '', AbstractMessage::ERROR);
            return null;
        }

        $maxFileSize = 2 * 1024 * 1024; // 2MB
        if ($uploadedFile['size'] > $maxFileSize) {
            $this->addFlashMessage('The thumbnail must not exceed 2MB.', '', AbstractMessage::ERROR);
            return null;
        }

        if (!GeneralUtility::makeInstance(FileNameValidator::class)->isValid($uploadedFile['name'])) {
            $this->addFlashMessage('This file type is not allowed.', '', AbstractMessage::ERROR);
            return null;
        }

        $storage = GeneralUtility::makeInstance(StorageRepository::class)->findByUid(1);

        if ($storage->hasFolder('user_upload/blog/')) {
            $uploadFolder = $storage->getFolder('user_upload/blog/');
        } else {
            $uploadFolder = $storage->createFolder('user_upload/blog/');
        }

        $falFile = $uploadFolder->addUploadedFile($uploadedFile, DuplicationBehavior::RENAME);

        $falFileReference = $this->resourceFactory->createFileReferenceObject([
            'uid_local' => $falFile->getUid(),
            'uid_foreign' => uniqid('NEW_'),
            'uid' => uniqid('NEW_'),
            'crop' => null,
        ]);

        $fileReference = GeneralUtility::makeInstance(ExtbaseFileReference::class);
        $fileReference->setOriginalResource($falFileReference);

        return $fileReference;
    }

    public function deleteAction(Blog $blog): ResponseInterface
    {
        $this->blogRepository->remove($blog);
        $this->addFlashMessage('Blog deleted successfully.');
        return $this->redirect('list');
    }



}