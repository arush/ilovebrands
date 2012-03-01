<?php

/**
 * Retail Evolved - Facebook Like Button
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA that is bundled with this
 * package in the file EVOLVED_EULA.txt.
 * It is also available through the world-wide-web at this URL:
 * http://retailevolved.com/eula-1-0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to service@retailevolved.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * You may edit this file, but only at your own risk, as long as it is within
 * the constraints of the license agreement. Before upgrading the module (not Magento), 
 * be sure to back up your original installation as the upgrade may override your
 * changes.
 *
 * @category   Evolved
 * @package    Evolved_Like
 * @copyright  Copyright (c) 2010 Kaelex Inc. DBA Retail Evolved (http://retailevolved.com)
 * @license    http://retailevolved.com/eula-1-0 (Retail Evolved EULA 1.0)
 */

class Evolved_Like_Model_System_Config_Backend_Like_Ref extends Mage_Core_Model_Config_Data
{
	protected function _afterSave()
    {
        if(!preg_match("/^[a-zA-Z0-9-+=.:_\/]{0,50}$/", $this->getValue())) {
			Mage::throwException(Mage::helper('evlike')->__('Ref parameter must be less than 50 characters and can contain alphanumeric characters and some punctuation (currently +/=-.:_)'));
		} 

        return $this;
    }
}