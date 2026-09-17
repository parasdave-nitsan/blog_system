<?php

namespace Nitsan\BlogSystem\Indexer;

use PDO;
use Tpwd\KeSearch\Domain\Repository\IndexRepository;
use Tpwd\KeSearch\Indexer\IndexerBase;
use Tpwd\KeSearch\Indexer\IndexerRunner;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BlogTableIndexer extends IndexerBase
{
    // Key for this indexer configuration. Also add it to
    // Configuration/TCA/Overrides/tx_kesearch_indexerconfig.php if you use a static items list.
    const KEY = 'tx_blogsystem_domain_model_blog';

    // The database table you want to index.
    const TABLE = 'tx_blogsystem_domain_model_blog';

    /**
     * Adds this custom indexer to the TCA of indexer configurations, so it becomes
     * selectable in the backend when creating a new "Indexer configuration" record.
     *
     * @param array $params
     * @param object $pObj
     */
    public function registerIndexerConfiguration(&$params, $pObj)
    {
        $customIndexer = [
            'Blog Table Indexer',
            self::KEY,
            'EXT:blog_system/Resources/Public/Icons/blog-system-blog.gif',
        ];
        $params['items'][] = $customIndexer;
    }

    /**
     * Main custom indexer function for ke_search. Called by IndexerRunner for
     * every indexer configuration record of type self::KEY.
     *
     * @param array $indexerConfig Configuration from TYPO3 Backend.
     * @param IndexerRunner $indexerObject Reference to indexer class.
     * @return string Message containing indexed elements.
     */
    public function customIndexer(array $indexerConfig, IndexerRunner $indexerObject): string
    {
        if ($indexerConfig['type'] != self::KEY) {
            return '';
        }

        if (class_exists('\Tpwd\KeSearch\Service\IndexerStatusService')) {
            $indexerStatusService = GeneralUtility::makeInstance(\Tpwd\KeSearch\Service\IndexerStatusService::class);
        }
        
        if (empty($indexerConfig['sysfolder'])) {
            throw new \Exception('No folder specified. Please set the folder which should be indexed in the indexer configuration!');
        }

        /** @var Connection $connection */
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable(self::TABLE);
        $queryBuilder = $connection->createQueryBuilder();

        // Don't fetch hidden/deleted records, but keep frontend user group
        // and start/stop time restrictions so they get copied into the index.
        $queryBuilder
            ->getRestrictions()
            ->removeAll()
            ->add(GeneralUtility::makeInstance(DeletedRestriction::class))
            ->add(GeneralUtility::makeInstance(HiddenRestriction::class));

        $where = [];
        $folders = GeneralUtility::trimExplode(',', htmlentities($indexerConfig['sysfolder']), true);
        $where[] = $queryBuilder->expr()->in('pid', $folders);

        // Incremental mode: only fetch records modified since last run
        if ($this->indexingMode == self::INDEXING_MODE_INCREMENTAL) {
            $where[] = $queryBuilder->expr()->gte('tstamp', $this->lastRunStartTime);
        }

        $query = $queryBuilder
            ->select('*')
            ->from(self::TABLE)
            ->where(...$where)
            ->executeQuery();

        $counter = 0;
        $totalCount = $query->rowCount();

        while ($record = $query->fetchAssociative()) {
            if (isset($indexerStatusService)) {
                $indexerStatusService->setRunningStatus($indexerConfig, $counter, $totalCount);
            }

            // --- Map your blog table fields here ---
            $title    = strip_tags($record['title'] ?? '');
            $abstract = strip_tags($record['slug'] ?? '');
            $content  = strip_tags($record['description'] ?? '');

            $fullContent = $title . "\n" . $abstract . "\n" . $content;

            // Link to single view. Adjust to your blog detail plugin/controller.
            $params = '&tx_blogsystem_pi1[blog]=' . $record['uid']
                . '&tx_blogsystem_pi1[controller]=Blog&tx_blogsystem_pi1[action]=detail';

            // Optional facet tags, e.g. by category
            $tags = '';

            $additionalFields = [
                'orig_uid' => $record['uid'],
                'orig_pid' => $record['pid'],
                'sortdate' => $record['datetime'] ?? $record['tstamp'] ?? 0,
            ];

            $indexerObject->storeInIndex(
                $indexerConfig['storagepid'],   // storage PID for the index entry
                $title,                         // record title
                self::KEY,                      // content type
                $indexerConfig['targetpid'],    // target PID: where is the single view page?
                $fullContent,                   // indexed fulltext (includes title)
                $tags,                          // tags for faceted search
                $params,                        // typolink params for single view
                $abstract,                      // abstract, shown in result list if not empty
                $record['sys_language_uid'] ?? 0, // language uid
                $record['starttime'] ?? 0,
                $record['endtime'] ?? 0,
                $record['fe_group'] ?? '',
                false,                           // debug only?
                $additionalFields
            );

            $counter++;
        }

        return $counter . ' blog records have been indexed.';
    }

    /**
     * Enables incremental indexing for this indexer.
     *
     * @param array $indexerConfig
     * @param IndexerRunner $indexerObject
     * @return string
     */
    public function startIncrementalIndexing(array $indexerConfig, IndexerRunner $indexerObject): string
    {
        if ($indexerConfig['type'] != self::KEY) {
            return '';
        }

        $this->indexingMode = self::INDEXING_MODE_INCREMENTAL;
        $content = $this->customIndexer($indexerConfig, $indexerObject);
        $content .= $this->removeDeleted($indexerConfig, self::TABLE);

        return $content;
    }

    /**
     * Removes index entries for blog records deleted/hidden since the last run.
     * Only needed for incremental indexing (full indexing has its own cleanup step).
     *
     * @param array $indexerConfig
     * @param string $tableName
     * @return string
     */
    public function removeDeleted(array $indexerConfig, string $tableName): string
    {
        /** @var IndexRepository $indexRepository */
        $indexRepository = GeneralUtility::makeInstance(IndexRepository::class);

        $folders = $this->getPagelist(
            $indexerConfig['startingpoints_recursive'],
            $indexerConfig['sysfolder']
        );

        $records = $this->findAllDeletedAndHiddenByPidListAndTimestampInAllLanguages(
            $tableName,
            $folders,
            $this->lastRunStartTime
        );

        $count = $indexRepository->deleteCorrespondingIndexRecords(self::KEY, $records, $indexerConfig);

        return chr(10) . 'Found ' . $count . ' deleted or hidden blog record(s).';
    }

    /**
     * @param string $tableName
     * @param array $pidList
     * @param int $tstamp
     * @return mixed[]
     */
    public function findAllDeletedAndHiddenByPidListAndTimestampInAllLanguages(
        string $tableName,
        array $pidList,
        int $tstamp
    ) {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable($tableName);
        $queryBuilder->getRestrictions()->removeAll();

        return $queryBuilder
            ->select('*')
            ->from($tableName)
            ->orWhere(
                $queryBuilder->expr()->eq('deleted', 1),
                $queryBuilder->expr()->eq('hidden', 1)
            )
            ->andWhere(
                $queryBuilder->expr()->in('pid', $queryBuilder->createNamedParameter($pidList, Connection::PARAM_INT_ARRAY)),
                $queryBuilder->expr()->gte('tstamp', $queryBuilder->createNamedParameter($tstamp, PDO::PARAM_INT))
            )
            ->executeQuery()
            ->fetchAllAssociative();
    }
}