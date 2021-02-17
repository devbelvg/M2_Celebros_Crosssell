<?php

/**
 * Celebros
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish correct extension functionality.
 * If you wish to customize it, please contact Celebros.
 *
 ******************************************************************************
 * @category    Celebros
 * @package     Celebros_Crosssell
 */

namespace Celebros\Crosssell\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * Crosssell data helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const XML_PATH_CROSSSELL_ENABLED = 'celebros_crosssell/general/crosssell_enabled';
    public const XML_PATH_CROSSSELL_LIMIT = 'celebros_crosssell/general/crosssell_limit';
    public const XML_PATH_UPSELL_ENABLED = 'celebros_crosssell/general/upsell_enabled';
    public const XML_PATH_UPSELL_LIMIT = 'celebros_crosssell/general/upsell_limit';
    public const XML_PATH_DEBUG_REQUEST_SHOW = 'celebros_crosssell/debug/request_show';

    /**
     * @var string
     */
    public $debugMessageTitle = 'Celebros Crosssell Engine';

    /**
     * @param int $store
     * @return bool
     */
    public function isCrosssellEnabled($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CROSSSELL_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param int $store
     * @return int
     */
    public function getCrosssellLimit($store = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_CROSSSELL_LIMIT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param int $store
     * @return bool
     */
    public function isUpsellEnabled($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_UPSELL_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param int $store
     * @return int
     */
    public function getUpsellLimit($store = null)
    {
        return (int)$this->scopeConfig->getValue(
            self::XML_PATH_UPSELL_LIMIT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param int $store
     * @return bool
     */
    public function isRequestDebug($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DEBUG_REQUEST_SHOW,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param array $data
     * @return string
     */
    public function prepareDebugMessage(array $data)
    {
        $data['title'] = isset($data['title']) ? __($data['title']) : __($this->debugMessageTitle);
        $str = __($data['title']);
        unset($data['title']);
        foreach ($data as $key => $val) {
            if ($val) {
                $str .= ' >>> ' . ucfirst(__($key)) . ': ' . $val;
            }
        }

        return $str;
    }
}
