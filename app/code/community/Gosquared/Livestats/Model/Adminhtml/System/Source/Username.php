<?php
class Gosquared_Livestats_Model_Adminhtml_System_Source_Username {
    public function toOptionArray() {
        return array(
            array('value'=>0, 'label'=>' Off'),
            array('value'=>1, 'label'=>' User ID'),
			array('value'=>2, 'label'=>' Name'),
			array('value'=>3, 'label'=>' Email')
        );
    }
}