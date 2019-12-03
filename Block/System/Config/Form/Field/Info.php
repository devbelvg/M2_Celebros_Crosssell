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
namespace Celebros\Crosssell\Block\System\Config\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Setup\ModuleContextInterface;

class Info extends \Magento\Config\Block\System\Config\Form\Field
{
    const MODULE_NAME = 'Celebros_Crosssell';
    
    /**
     * @var \Magento\Framework\Module\ResourceInterface
     */    
    protected $_moduleDb;
    
    /**
     * @param \Magento\Framework\Module\ResourceInterface $moduleDb
     * @return void
     */
    public function __construct(
        \Magento\Framework\Module\ResourceInterface $moduleDb
    ) {
        $this->_moduleDb = $moduleDb;
    }
    
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string 
     */ 
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $id = $element->getHtmlId();
        $html = '<tr id="row_' . $id . '">';
        $html .= '<td class="label">' . __('Module Version') . '</td><td class="value">' . $this->getModuleVersion() . '</td><td class="scope-label"></td>';
        $html .= '</tr>';
       
        return $html;
    }
    
    /**
     * @return string 
     */ 
    public function getModuleVersion()
    {
        return $this->_moduleDb->getDbVersion(self::MODULE_NAME);
    }
}
