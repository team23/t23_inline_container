<?php

namespace Team23\T23InlineContainer\Hooks;

/*
 * This file is part of TYPO3 CMS-based extension "t23_inline_container" by TEAM23.

 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use B13\Container\Domain\Factory\ContainerFactory;
use B13\Container\Integrity\Database;
use B13\Container\Tca\Registry;
use Team23\T23InlineContainer\Integrity\Sorting;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DataHandler implements SingletonInterface
{
    /**
     * @var array<int, array>
     */
    private $postProcessContainerRecordList = [];

    /**
     * @param $status
     * @param $table
     * @param $id
     * @param $fieldArray
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $pObj
     * @return void
     */
    public function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, \TYPO3\CMS\Core\DataHandling\DataHandler &$pObj)
    {
        /**
         * fix sorting of container inline elements
         */
        if (
            $table === 'tt_content' &&
            ($status === 'update' || $status === 'new') &&
            (int)$pObj->checkValue_currentRecord['tx_t23inlinecontainer_elements'] > 0 &&
            (0 < $containerUid = (int)$pObj->checkValue_currentRecord['uid'])
        ) {
            $this->postProcessContainerRecordList[$containerUid] = $pObj->checkValue_currentRecord;
        }
    }

    public function processCmdmap_preProcess($command, $table, $id, $value, $pObj, $pasteUpdate)
    {
        if (in_array($command, ['copy', 'localize']) && $table === 'tt_content') {
            $GLOBALS['TCA']['tt_content']['columns']['tx_t23inlinecontainer_elements']['config']['type'] = 'none';
        }
    }

    public function processCmdmap_postProcess($command, $table, $id, $value, $pObj, $pasteUpdate, $pasteDatamap)
    {
        if (in_array($command, ['copy', 'localize']) && $table === 'tt_content') {
            $GLOBALS['TCA']['tt_content']['columns']['tx_t23inlinecontainer_elements']['config']['type'] = 'tx_t23inlinecontainer_elements';
        }
    }

    /**
     * Fix container inline elements sorting after everything else has been processes
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
     * @return void
     */
    public function processDatamap_afterAllOperations(\TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler)
    {
        // Make sure that container sorting is only update once per container element
        // => Only run sorting update after all operations have been finished
        if (!empty($this->postProcessContainerRecordList) && $dataHandler->isOuterMostInstance()) {
            $database = GeneralUtility::makeInstance(Database::class);
            $registry = GeneralUtility::makeInstance(Registry::class);
            $containerFactory = GeneralUtility::makeInstance(ContainerFactory::class);
            $sorting = GeneralUtility::makeInstance(Sorting::class, $database, $registry, $containerFactory);
            foreach ($this->postProcessContainerRecordList as $containerRecord) {
                $cType = $containerRecord['CType'];
                $sorting->runForSingleContainer($containerRecord, $cType);
            }
        }
    }
}
