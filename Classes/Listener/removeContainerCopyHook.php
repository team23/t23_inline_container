<?php

declare(strict_types=1);

namespace Team23\T23InlineContainer\Listener;

/*
 * This file is part of TYPO3 CMS-based extension "t23_inline_container" by TEAM23.

 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *
 */

use TYPO3\CMS\Core\Core\Event\BootCompletedEvent;

class removeContainerCopyHook {
    public function __invoke(BootCompletedEvent $e): void
    {
        unset($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['tx_container-post-process']);
    }
}