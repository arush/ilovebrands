<?php
$collection = Mage::getResourceModel('evogue_customersegment/segment_collection');
foreach($collection as $segment) {
    $segment->afterLoad();
    $segment->save();
}
