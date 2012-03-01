<?php
class Vdh_Randomquote_Block_Catalog_Product_View_Profile extends Mage_Payment_Block_Catalog_Product_View_Profile {

    public function getDateHtml()
    {
        if ($this->_profile->getStartDateIsEditable()) {
        
        	$profile = Mage::helper('randomquote')->getProfileDate();
        	
            $this->setDateHtmlId('recurring_start_date');
            $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
            $calendar = $this->getLayout()
                ->createBlock('core/html_date')
                ->setId('recurring_start_date')
                ->setName(Mage_Payment_Model_Recurring_Profile::BUY_REQUEST_START_DATETIME)
                ->setClass('datetime-picker input-text')
                ->setImage($this->getSkinUrl('images/calendar.gif'))
                ->setFormat($format)
                ->setValue($profile['billing_date'])
                ->setTime(true);
            return $calendar->getHtml();
        }
    }

}