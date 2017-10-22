<?php

namespace Cobweb\Redirect40x\Hooks;

/**
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

class PageNotFoundHandler
{
    /**
     * Replaces the current handling configuration
     * and keeps it for
     */
    public function hookPageNotFoundHandlers()
    {
        // Forces the 404 redirection to be performed by this extension
        $GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFound_handling_hooked'] = $GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFound_handling'];
        $GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFound_handling'] = 'USER_FUNCTION:Cobweb\Redirect40x\UserFunc\Redirect40x->redirect';

        // Avoid headers to be send before this extension performs redirections
        $GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFound_handling_statheader_hooked'] = $GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFound_handling_statheader'];
        $GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFound_handling_statheader'] = '';
    }
}