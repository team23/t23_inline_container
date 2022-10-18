<?php

declare(strict_types=1);

namespace Team23\T23InlineContainer\Tca;

/*
 * This file is part of TYPO3 CMS-based extension "t23_inline_container" by TEAM23.

 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use B13\Container\Domain\Factory\ContainerFactory;
use B13\Container\Domain\Factory\Exception;
use B13\Container\Tca\Registry;
use TYPO3\CMS\Backend\View\BackendLayoutView;

class ItemProcFunc {
    /**
     * @var ContainerFactory
     */
    protected $containerFactory;

    /**
     * @var BackendLayoutView
     */
    protected $backendLayoutView;

    /**
     * @var Registry
     */
    protected $tcaRegistry;

    public function __construct(ContainerFactory $containerFactory, Registry $tcaRegistry, BackendLayoutView $backendLayoutView)
    {
        $this->containerFactory = $containerFactory;
        $this->tcaRegistry = $tcaRegistry;
        $this->backendLayoutView = $backendLayoutView;
    }

    public function colPos(array &$parameters): void
    {
        $row = $parameters['row'];
        if ($row['tx_container_parent'] > 0) {
            try {
                $container = $this->containerFactory->buildContainer((int)$row['tx_container_parent']);
                $cType = $container->getCType();
                $grid = $this->tcaRegistry->getGrid($cType);
                if (is_array($grid)) {
                    $items = [];
                    foreach ($grid as $rows) {
                        foreach ($rows as $column) {
                            $items[] = [
                                $column['name'],
                                $column['colPos'],
                            ];
                        }
                    }
                    $parameters['items'] = $items;
                    return;
                }
            } catch (Exception $e) {
            }
        }
    }
}