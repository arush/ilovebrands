<?php
class Evogue_CustomerBalance_Block_Adminhtml_Customer_Edit_Tab_Customerbalance
    extends Mage_Adminhtml_Block_Widget
    implements Mage_Adminhtml_Block_Widget_Tab_Interface {

    public function __construct() {
        parent::__construct();
        $this->setId('customerbalance');
        $this->setTitle(Mage::helper('evogue_customerbalance')->__('Store Credit'));
    }

    public function getTabLabel() {
        return $this->getTitle();
    }

    public function getTabTitle() {
        return $this->getTitle();
    }

    public function canShowTab() {
        return true;
    }

    public function isHidden() {
        if( !$this->getRequest()->getParam('id') ) {
            return true;
        }
        return false;
    }

    public function getTabClass() {
        return 'ajax';
    }

    public function getSkipGenerateContent() {
        return true;
    }

    public function getAfter() {
        return 'tags';
    }

    public function getTabUrl() {
        return $this->getUrl('*/customerbalance/form', array('_current' => true));
    }
}
