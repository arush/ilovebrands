<?php
class Gosquared_Livestats_Model_Adminhtml_System_Source_Track {
    public function toOptionArray() {
        return array(
            array('value'=>1, 'label'=>' Track'),
            array('value'=>0, 'label'=>' Don\'t Track')
        );
    }
}