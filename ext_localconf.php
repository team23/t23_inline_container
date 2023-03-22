<?php

defined('TYPO3') || die();

/*
 * This file is part of TYPO3 CMS-based extension "t23_inline_container" by TEAM23.

 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

$GLOBALS ['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass']['tx-t23inlinecontainer'] = 'Team23\T23InlineContainer\Hooks\DataHandler';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['tx-t23inlinecontainer'] = 'Team23\T23InlineContainer\Hooks\DataHandler';

$GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['formDataGroup']['tcaDatabaseRecord'][\Team23\T23InlineContainer\Backend\FormDataProvider\ContainerChildrenFormDataProvider::class] = [
    'depends' => [
        \TYPO3\CMS\Backend\Form\FormDataProvider\PageTsConfig::class,
    ],
    'before' => [
        \TYPO3\CMS\Backend\Form\FormDataProvider\InlineOverrideChildTca::class,
    ]
];