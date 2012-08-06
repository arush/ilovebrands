<?php
/**
 * Manufacturers extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   FME
 * @package    Manufacturers
 * @author     Kamran Rafiq Malik <kamran.malik@unitedsol.net>
 * @copyright  Copyright 2010 © free-magentoextensions.com All right reserved
 */

class FME_Manufacturers_Model_Productmanufacturers extends Mage_Core_Model_Abstract

{

	 public function _construct()
    {
        parent::_construct();
        
        $this->_init('manufacturers/productmanufacturers');
        $this->setIdFieldName('manufacturers_id');
    }

     /**
     * Retrieve option array
     *
     * @return array
     */
    static public function getOptionArray()
    {
       
	   $optionValues = array();
		$collection = Mage::getModel('manufacturers/manufacturers')->getCollection();

		foreach ( $collection as $item ) {
			array_push($optionValues,array($item->getManufacturersId(), $item->getMName()));
		}
		
        return $optionValues;
    }

       /**
     * Retrieve all options
     *
     * @return array
     */
    static public function getAllOption()
    {
        $options = self::getOptionArray();
        array_unshift($options, array('value'=>'', 'label'=>''));
        return $options;
    }


/**
     * Retireve all options
     *
     * @return array
     */
    static public function getAllOptions()
    {
        $res = array();
        $res[] = array('value'=>'0', 'label'=> Mage::helper('catalog')->__('No Manufacturer'));
        foreach (self::getOptionArray() as $index => $value) {
            $res[] = array(
               'value' => $value[0],
               'label' => $value[1]
            );
        }
        return $res;
    }
    

 /**
     * Retrieve option text
     *
     * @param int $optionId
     * @return string
     */
    static public function getOptionText($optionId)
    {
        $options = self::getOptionArray();
        return isset($options[$optionId]) ? $options[$optionId] : null;
    }


    /**
     * Retrieve Column(s) for Flat
     *
     * @return array
     */
    public function getFlatColums()
    {
        return array();
    }


    /**
     * Retrieve Indexes for Flat
     *
     * @return array
     */
    public function getFlatIndexes()
    {
        return array();
    }

  /**
     * Retrieve Select For Flat Attribute update
     *
     * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @param int $store
     * @return Varien_Db_Select|null
     */
    public function getFlatUpdateSelect($store)
    {
        return null;
    }
} 