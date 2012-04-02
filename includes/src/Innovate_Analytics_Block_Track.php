<?php
/**
* Innovate Analytics block
*
* @codepool   Local
* @category   Innovate
* @package    Innovate_Analytics
* @module     Analytics
*/
class Innovate_Analytics_Block_Track extends Mage_Core_Block_Template
{
    public function getRegConfirmOn()
      {        
          return (boolean)Mage::getStoreConfig('analytics/track/analytics_reg_confirm_on');
      }
    
    public function getRegConfirmCode()
      {        
          return (string)Mage::getStoreConfig('analytics/track/analytics_reg_confirm_code');
      }
    
    public function getCheckoutOn()
      {        
          return (boolean)Mage::getStoreConfig('analytics/track/analytics_checkout_on');
      }
    
    public function getCheckoutStartedCode()
      {        
          return (string)Mage::getStoreConfig('analytics/track/analytics_checkout_started_code');
      }
    
    public function getCheckoutSuccessCode()
      {        
          return (string)Mage::getStoreConfig('analytics/track/analytics_checkout_success_code');
      }
      
    public function getInvitationOn()
      {        
          return (boolean)Mage::getStoreConfig('analytics/track/analytics_invitation_on');
      }
    
    public function getInvitationLandingCode()
      {        
          return (string)Mage::getStoreConfig('analytics/track/analytics_invitation_landing_code');
      }
      
    public function getInvitationSuccessCode()
      {
          return (string)Mage::getStoreConfig('analytics/track/analytics_invitation_success_code');
      }
}
