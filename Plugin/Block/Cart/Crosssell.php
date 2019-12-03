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
namespace Celebros\Crosssell\Plugin\Block\Cart;

use Celebros\Crosssell\Helper\Api as Api;
use Magento\Checkout\Model\Session as Session;
use Magento\Catalog\Block\Product\Context as Context;
use Magento\Catalog\Model\ResourceModel\Product\Collection as Collection;

/**
 * Crosssell block plugin 
 */
class Crosssell
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Catalog\Block\Product\Context
     */
    protected $context;
    
    /**
     * @var int
     */
    protected $_maxItemCount;
    
    /**
     * @var array
     */
    protected $_addedIds = [];
    
    /**
     * @var array
     */
    protected $_items = [];

    /**
     * @var \Celebros\Crosssell\Helper\Data
     */
    public $helper;
    
    /**
     * @param \Celebros\Crosssell\Helper\Data $helper
     * @return void     
     */
    public function __construct(
        Api $helper,
        Session $checkoutSession,
        Context $context
    ) {
        $this->helper = $helper;
        $this->_checkoutSession = $checkoutSession;
        $this->_catalogConfig = $context->getCatalogConfig();
        $this->_maxItemCount = $this->helper->getCrosssellLimit();
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     */
    protected function _collectItems(\Magento\Catalog\Model\Product $product)
    {
        $collection = $product->getCrossSellProductCollection();
        $collection = $this->_addProductAttributesAndPrices($collection);
        foreach ($collection as $it) {
            if (!in_array($it->getEntityId(), $this->_addedIds)
            && count($this->items) < $this->_maxItemCount) {
                $this->items[] = $it;
                $this->_addedIds[] = $it->getEntityId();
            }
        } 
    }
    
    /**
     * @return int
     */
    protected function _getLastAddedProductId()
    {
        return $this->_checkoutSession->getLastAddedProductId(true);
    }
    
    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function _addProductAttributesAndPrices(Collection $collection)
    {
        return $collection
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->addAttributeToSelect(
                $this->_catalogConfig->getProductAttributes()
            )->addUrlRewrite();
    }
    
    /**
     * @param \Magento\Checkout\Block\Cart\Crosssell $subj
     * @param array $result
     * @return array
     */
    public function aroundGetItems(\Magento\Checkout\Block\Cart\Crosssell $subj, $result)
    {
        if ($this->helper->isCrosssellEnabled()) {
            $this->items = (array)$subj->getData('items');
            if (empty($this->items)) {
                $lastAddedId = (int)$this->_getLastAddedProductId();
                $lastAddedProduct = null;
                $quoteItems = $subj->getQuote()->getAllItems();
                foreach ($quoteItems as $item) {
                    $this->_addedIds[] = $item->getProductId();
                    if ($item->getProductId() == $lastAddedId) {
                        $lastAddedProduct = $item->getProduct();
                    }
                }

                if ($lastAddedProduct instanceof \Magento\Catalog\Model\Product) {
                    $this->_collectItems($lastAddedProduct);
                }
                
                if (count($this->items) < $this->_maxItemCount) {
                    foreach ($quoteItems as $item) {
                        $product = $item->getProduct();
                        $this->_collectItems($product);
                    }
                }

                $subj->setData('items', $this->items);
            }
            
            return $this->items;
        }
        
        return $result;
    }
}