<?php
defined('TYPO3_MODE') || die();


$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/index_ts.php']['preprocessRequest']['Redirect40x'] = 'Cobweb\Redirect40x\Hooks\PageNotFoundHandler->hookPageNotFoundHandlers';
