<?php
/**
* Innovate Analytics block
*
* @codepool   Local
* @category   Innovate
* @package    Innovate_Analytics
* @module     Analytics
*/
class Innovate_Kissinsights_Block_Survey extends Mage_Core_Block_Template
{
    public function getKissinsightsOn()
      {        
          return (boolean)Mage::getStoreConfig('kissinsights/survey/kissinsights_on');
      }
    
    public function getKissinsightsCode()
      {        
          return (string)Mage::getStoreConfig('kissinsights/survey/kissinsights_code');
      }
    
    public function getKissinsightsIdentify()
      {        
          return (boolean)Mage::getStoreConfig('kissinsights/survey/kissinsights_identify');
      }
    
}
