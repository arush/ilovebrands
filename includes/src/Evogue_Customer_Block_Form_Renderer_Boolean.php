<?php
class Evogue_Customer_Block_Form_Renderer_Boolean extends Evogue_Customer_Block_Form_Renderer_Select {
    
    public function getOptions() {
        return array(
            array(
                'value' => '',
                'label' => ''
            ),
            array(
                'value' => '0',
                'label' => Mage::helper('evogue_customer')->__('No')
            ),
            array(
                'value' => '1',
                'label' => Mage::helper('evogue_customer')->__('Yes')
            ),
        );
    }
}
