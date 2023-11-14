<?php

namespace Team23\T23InlineContainer\Helper;

/*
 * This file is part of TYPO3 CMS-based extension "t23_inline_container" by TEAM23.

 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use B13\Container\Domain\Factory\ContainerFactory;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ColPosHelper {

    /**
     * Function returns allowed colPos considering the maxItems in the col
     *
     * @param int $containerUid
     * @param int $childUid
     * @return array
     * @throws \B13\Container\Domain\Factory\Exception
     */
    public static function getAvailableColPos(int $containerUid, int $childUid = 0) : array {
        $containerRegistry = GeneralUtility::makeInstance(Registry::class);
        $containerFactory = GeneralUtility::makeInstance(ContainerFactory::class);
        $container = $containerFactory->buildContainer($containerUid);

        $availableColumns = $containerRegistry->getAvailableColumns($container->getCType());
        foreach ($availableColumns as $key => $columnConfiguration) {
            $children = $container->getChildrenByColPos($columnConfiguration['colPos']);
            if (($columnConfiguration['maxitems'] && count($container->getChildrenByColPos($columnConfiguration['colPos'])) >= $columnConfiguration['maxitems']) && !in_array($childUid, array_column($children, 'uid'))) {
                unset($availableColumns[$key]);
            }
        }

        return array_values($availableColumns);
    }

    /**
     * Function returns the number of allowed children in a container summed over all colPos
     *
     * @param string $CType
     * @return int
     */
    public static function getMaxItems(string $CType) : int {
        $maxitems = 0;
        $containerRegistry = GeneralUtility::makeInstance(\B13\Container\Tca\Registry::class);

        foreach ($containerRegistry->getAvailableColumns($CType) as $columnConfiguration) {
            // If one column has maxitems = 0 unlimited children can be added
            if ($columnConfiguration['colPos']) {
                $maxitems += $containerRegistry->getContentDefenderConfiguration($CType, $columnConfiguration['colPos'])['maxitems'];
            }
            else {
                return -1;
            }
        }

        return $maxitems;
    }
}