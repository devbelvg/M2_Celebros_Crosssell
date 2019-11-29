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
namespace Celebros\Crosssell\Plugin\Model;

use Celebros\Crosssell\Helper\Api as Api;

class Product
{
    /**
     * @var \Celebros\Crosssell\Helper\Data
     */
    public $helper;

    /**
     * @param \Celebros\Crosssell\Helper\Data $helper
     */
    public function __construct(
        Api $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Catalog\Model\Product $subj
     * @param \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection $result
     * @return \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection
     */
    public function afterGetUpSellProductCollection(\Magento\Catalog\Model\Product $subj, $result)
    {
        if ($this->helper->isUpsellEnabled()) {
            $skus = $this->helper->getRecommendedIds($subj->getSku());
            $collection = $subj->getLinkInstance()->useUpSellLinks()->getProductCollection();
            $collection->addFieldToFilter('sku', $skus);
        
            return $collection;
        } else {
            return $result;
        }
    }
    
    /**
     * @param \Magento\Catalog\Model\Product $subj
     * @param \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection $result
     * @return \Magento\Catalog\Model\ResourceModel\Product\Link\Product\Collection
     */
    public function afterGetCrossSellProductCollection(\Magento\Catalog\Model\Product $subj, $result)
    {
        if ($this->helper->isCrosssellEnabled()) {
            $skus = $this->helper->getRecommendedIds($subj->getSku());
            $collection = $subj->getLinkInstance()->useCrossSellLinks()->getProductCollection();
            $collection->addFieldToFilter('sku', $skus);
            
            return $collection;
        } else {
            return $result;
        }
    }
}