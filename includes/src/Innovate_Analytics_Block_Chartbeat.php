<?php
/**
* Innovate Analytics block
*
* @codepool   Local
* @category   Innovate
* @package    Innovate_Analytics
* @module     Analytics
*/
class Innovate_Analytics_Block_Chartbeat extends Mage_Core_Block_Template
{
    public function getChartbeatOn()
      {        
          return (boolean)Mage::getStoreConfig('analytics/chartbeat/analytics_chartbeat_on');
      }
    
    public function getChartbeatHeadCode()
      {        
          return (string)Mage::getStoreConfig('analytics/track/analytics_chartbeat_head_code');
      }
    
    public function getChartbeatFootCode()
      {        
          return (string)Mage::getStoreConfig('analytics/track/analytics_chartbeat_foot_code');
      }
    
    
}
