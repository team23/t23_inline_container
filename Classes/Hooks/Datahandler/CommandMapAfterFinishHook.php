<?php

declare(strict_types=1);

namespace Team23\T23InlineContainer\Hooks\Datahandler;

/*
 * This file is part of TYPO3 CMS-based extension "t23_inline_container" by TEAM23.

 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *
 */

use B13\Container\Domain\Factory\ContainerFactory;
use B13\Container\Hooks\Datahandler\Database;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;


class CommandMapAfterFinishHook
{
    protected Database $database;
    protected ContainerFactory $containerFactory;
    protected Registry $registry;

    public function __construct(Database $database, ContainerFactory $containerFactory, Registry $registry)
    {
        $this->database = $database;
        $this->containerFactory = $containerFactory;
        $this->registry = $registry;
    }

    /**
     *
     * extracted sorting-fix from namespace B13\Container\Integrity\Sorting
     */
    public function processCmdmap_afterFinish(DataHandler $dataHandler): void
    {
        $cmdmap = $dataHandler->cmdmap;
        $copyMappingArray_merged = $dataHandler->copyMappingArray_merged;

        foreach ($cmdmap as $table => $incomingCmdArrayPerId) {
            if ($table !== 'tt_content') {
                continue;
            }
            foreach ($incomingCmdArrayPerId as $id => $incomingCmdArray) {
                if (!is_array($incomingCmdArray)) {
                    continue;
                }
                if (empty($copyMappingArray_merged['tt_content'][$id])) {
                    continue;
                }
                $newId = $copyMappingArray_merged['tt_content'][$id];
                $containerRecord = $this->database->fetchOneRecord($newId);
                if ($containerRecord['tx_container_parent'] > 0) {
                    continue;
                }
                $localDataHandler = GeneralUtility::makeInstance(DataHandler::class);
                $container = $this->containerFactory->buildContainer($containerRecord['uid']);
                $colPosList = $this->registry->getAllAvailableColumnsColPos($containerRecord['CType']);
                $prevChild = null;
                foreach ($colPosList as $colPos) {
                    $children = $container->getChildrenByColPos($colPos);
                    if (empty($children)) {
                        continue;
                    }
                    foreach ($children as $child) {
                        if ($prevChild === null) {
                            $cmdmap = [
                                'tt_content' => [
                                    $child['uid'] => [
                                        'move' => [
                                            'action' => 'paste',
                                            'target' => $container->getPid(),
                                            'update' => [
                                                'colPos' => $container->getUid() . '-' . $child['colPos'],
                                                'sys_language_uid' => $containerRecord['sys_language_uid'],

                                            ],
                                        ],
                                    ],
                                ],
                            ];
                            $localDataHandler->start([], $cmdmap);
                            $localDataHandler->process_datamap();
                            $localDataHandler->process_cmdmap();
                        } else {
                            $cmdmap = [
                                'tt_content' => [
                                    $child['uid'] => [
                                        'move' => [
                                            'action' => 'paste',
                                            'target' => -$prevChild['uid'],
                                            'update' => [
                                                'colPos' => $container->getUid() . '-' . $child['colPos'],
                                                'sys_language_uid' => $containerRecord['sys_language_uid'],

                                            ],
                                        ],
                                    ],
                                ],
                            ];
                            $localDataHandler->start([], $cmdmap);
                            $localDataHandler->process_datamap();
                            $localDataHandler->process_cmdmap();
                        }
                        $prevChild = $child;
                    }
                }
            }
        }
    }
}
