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

use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;
use Zend\Uri\UriFactory as UriFactory;

/**
 * Crosssell API helper 
 */
class Api extends \Celebros\Crosssell\Helper\Data
{
    const XML_PATH_ADVANCED = 'celebros_crosssell/advanced/';
    const XML_PATH_HOST_PARAM = 'crosssell_address';
    const API_URL_PATH = '/JsonEndPoint/ProductsRecommendation.aspx';
    const API_SUCCESS_STATUS = 'Success';
    
    /**
     * @var array
     */
    protected $apiQuery = [];
    
    /**
     * @var string
     */
    protected $apiUrl;
    
    /**
     * @var array
     */
    protected $response = [];

    /**
     * @var array
     */
    protected $requestParams = [
        'siteKey' => 'crosssell_customer_name',
        'RequestHandle' => 'crosssell_request_handle',
        'RequestType' => '1',
        'Encoding' => 'utf-8'
    ];
    
    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    public $curl;
    
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $jsonHelper;
    
    /**
     * @var \Celebros\Crosssell\Helper\Cache
     */
    public $cache;
    
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    public $messageManager;
    
    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Celebros\Crosssell\Helper\Cache $cache
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @return void
     */
    public function __construct(
        Context $context,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Celebros\Crosssell\Helper\Cache $cache,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->curl = $curl;
        $this->jsonHelper = $jsonHelper;
        $this->cache = $cache;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }
    
    /**
     * @param string $param
     * @param int $store
     * @return string
     */
    protected function _extractParam($param, $store = null)
    {
        $configVal = $this->scopeConfig->getValue(
            self::XML_PATH_ADVANCED . $param,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        
        return $configVal;
    }
    
    /**
     * @param string $sku
     * @return void
     */
    protected function _collectApiUrlParams($sku)
    {
        foreach ($this->requestParams as $key => $param) {
            $conf = $this->_extractParam($param);
            $conf = $conf ? : $param;
            $this->apiQuery[$key] = $conf;
        }
        
        $this->apiQuery['SKU'] = $sku;
    }
    
    /**
     * @param string $sku
     * @return string
     */
    protected function prepareApiUrl($sku) : string
    {
        $uri = UriFactory::factory('https:');
        $uri->setHost($this->_extractParam(self::XML_PATH_HOST_PARAM));
        $uri->setPath(self::API_URL_PATH);
        $this->_collectApiUrlParams($sku); 
        $uri->setQuery($this->apiQuery);
        $this->apiUrl = $uri->toString();
        
        return $this->apiUrl;
    }

    /**
     * @param array $result
     * @return bool
     */
    protected function checkStatus(array $result)
    {
        if (isset($result['Status']) 
        && $result['Status'] == self::API_SUCCESS_STATUS) {
            return true;
        }
        
        return false;
    }
    
    /**
     * @param array $result
     * @return array
     */
    protected function extractItemIds(array $result) : array
    {
        if (isset($result['Items'])) {
            $skus = [];
            foreach ($result['Items'] as $item) {
                if (isset($item['Fields']) && isset($item['Fields']['SKU'])) {
                    $skus[] = $item['Fields']['SKU'];
                }
            }    
        
            return $skus;
        }
        
        return [];
    }
    
    /**
     * @param array $message
     * @return void
     */
    protected function sendDebugMessage(array $message)
    {
        if ($this->isRequestDebug()) {
            $this->messageManager->addSuccess(
                $this->prepareDebugMessage($message)
            );
        }
    }
    
    /**
     * @param string $sku
     * @return array
     */
    public function getRecommendedIds($sku) : array
    {
        $cacheId = $this->cache->getId(__METHOD__, array($sku));
        $this->prepareApiUrl($sku);
        $arrIds = array();
        $startTime = round(microtime(true) * 1000);
        $this->curl->get($this->apiUrl, []);
        if ($response = $this->cache->load($cacheId)) {
            $this->sendDebugMessage([
                'request' => $this->apiUrl,
                'cached' => 'TRUE'
            ]);
          
            return explode(",", $response);
        } else {
            $stime = round(microtime(true) * 1000) - $startTime;
            $this->sendDebugMessage([
                'request' => $this->apiUrl,
                'cached' => 'FALSE',
                'duration' => $stime . 'ms'
            ]);
                
            $result = (array)$this->jsonHelper->jsonDecode($this->curl->getBody());
            if ($this->checkStatus($result)) {
                $ids = $this->extractItemIds($result);
                $this->cache->save(implode(",", $ids), $cacheId);
                return $ids;
            }
        }

        return [];
    }
}
