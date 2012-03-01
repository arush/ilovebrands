<?php
class Evogue_CustomerSegment_Model_Segment_Condition_Product_Combine
    extends Evogue_CustomerSegment_Model_Condition_Combine_Abstract {

    public function __construct() {
        parent::__construct();
        $this->setType('evogue_customersegment/segment_condition_product_combine');
    }

    public function getNewChildSelectOptions() {
        $children = array_merge_recursive(
            parent::getNewChildSelectOptions(),
            array(
                array( // self
                    'value' => $this->getType(),
                    'label' => Mage::helper('rule')->__('Conditions Combination')
                )
            )
        );

        if ($this->getDateConditions()) {
            $children = array_merge_recursive(
                $children,
                array(
                    array(
                        'value' => array(
                            Mage::getModel('evogue_customersegment/segment_condition_uptodate')->getNewChildSelectOptions(),
                            Mage::getModel('evogue_customersegment/segment_condition_daterange')->getNewChildSelectOptions(),
                        ),
                        'label' => Mage::helper('evogue_customersegment')->__('Date Ranges')
                    )
                )
            );
        }

        $children = array_merge_recursive(
            $children,
            array(
                Mage::getModel('evogue_customersegment/segment_condition_product_attributes')->getNewChildSelectOptions()
            )
        );

        return $children;
    }

    public function getConditionsSql($customer, $website) {
        return false;
    }

    public function getSubfilterType() {
        return 'product';
    }

	public function getSubfilterSql($fieldName, $requireValid, $website) {
        $table = $this->getResource()->getTable('catalog/product');

        $select = $this->getResource()->createSelect();
        $select->from(array('main'=>$table), array('entity_id'));

        if ($this->getAggregator() == 'all') {
            $whereFunction = 'where';
        } else {
            $whereFunction = 'orWhere';
        }

        $gotConditions = false;
        foreach ($this->getConditions() as $condition) {
            if ($condition->getSubfilterType() == 'product') {
                $subfilter = $condition->getSubfilterSql('main.entity_id', ($this->getValue() == 1), $website);
                if ($subfilter) {
                    $select->$whereFunction($subfilter);
                    $gotConditions = true;
                }
            }
        }
        if (!$gotConditions) {
            $select->where('1=1');
        }

        $inOperator = ($requireValid ? 'IN' : 'NOT IN');
        return sprintf("%s %s (%s)", $fieldName, $inOperator, $select);
    }
}
