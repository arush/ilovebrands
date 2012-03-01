<?php

$this->startSetup();

$this->run("
CREATE TABLE {$this->getTable('evogue_customerbalance')} (
    `primary_id` INT NOT NULL AUTO_INCREMENT ,
    `customer_id` INT( 10 ) UNSIGNED NOT NULL ,
    `website_id` SMALLINT( 5 ) NOT NULL ,
    `balance` DECIMAL( 12, 4 ) NOT NULL ,
    PRIMARY KEY ( `primary_id` ),
    KEY `FK_CUSTOMERBALANCE_CUSTOMER_ENTITY` (`customer_id`),
    CONSTRAINT `FK_CUSTOMERBALANCE_CUSTOMER_ENTITY` FOREIGN KEY (customer_id) REFERENCES `{$this->getTable('customer_entity')}` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE = InnoDB
");

$this->endSetup();
