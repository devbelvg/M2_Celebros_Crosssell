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

class Api extends \Celebros\Crosssell\Helper\Data
{
    const XML_PATH_ADVANCED = 'celebros_crosssell/advanced/';
    const XML_PATH_HOST_PARAM = 'crosssell_address';
    const API_URL_PATH = '/JsonEndPoint/ProductsRecommendation.aspx';
    const API_SUCCESS_STATUS = 'Success';
    
    protected $apiQuery = [];
    protected $apiUrl;
    
    public $curl;
    public $jsonHelper;
    public $messageManager;
    
    protected $requestParams = [
        'siteKey' => 'crosssell_customer_name',
        'RequestHandle' => 'crosssell_request_handle',
        'RequestType' => '1',
        'Encoding' => 'utf-8'
    ];
    
    public function __construct(
        Context $context,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->curl = $curl;
        $this->jsonHelper = $jsonHelper;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }
    
    protected function _extractParam($param, $store = null)
    {
        $configVal = $this->scopeConfig->getValue(
            self::XML_PATH_ADVANCED . $param,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        
        return $configVal;
    }
    
    protected function _collectApiUrlParams($sku)
    {
        foreach ($this->requestParams as $key => $param) {
            $conf = $this->_extractParam($param);
            $conf = $conf ? : $param;
            $this->apiQuery[$key] = $conf;
        }
        
        $this->apiQuery['SKU'] = $sku;
    }
    
    protected function prepareApiUrl($sku)
    {
        $uri = UriFactory::factory('https:');
        $uri->setHost($this->_extractParam(self::XML_PATH_HOST_PARAM));
        $uri->setPath(self::API_URL_PATH);
        $this->_collectApiUrlParams($sku); 
        $uri->setQuery($this->apiQuery);
        $this->apiUrl = $uri->toString();
        
        return $this->apiUrl;
    }
    
    public function getRecommendedIds($sku) : array
    {
        $this->prepareApiUrl($sku);
        $arrIds = array();
        $startTime = round(microtime(true) * 1000);
        $this->curl->get($this->apiUrl, []);
        if ($this->isRequestDebug()) {
            $stime = round(microtime(true) * 1000) - $startTime;
            $message = [
                'title' => __('Celebros Crosssell Engine'),
                'request' => $this->apiUrl,
                'cached' => 'FALSE',
                'duration' => $stime . 'ms'
            ];
            
            $this->messageManager->addSuccess(
                $this->prepareDebugMessage($message)
            );
        }
        
        $result = $this->jsonHelper->jsonDecode($this->curl->getBody());
        if ($this->checkStatus($result)) {
            return $this->extractItemIds($result);
        } else {
            return [];
        }
        
        $obj = json_decode($jsonData);
        for ($i=0; isset($obj->Items) && $i < count($obj->Items); $i++) {
            $arrIds[] = $obj->Items[$i]->Fields->SKU;
        }
        
        return $arrIds;    
    }
    
    protected function checkStatus(array $result)
    {
        if (isset($result['Status']) 
        && $result['Status'] == self::API_SUCCESS_STATUS) {
            return true;
        }
        
        return false;
    }
    
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
}