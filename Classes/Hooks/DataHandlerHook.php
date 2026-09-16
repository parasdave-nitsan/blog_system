<?php

namespace Nitsan\BlogSystem\Hooks;

use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DataHandlerHook
{
    private LoggerInterface $logger;

    public function __construct()
    {
        $this->logger = GeneralUtility::makeInstance(LogManager::class)
            ->getLogger(__CLASS__);
    }

    public function prepareCacheFlush($table, $uid)
    {
      $this->logger->info("Hook called", ["Table"=> $table,"uid"=> $uid]);
    }
}