<?php
/**
 * Handle extension and TS configuration.
 */
declare(strict_types=1);

namespace Cobweb\Redirect40x\Service;

/**
 * Handle extension and TS configuration.
 */
class ConfigurationService
{
    const EXTNAME = 'redirect40x';
    /**
     * Current configuration.
     *
     * @var array
     */
    protected $configuration = [];

    /**
     * Build up the configuration.
     */
    public function __construct()
    {
        if (isset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][self::EXTNAME])) {
            $extensionConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][self::EXTNAME]);
            if (is_array($extensionConfig)) {
                $this->configuration = array_merge($this->configuration, $extensionConfig);
            }
        }
        if (is_object($GLOBALS['TSFE']) && isset($GLOBALS['TSFE']->tmpl->setup[self::EXTNAME . '.']) && is_array($GLOBALS['TSFE']->tmpl->setup[self::EXTNAME . '.'])) {
            $this->configuration = array_merge(
                $this->configuration,
                $GLOBALS['TSFE']->tmpl->setup[self::EXTNAME . '.']
            );
        }
    }

    /**
     * Get the configuration.
     *
     * @param string $key
     *
     * @return null|mixed
     */
    public function get(string $key)
    {
        $result = null;
        if (isset($this->configuration[$key])) {
            $result = $this->configuration[$key];
        } elseif (isset($GLOBALS['TSFE']->config['config'][self::EXTNAME . '.'][$key])) {
            $result = $GLOBALS['TSFE']->config['config'][self::EXTNAME . '.'][$key];
        }

        return $result;
    }

    /**
     * Get the configuration as bool.
     *
     * @param string $key
     *
     * @return bool
     */
    public function isBool(string $key)
    {
        return (bool) $this->get($key);
    }
}
