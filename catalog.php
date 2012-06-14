<?php
header('Content-Type: text/plain');

ini_set('display_errors',true);
include('app/Mage.php');
Mage::setIsDeveloperMode(true);

Mage::app(); //pass in store code if you like

$coll = Mage::getModel('catalog/category')->getCollection()->addAttributeToSelect('name')->addAttributeToSelect('id');
foreach ($coll as $cat) {
	echo $cat->getId()."\t".$cat->getName()."\n";
}