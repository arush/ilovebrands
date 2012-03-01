<?php
/**
 * WDCA - Sweet Tooth Instore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the SWEET TOOTH (TM) INSTORE
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth Instore License is available at this URL: 
 * http://www.sweettoothrewards.com/instore/sweet_tooth_instore_license.txt
 * The Open Software License is available at this URL: 
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 * 
 * By adding to, editing, or in any way modifying this code, WDCA is 
 * not held liable for any inconsistencies or abnormalities in the 
 * behaviour of this code. 
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the 
 * provided Sweet Tooth Instore License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time WDCA spent 
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension. 
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * support@wdca.ca or call 1-888-699-WDCA(9322), so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewardsinstore]
 * @copyright  Copyright (c) 2011 Sweet Tooth (http://www.sweettoothrewards.com)
 * @license    http://www.sweettoothrewards.com/instore/sweet_tooth_instore_license.txt
 */

/**
 * This class contains Instore specific setup functions.
 *
 * @category   TBT
 * @package    TBT_Rewardsinstore
 * @author     Sweet Tooth Instore Team <support@wdca.ca>
 */
class TBT_Rewardsinstore_Model_Resource_Eav_Mysql4_Setup extends Mage_Eav_Model_Entity_Setup
{
    /**
     * If true, install errors will be thrown as exceptions so we can
     * catch them while creating a new install script.
     * 
     * IMPORTANT: never commit as 'true'
     */
    const DEBUG_MODE = false;
    
    public function insertCartruleAttributes()
    {
        $attributes = array(
            'base_subtotal' => 'Subtotal',
            'total_qty'     => 'Total Items Quantity');
           
        foreach ($attributes as $code => $label) {
            $this->insertCartruleAttribute($code, $label);
        }
    }
    
    /**
     * Helper function for easily adding Cartrule attributes
     */
    public function insertCartruleAttribute($code, $frontend_label)
    {
        $this->attemptQuery ("
            insert  into {$this->getTable('rewardsinstore/cartrule_attribute')}
            (`code`,`frontend_label`) values ('$code','$frontend_label');
        ");
        
    }
    
    public function postDefaultInstallNotice()
    {
        // TODO: include a boolean $update parameter to specify whether we use 'installation' or 'update' in msg_title
        $msg_title = "Sweet Tooth Instore Installation Successful.";
        $msg_desc = "Sweet Tooth Instore v" . Mage::getConfig()->getNode('modules/TBT_Rewardsinstore/version')
            . " has been successfully installed.";
        $msg_url = "http://www.sweettoothrewards.com/wiki/index.php/Instore";
        
        $this->createInstallNotice($msg_title, $msg_desc, $msg_url);
    }
    
    /************************************************************************
     * Code below is copied from the rewards/mysql4_install helper.
     * TODO: refactor the rewards helper into its own Setup.php and extend it.
     * The benefit of this is that it extends the magento installer.
     ***********************************************************************/
    
    protected $ex_stack = array();
    
    /*
     * Errors to be ignored.
     * Arrays of strings are evaluated as AND conditions where all 
     * substrings must be present for the exception to be ignored.
     */
    protected $ignoredErrors = array (
        "SQLSTATE[42S21]: Column already exists",
        array (
            "SQLSTATE[42000]: Syntax error or access violation: 1091 Can't DROP",
            "check that column/key exists"),
        "SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry"
    );
    
    /**
     * Adds an exception problem to the stack of problems that may
     * have occured during installation.
     * Ignores duplicate column name errors; ignore if the msg starts with "SQLSTATE[42S21]: Column already exists"
     * @param Exception $ex
     */
    public function addInstallProblem(Exception $ex)
    {
        if ($this->isIgnoredError($ex)) {
            return $this;
        }
        
        $this->ex_stack [] = $ex;
        return $this;
    }
    
    /**
     * Returns true if the exception is to be ignored.
     *
     * @param unknown_type $ex
     * @return boolean If the exception should be ignored
     */
    protected function isIgnoredError($ex) 
    {
        foreach ($this->ignoredErrors as $ignoredMessage) {
            
            if (is_array($ignoredMessage)) {
                // Evaluate AND conditions
                if ($this->isIgnoredErrorCombination($ex, $ignoredMessage)) {
                    return true;
                }
            } else {
                // Evaluate single message
                if (strpos($ex->getMessage(), $ignoredMessage) !== false) {
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * Returns true if the exception message contains all the 
     * substrings of $ignoredMessages.
     *
     * @param unknown_type $ex
     * @param array $ignoredMessages
     * @return boolean If the exception should be ignored or not
     */
    protected function isIgnoredErrorCombination($ex, array $ignoredMessages) 
    {
        foreach ($ignoredMessages as $ignoreMessage) {
            if (strpos($ex->getMessage(), $ignoreMessage) == false) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Returns true if any problems occured after installation
     * @return boolean 
     */
    public function hasProblems()
    {
        return sizeof($this->ex_stack) > 0;
    }
    
    /**
     * Returns a string of problems that occured after any installation scripts were run through this helper
     * @return string message to display to the user
     */
    public function getProblemsString()
    {
        $msg = Mage::helper('rewardsinstore')->__("The following errors occured while trying to install the module.");
        $msg .= "\n<br>";
        foreach ($this->ex_stack as $ex_i => $ex) {
            $msg .= "<b>#{$ex_i}: </b>";
            if (Mage::getIsDeveloperMode()) {
                $msg .= nl2br($ex);
            } else {
                $msg .= $ex->getMessage();
            }
            $msg .= "\n<br>";
        }
        $msg .= "\n<br>";
        $msg .= Mage::helper('rewardsinstore')->__("If any of these problems were unexpected, I recommend that you contact the module support team to avoid problems in the future.");
        return $msg;
    }
    
    /**
     * Clears any insall problems (exceptions) that were in the stack
     */
    public function clearProblems()
    {
        $this->ex_stack = array();
        return $this;
    }
    
    /**
     * Runs a SQL query using the install resource provided and 
     * remembers any errors that occur. 
     *
     * @param string $sql
     * @return TBT_Rewards_Helper_Mysql4_Install
     */
    public function attemptQuery($sql)
    {
        try {
            $this->run($sql);
        } catch (Exception $ex) {
            $this->throwIfDebugMode($ex);
            $this->addInstallProblem($ex);
        }
        return $this;
    }
    
    protected function throwIfDebugMode($ex) 
    {
        // This will rethrow if debug mode is on
        if (self::DEBUG_MODE) {
            if (!$this->isIgnoredError($ex)) {
                throw $ex;
            }
        }
    }
    
    /**
     * Creates an installation message notice in the backend.
     * 
     * @param string $msg_title
     * @param string $msg_desc
     * @param string $url=null if null default Sweet Tooth URL is used.
     */
    public function createInstallNotice($msg_title, $msg_desc, $url = null)
    {
        $message = Mage::getModel('adminnotification/inbox');
        $message->setDateAdded(date("c", time()));
        
        if ($url == null) {
            $url = "http://www.sweettoothrewards.com/wiki/index.php/Change_Log";
        }
        
        $message->setSeverity(Mage_AdminNotification_Model_Inbox::SEVERITY_NOTICE);
        
        // If problems occured increase severity and append logged messages.
        if ($this->hasProblems()) {
            $message->setSeverity(Mage_AdminNotification_Model_Inbox::SEVERITY_MINOR);
            $msg_title .= " Problems may have occured during installation.";
            $msg_desc .= " " . $this->getProblemsString();
            $this->clearProblems();
        }
        
        $message->setTitle($msg_title);
        $message->setDescription($msg_desc);
        $message->setUrl($url);
        $message->save();
        
        return $this;
    }
    
    /**
     * Add attribute to an entity type
     *
     * If attribute is system will add to all existing attribute sets
     *
     * @param string|integer $entityTypeId
     * @param string $code
     * @param array $attr
     * @return Mage_Eav_Model_Entity_Setup
     */
    public function addAttribute($entityTypeId, $code, array $attr)
    {
        try {
            parent::addAttribute($entityTypeId, $code, $attr);
        } catch (Exception $ex) {
            $this->throwIfDebugMode($ex);
            $this->addInstallProblem($ex);
        }
        return $this;
    }
}
