<?php
/**
 * Pod1 Shutl extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 *
 * @category   Pod1
 * @package    Pod1_Shutl
 * @copyright  Copyright (c) 2010 Pod1 (http://www.pod1.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$installer = $this;
$installer->startSetup();

$sql = "
CREATE TABLE IF NOT EXISTS {$this->getTable('shutl_stocklocation')} (
  `shutl_stocklocation_id` int(11) NOT NULL AUTO_INCREMENT,
  `postcode` varchar(255) NOT NULL,
  `shutl_reference` varchar(255) NOT NULL,
  PRIMARY KEY (`shutl_stocklocation_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS {$this->getTable('shutl_stocklevel')} (
  `shutl_stocklevel_id` int(11) NOT NULL AUTO_INCREMENT,
  `sku` varchar(255) NOT NULL,
  `shutl_stocklocation_id` int(11) NOT NULL,
  `stock_level` int(11) NOT NULL,
  PRIMARY KEY (`shutl_stocklevel_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

";
$installer->run($sql);

$installer->endSetup();
$installer->installEntities();