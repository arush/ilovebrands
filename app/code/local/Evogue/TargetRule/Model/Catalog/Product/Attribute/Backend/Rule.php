<?php
class Evogue_TargetRule_Model_Catalog_Product_Attribute_Backend_Rule
    extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract {

    public function beforeSave($object) {
        $attributeName  = $this->getAttribute()->getName();
        $useDefault     = $object->getData($attributeName . '_default');

        if ($useDefault == 1) {
            $object->setData($attributeName, null);
        }

        return $this;
    }
}
