<?php

namespace Cobweb\Redirect40x\UserFunc;

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

use Cobweb\Redirect40x\Service\ConfigurationService;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;


/**
 * This userFunc redirects users to different pages, depending on the 40x error code
 *
 * @author Roberto Presedo <cobweb@cobweb.ch>
 * @package redirect40x
 */
class Redirect40x
{
    /**
     * Reasons codes
     * see \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController::fetch_the_id()
     */
    const PAGE_NOT_ACCESSIBLE = 1;
    const SUBSECTION_NOT_ACCESSIBLE = 2;
    const DOMAIN_NOT_CORRECT = 3;
    const ALIAS_NOT_EXISTS = 4;

    /**
     * The backReference to the mother cObj object set at call time
     *
     * @var ContentObjectRenderer
     */
    public $cObj;

    /**
     * Current extension configuration.
     * @var ConfigurationService
     */
    protected $configuration = [];

    /**
     * Current Url
     * @var string
     */
    protected $currentUrl = '';

    /**
     * Not found messages as defined in
     * see \TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController::fetch_the_id()
     * @var array
     */
    protected $codes = [
        'ID was not an accessible page' => 1,
        'Subsection was found and not accessible' => 2,
        'ID was outside the domain' => 3,
        'The requested page alias does not exist' => 4
    ];

    /**
     * Constructs this backend.
     */
    public function __construct()
    {
        $this->configuration = GeneralUtility::makeInstance(ConfigurationService::class);
        $this->cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
    }

    /**
     * @param $params
     * @throws Exception
     */
    public function redirect($params)
    {
        $this->setCurrentUrl($params['currentUrl']);
        $redirectCode = 0;
        if (isset($this->codes[$params['reasonText']])) {
            $redirectCode = $this->codes[$params['reasonText']];
        }
        try {
            switch ($redirectCode) {
                case self::PAGE_NOT_ACCESSIBLE:
                    // Call hook for "page not accessible" handling:
                    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['redirect40x']['pageNotAccessibleHook'])) {
                        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['redirect40x']['pageNotAccessibleHook'] as $_funcRef) {
                            GeneralUtility::callUserFunction($_funcRef, $params, $this);
                        }
                    } else {
                        // There is a user identified, but he has no access to the current page
                        if (!is_array($this->getFeController()->fe_user->user)) {
                            $this->throw401();
                        } else {
                            $this->throw403();
                        }
                    }
                    break;
                case self::SUBSECTION_NOT_ACCESSIBLE:
                    // Call hook for "subsection not accessible" handling:
                    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['redirect40x']['subsectionNotAccessibleHook'])) {
                        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['redirect40x']['subsectionNotAccessibleHook'] as $_funcRef) {
                            GeneralUtility::callUserFunction($_funcRef, $params, $this);
                        }
                    } else {
                        // There is a user identified, but he has no access to the current page
                        if (!is_array($this->getFeController()->fe_user->user)) {
                            $this->throw401();
                        } else {
                            $this->throw403();
                        }
                    }
                    break;
                case self::DOMAIN_NOT_CORRECT:
                    // Call hook for "domain not correct" handling:
                    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['redirect40x']['domainNotCorrectHook'])) {
                        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['redirect40x']['domainNotCorrectHook'] as $_funcRef) {
                            GeneralUtility::callUserFunction($_funcRef, $params, $this);
                        }
                    } else {
                        $this->throw404();
                    }
                    break;
                case self::ALIAS_NOT_EXISTS:
                    // Call hook for "alias not exists" handling:
                    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['redirect40x']['aliasNotExistsHook'])) {
                        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['redirect40x']['aliasNotExistsHook'] as $_funcRef) {
                            GeneralUtility::callUserFunction($_funcRef, $params, $this);
                        }
                    } else {
                        $this->throw404();
                    }
                    break;
                default:
                    // Call hook for "default 404 page" handling:
                    if (is_array($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['redirect40x']['defaultHook'])) {
                        foreach ($GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['redirect40x']['defaultHook'] as $_funcRef) {
                            GeneralUtility::callUserFunction($_funcRef, $params, $this);
                        }
                    } else {
                        $this->throw404();
                    }
                    break;
            }

        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), 1291068569);
        }
    }

    /**
     * Throws a 401 Unauthorized header
     */
    protected function throw401() {
        $this->redirectToLoginPage();
    }


    /**
     * Throws a 403 Forbidden header
     */
    protected function throw403() {
        $this->redirectToNotAuthorizedPage();
    }


    /**
     * Throws a 404 Page not found header
     */
    protected function throw404() {
        $this->redirectToPageNotFound();
    }


    /**
     * Redirects the visitor to the login page
     * with a return argument to the current page
     */
    protected function redirectToLoginPage()
    {
        $this->fixFeController();
        $targetUid = intval($this->configuration->get('LoginPageUid'));
        if ($targetUid > 0) {
            $lang = (int)GeneralUtility::_GP('L');
            $linkParam['parameter'] = $targetUid;
            $linkParam['additionalParams'] = '&L=' . $lang;
            if (intval($this->configuration->get('AddReturnLink'))) {
                $linkParam['additionalParams'] .= '&return_url=' . $this->getCurrentUrl();
            }
            header('HTTP/1.1 401 Unauthorized');
            header("Refresh:0; url=" . $this->getUrl($linkParam));
            die;
        } else {
            /**
             * loginPageUid is not set in the extension configuration
             */
            throw new \RuntimeException('Redirect40x Extension : Configuration Error: Login Form Page uid is not defined.',
                1508686676);
        }
    }
    /**
     * Redirects the visitor to the login page
     * with a return argument to the current page
     */
    protected function redirectToNotAuthorizedPage()
    {
        $this->fixFeController();
        $targetUid = intval($this->configuration->get('UnauthorizedPageUid'));
        if ($targetUid > 0) {
            $lang = (int)GeneralUtility::_GP('L');
            $linkParam['parameter'] = $targetUid;
            $linkParam['additionalParams'] = '&L=' . $lang;
            header('HTTP/1.1 403 Forbidden');
            header("Refresh:0; url=" . $this->getUrl($linkParam));
            die;
        } else {
            /**
             * loginPageUid is not set in the extension configuration
             */
            throw new \RuntimeException('Redirect40x Extension : Configuration Error: Not Authorized Page uid is not defined.',
                1508712301);
        }
    }


    /**
     * Redirects the visitor to the 404 page
     */
    protected function redirectToPageNotFound()
    {
        if (isset($GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFound_handling_hooked']) && !empty($GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFound_handling_hooked'])) {
            if (!empty($GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFound_handling_statheader_hooked'])) {
                header($GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFound_handling_statheader_hooked']);
            } else {
                header("HTTP/1.1 404 Not Found");
            }
            header("Refresh:0; url=" . $GLOBALS['TYPO3_CONF_VARS']['FE']['pageNotFound_handling_hooked']);
            die;
        } else {
            /**
             * PageNotFoundUid is not set in the extension configuration
             */
            throw new \RuntimeException('Redirect40x Extension : Configuration Error: $GLOBALS[\'TYPO3_CONF_VARS\'][\'FE\'][\'pageNotFound_handling\'] is not defined.',
                1508686586);
        }
    }

    /**
     * Return the url
     * @param array $params
     * @return string
     */
    protected function getUrl($params)
    {
        $params['returnLast'] = 'url';
        $url = $this->cObj->typoLink('', $params);
        if (substr($url, 0, 4) === 'http') {
            return $url;
        }
        if (isset($this->getFeController()->config['config']['absRefPrefix'])) {
            return $this->getFeController()->config['config']['absRefPrefix'] . $url;
        } else {
            return $url;
        }
    }

    /**
     * Initializing the Template Service and config array in the TypoScriptFrontendController
     * Otherwise Typolinks are throwing exceptions
     */
    protected function fixFeController()
    {
        $this->getFeController()->initTemplate();
        $this->getFeController()->getConfigArray();
    }

    /**
     * @return string
     */
    protected function getCurrentUrl(): string
    {
        return $this->currentUrl;
    }

    /**
     * @param string $currentUrl
     */
    protected function setCurrentUrl(string $currentUrl)
    {
        $this->currentUrl = $currentUrl;
    }

    /**
     * @return TypoScriptFrontendController
     */
    protected function getFeController()
    {
        return $GLOBALS['TSFE'];
    }


}
