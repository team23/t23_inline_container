<?php

defined('TYPO3') || die('Access denied.');

call_user_func(static function () {

    $commandMapHooks = [
        'tx_t23inlinecontainer-after-finish' => \Team23\T23InlineContainer\Hooks\Datahandler\CommandMapAfterFinishHook::class,
    ];
     // set our hooks at the beginning of Datamap Hooks
     $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'] = array_merge(
        $commandMapHooks,
        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'] ?? []
    );
});
