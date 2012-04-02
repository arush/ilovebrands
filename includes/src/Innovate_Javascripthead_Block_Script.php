<?php
/**
* Innovate Analytics block
*
* @codepool   Local
* @category   Innovate
* @package    Innovate_Javascripthead
* @module     Javascripthead
*/
class Innovate_Javascripthead_Block_Script extends Mage_Core_Block_Template
{
    public function getJavascriptheadOn1()
      {        
          return (boolean)Mage::getStoreConfig('javascripthead/script/javascripthead_on1');
      }
    
    public function getJavascriptheadCode1()
      {        
          return (string)Mage::getStoreConfig('javascripthead/script/javascripthead_code1');
      }
    
    public function getJavascriptheadOn2()
      {        
          return (boolean)Mage::getStoreConfig('javascripthead/script/javascripthead_on2');
      }
    
    public function getJavascriptheadCode2()
      {        
          return (string)Mage::getStoreConfig('javascripthead/script/javascripthead_code2');
      }
    
    public function getJavascriptheadOn3()
      {        
          return (boolean)Mage::getStoreConfig('javascripthead/script/javascripthead_on3');
      }
    
    public function getJavascriptheadCode3()
      {        
          return (string)Mage::getStoreConfig('javascripthead/script/javascripthead_code3');
      }
    
    public function getJavascriptheadOn4()
      {        
          return (boolean)Mage::getStoreConfig('javascripthead/script/javascripthead_on4');
      }
    
    public function getJavascriptheadCode4()
      {        
          return (string)Mage::getStoreConfig('javascripthead/script/javascripthead_code4');
      }
}
