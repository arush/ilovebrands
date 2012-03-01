<?php
class Evogue_Customer_Block_Form_Template extends Mage_Core_Block_Abstract {

    protected $_renderBlocks    = array();

    public function addRenderer($type, $block, $template) {
        $this->_renderBlocks[$type] = array(
            'block'     => $block,
            'template'  => $template,
        );

        return $this;
    }

    public function getRenderers() {
        return $this->_renderBlocks;
    }
}
