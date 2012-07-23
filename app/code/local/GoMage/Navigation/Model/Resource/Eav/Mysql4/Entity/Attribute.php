<?php
 /**
 * GoMage Advanced Navigation Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2011 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 3.0
 * @since        Class available since Release 1.0
 */
	
	class GoMage_Navigation_Model_Resource_Eav_Mysql4_Entity_Attribute extends Mage_Catalog_Model_Resource_Eav_Mysql4_Attribute{
	
	    protected function _saveOption(Mage_Core_Model_Abstract $object)
	    {
	        $option = $object->getOption();
	        if (is_array($option)) {
	            $write = $this->_getWriteAdapter();
	            $optionTable        = $this->getTable('attribute_option');
	            $optionValueTable   = $this->getTable('attribute_option_value');
	            $stores = Mage::getModel('core/store')
	                ->getResourceCollection()
	                ->setLoadDefault(true)
	                ->load();

	            if (isset($option['value'])) {
	                $attributeDefaultValue = array();
	                if (!is_array($object->getDefault())) {
	                    $object->setDefault(array());
	                }

	                foreach ($option['value'] as $optionId => $values) {
	                    $intOptionId = (int) $optionId;
	                    if (!empty($option['delete'][$optionId])) {
	                        if ($intOptionId) {
	                            $condition = $write->quoteInto('option_id=?', $intOptionId);
	                            $write->delete($optionTable, $condition);
	                        }

	                        continue;
	                    }

	                    if (!$intOptionId) {
	                        $data = array(
	                           'attribute_id'  => $object->getId(),
	                           'sort_order'    => isset($option['order'][$optionId]) ? $option['order'][$optionId] : 0,
	                        );
	                        $write->insert($optionTable, $data);
	                        $intOptionId = $write->lastInsertId();
	                    }
	                    else {
	                        $data = array(
	                           'sort_order'    => isset($option['order'][$optionId]) ? $option['order'][$optionId] : 0,
	                        );
	                        $write->update($optionTable, $data, $write->quoteInto('option_id=?', $intOptionId));
	                    }
	                    
	                    $attribute_id = $object->getId();
	                    $table = Mage::getSingleton('core/resource')->getTableName('gomage_navigation_attribute_option');
	                    
	                    $connection =  $this->_getReadAdapter();
	                    
	                    
	                    if(isset($option['remove_image'][$intOptionId]) && $option['remove_image'][$intOptionId] > 0){
							
							$connection->query("DELETE FROM {$table} WHERE `attribute_id` = {$attribute_id} AND `option_id` = {$intOptionId}; ");
							
						}
	                    
	                    if(isset($option['image'][$optionId])){
	                    	
	                    	$imageInfo = Zend_Json::decode($option['image'][$optionId]);
	                    	if(isset($imageInfo[0])){
	                    		$imageInfo = $imageInfo[0];
	                    		
	                    	}else{
	                    		$imageInfo = null;
	                    	}
	                    	
	                    	if(!empty($imageInfo) && isset($imageInfo['status']) && $imageInfo['status'] == 'new'){
	                    		
	                    		$image 	= Mage::getSingleton('gomage_navigation/observer')->moveImageFromTmp($imageInfo['file']);
								$name	= $imageInfo['name'];
								$size	= (float)$imageInfo['size'];
	                    		
	                    		if($connection->fetchOne("SELECT COUNT(*) FROM {$table} WHERE `attribute_id` = {$attribute_id} AND `option_id` = {$intOptionId};") > 0){
								
									$connection->query("UPDATE {$table} SET `filename` = '{$image}', `name` = '{$name}', `size` = {$size} WHERE `attribute_id` = {$attribute_id} AND `option_id` = {$intOptionId}; ");
								
								}else{
									
									$connection->query("INSERT INTO {$table} VALUES ({$attribute_id},{$intOptionId},'{$image}','{$name}',{$size});");
									
								}
	                    		
	                    	}
	                    	
	                    	
	                    }

	                    if (in_array($optionId, $object->getDefault())) {
	                        if ($object->getFrontendInput() == 'multiselect') {
	                            $attributeDefaultValue[] = $intOptionId;
	                        } else if ($object->getFrontendInput() == 'select') {
	                            $attributeDefaultValue = array($intOptionId);
	                        }
	                    }


	                    // Default value
	                    if (!isset($values[0])) {
	                        Mage::throwException(Mage::helper('eav')->__('Default option value is not defined.'));
	                    }

	                    $write->delete($optionValueTable, $write->quoteInto('option_id=?', $intOptionId));
	                    foreach ($stores as $store) {
	                        if (isset($values[$store->getId()]) && (!empty($values[$store->getId()]) || $values[$store->getId()] == "0")) {
	                            $data = array(
	                                'option_id' => $intOptionId,
	                                'store_id'  => $store->getId(),
	                                'value'     => $values[$store->getId()],
	                            );
	                            $write->insert($optionValueTable, $data);
	                        }
	                    }
	                }

	                $write->update($this->getMainTable(), array(
	                    'default_value' => implode(',', $attributeDefaultValue)
	                ), $write->quoteInto($this->getIdFieldName() . '=?', $object->getId()));
	            }
	        }
	        return $this;
	    }
		
		
	}