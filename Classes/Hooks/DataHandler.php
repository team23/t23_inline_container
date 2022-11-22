<?php

namespace Team23\T23InlineContainer\Hooks;

/*
 * This file is part of TYPO3 CMS-based extension "t23_inline_container" by TEAM23.

 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

use TYPO3\CMS\Core\SingletonInterface;

class DataHandler implements SingletonInterface
{

    public function processCmdmap_preProcess($command, $table, $id, $value, $pObj, $pasteUpdate)
    {
        if (in_array($command, ['copy', 'localize']) && $table === 'tt_content') {
            $GLOBALS['TCA']['tt_content']['columns']['tx_t23inlinecontainer_elements']['config']['type'] = 'none';
        }
    }

    public function processCmdmap_postProcess($command, $table, $id, $value, $pObj, $pasteUpdate, $pasteDatamap)
    {
        if (in_array($command, ['copy', 'localize']) && $table === 'tx_extension_table') {
            $GLOBALS['TCA']['tt_content']['columns']['tx_t23inlinecontainer_elements']['config']['type'] = 'tx_t23inlinecontainer_elements';
        }
    }
}