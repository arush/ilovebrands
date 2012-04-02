<?php

class Ebizmarts_SagePaySuite_Model_Config extends Mage_Payment_Model_Config
{

    /**
     * Retrieve array of credit card types
     *
     * @return array
     */
    public function getCcTypesSagePayDirect($includePaypal = true)
    {
        $types = array();
        foreach (Mage::getConfig()->getNode('global/payment/cc_sgps/types')->asArray() as $data) {
			if($includePaypal === false && $data['code'] == 'PAYPAL'){
        		continue;
        	}
        	$types[$data['code']] = $data['name'];
        }
        return $types;
    }

    public function alterY(&$item, $key)
    {
        $item = $key;
    }

    public function getYearsStart()
    {
      $first = date('Y');
      $_y = range($first-5, $first);
      $_y = array_flip($_y);

      array_walk($_y, array($this, 'alterY'));

      return $_y;
    }

}