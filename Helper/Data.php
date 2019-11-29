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

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_CROSSSELL_ENABLED = 'celebros_crosssell/general/crosssell_enabled';
    const XML_PATH_CROSSSELL_LIMIT = 'celebros_crosssell/general/crosssell_limit';
    const XML_PATH_UPSELL_ENABLED = 'celebros_crosssell/general/upsell_enabled';
    const XML_PATH_UPSELL_LIMIT = 'celebros_crosssell/general/upsell_limit';
    
    public function isCrosssellEnabled($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CROSSSELL_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
    
    public function getCrosssellLimit($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CROSSSELL_LIMIT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
    
    public function isUpsellEnabled($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_UPSELL_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
    
    public function getUpsellLimit($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_UPSELL_LIMIT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }
    
    public function isRequestDebug()
    {
        return true;
    }
    
    public function prepareDebugMessage(array $data)
    {
        if (isset($data['title'])) {
            $str = __($data['title']);
            unset($data['title']);
            foreach ($data as $key => $val) {
                if ($val) {
                    $str .= '<br>' . ucfirst(__($key)) . ': ' . $val;
                }
            }
            
            return $str;
        }
        
        return false;
    }
}