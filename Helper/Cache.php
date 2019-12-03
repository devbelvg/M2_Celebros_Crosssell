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

use Magento\Framework\App\Helper;
use Celebros\Crosssell\Model\Cache\Type\Request as CrosssellCache;

/**
 * Crosssell cache helper 
 */
class Cache extends Helper\AbstractHelper
{
    const CACHE_LIFETIME = 13600;
    
    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    protected $helper;
    
    /**
     * @var \Magento\Framework\App\Cache
     */
    protected $cache;
    
    /**
     * @var \Magento\Framework\App\Cache\State
     */
    protected $cacheState;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Cache $cache
     * @param \Magento\Framework\App\Cache\State $cacheState
     * @return void     
     */
    public function __construct(
        Helper\Context $context,
        \Magento\Framework\App\Cache $cache,
        \Magento\Framework\App\Cache\State $cacheState
    ) {
        $this->cache = $cache;
        $this->cacheState = $cacheState;
        parent::__construct($context);
    }
    
    /**
     * @param string $method
     * @param array $vars
     * @return string     
     */
    public function getId(string $method, $vars = array()) : string
    {
        return sha1($method . '::' . implode('', $vars));
    }
    
    /**
     * @param string $cacheId
     * @return string|bool    
     */
    public function load(string $cacheId)
    {
        if ($this->cacheState->isEnabled(CrosssellCache::TYPE_IDENTIFIER)) { 
            return $this->cache->load($cacheId);
        }
        
        return false;
    }
    
    /**
     * @param string $data
     * @param string $cacheId
     * @return bool    
     */
    public function save(string $data, string $cacheId)
    {
        if ($this->cacheState->isEnabled(CrosssellCache::TYPE_IDENTIFIER)) { 
            $this->cache->save($data, $cacheId, array(CrosssellCache::CACHE_TAG), self::CACHE_LIFETIME);
            
            return true;
        }
        
        return false;
    }
}
