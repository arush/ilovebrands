<?php

class Magazento_Homepage_Block_Developer extends Mage_Adminhtml_Block_System_Config_Form_Fieldset {

    public function render(Varien_Data_Form_Element_Abstract $element) {
        $content = '<p></p>';
        $content.= '<style>';
        $content.= '.magazento {
                        background:#FAFAFA;
                        border: 1px solid #CCCCCC;
                        margin-bottom: 10px;
                        padding: 10px;
                        height:auto;

                    }
                    .magazento h3 {
                        color: #EA7601;
                    }
                    .contact-type {
                        color: #EA7601;
                        font-weight:bold;
                    }
                    .magazento img {
                        border: 1px solid #CCCCCC;
                        float:left;
                        height:235px;
                    }
                    .magazento .info {
                        border: 1px solid #CCCCCC;
                        background:#E7EFEF;
                        padding: 5px 10px 0 5px;
                        margin-left:210px;
                        height:230px;
                    }
                    ';
        $content.= '</style>';


        $content.= '<div class="magazento">';
            $content.= '<a href="http://www.ecommerceoffice.com/" target="_blank"><img src="'.Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'frontend/default/default/magazento/homepage/promo.jpg" alt="www.ecommerceoffice.com" /></a>';
            $content.= '<div class="info">';
                $content.= '<h3>Magento extensions</h3>';
                $content.= '<p><a href="http://www.ecommerceoffice.com/" target="_blank">www.ecommerceoffice.com</a> - experts are specializing in custom extension development for the world\'s fastest growing eCommerce platform - Magento. <br/>';
                $content.= 'All extensions are designed for the Magento CMS archetecture, using native libraries and only the most efficient approaches to development.<br/>';
                $content.= 'If you need Magento development or have a concept for an extension you\'d like developed for the Magento CMS platform, please contact us.</br></p>';
                $content.= '--------------------------------------------------------<br>';
                $content.= '<span class="contact-type">Company website:</span> <a href="http://www.ecommerceoffice.com/" target="_blank">www.ecommerceoffice.com</a>  <br/>';
                $content.= '<span class="contact-type">E-mail:</span> volgodark@gmail.com  <br/>';
                $content.= '<span class="contact-type">Skype:</span> volgodark  <br/>';
                $content.= '<span class="contact-type">Phone:</span> +7 909389 2222  <br/>';
                $content.= '<span class="contact-type">Magento:</span> <a href="http://www.magentocommerce.com/magento-connect/developer/Magazento" target="_blank">visit</a>  <br/>';
                $content.= '<span class="contact-type">Facebook:</span> <a href="http://www.facebook.com/ivan.proskuryakov" target="_blank">visit</a>  <br/>';
//                $content.= '<span class="contact-type">LinkedIn:</span> <a href="http://www.linkedin.com/pub/ivan-proskuryakov/31/200/316" target="_blank">visit</a>  <br/>';

                $content.= '</div>';

        $content.= '</div>';

        return $content;


    }


}
