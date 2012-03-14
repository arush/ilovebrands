<?php

class Ebizmarts_SagePaySuite_Model_Sagepaysuite_Source_Trncurrency
{
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'base',
                'label' => Mage::helper('sagepaysuite')->__('Base - ' . Mage::app()->getStore()->getConfig('currency/options/base'))
            ),
            array(
                'value' => 'store',
                'label' => Mage::helper('sagepaysuite')->__('Store')
            )
        );
    }
}