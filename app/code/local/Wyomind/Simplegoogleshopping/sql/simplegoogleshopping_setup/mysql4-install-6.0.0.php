<?php

$installer = $this;

$installer->startSetup();

$installer->run("
DROP TABLE IF   EXISTS {$this->getTable('simplegoogleshopping')};
 ");

 $installer->run("

CREATE TABLE {$this->getTable('simplegoogleshopping')} (
 `simplegoogleshopping_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `simplegoogleshopping_filename` varchar(255) DEFAULT NULL,
  `simplegoogleshopping_path` varchar(255) DEFAULT NULL,
  `simplegoogleshopping_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `store_id` int(11) NOT NULL DEFAULT '1',
  `simplegoogleshopping_url` varchar(120) DEFAULT NULL,
  `simplegoogleshopping_title` text ,
  `simplegoogleshopping_description` text ,
  `simplegoogleshopping_xmlitempattern` text,
  `simplegoogleshopping_categories` longtext,
  `simplegoogleshopping_category_filter` INT(1) DEFAULT 1,
  `simplegoogleshopping_type_ids` varchar(150) DEFAULT NULL,
  `simplegoogleshopping_visibility` varchar(10) DEFAULT NULL,
  `simplegoogleshopping_attributes` text ,
  `cron_expr` varchar(900) NOT NULL DEFAULT '{\"days\":[\"Monday\",\"Tuesday\",\"Wednesday\",\"Thursday\",\"Friday\",\"Saturday\",\"Sunday\"],\"hours\":[\"00:00\",\"04:00\",\"08:00\",\"12:00\",\"16:00\",\"20:00\"]}',
  PRIMARY KEY (`simplegoogleshopping_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");
$installer->run("
	
	INSERT INTO `{$this->getTable('simplegoogleshopping')}` (`simplegoogleshopping_id`, `simplegoogleshopping_filename`, `simplegoogleshopping_path`, `simplegoogleshopping_time`, `store_id`, `simplegoogleshopping_url`, `simplegoogleshopping_title`, `simplegoogleshopping_description`, `simplegoogleshopping_xmlitempattern`, `simplegoogleshopping_categories`, `simplegoogleshopping_type_ids`, `simplegoogleshopping_visibility`, `simplegoogleshopping_attributes`) VALUES


(1, 'GoogleShopping_standard.xml', '/', NULL, 1, 'http://wwww.website.com', 'Data feed title', 'Data feed description', '<g:id>{sku}</g:id>
<title>{name,[substr],[70],[...]}</title>
<link>{url}</link>
<!-- you must change the currency unit or convert the price to match with your magento store and your merchant account setting -->
<g:price>{normal_price,[USD]} </g:price>
{G:SALE_PRICE,[USD]}
<g:online_only>y</g:online_only>
<description>{description,[html_entity_decode],[strip_tags]}</description>
<g:condition>new</g:condition>
{G:PRODUCT_TYPE,[10]}
{G:GOOGLE_PRODUCT_CATEGORY}
{G:IMAGE_LINK}
<g:availability>{is_in_stock?[in stock]:[out of stock]}</g:availability>
<g:quantity>{qty}</g:quantity>
<g:featured_product>{is_special_price?[yes]:[no]} </g:featured_product>
<g:color>{color,[implode],[,]}</g:color>
<g:shipping_weight>{weight,[float],[2]} kilograms</g:shipping_weight>
{G:PRODUCT_REVIEW}
<g:manufacturer>{manufacturer}</g:manufacturer>
<!-- In most of cases brand + mpn are sufficient, eg. :-->
<g:brand>{manufacturer}</g:brand>
<g:mpn>{sku}</g:mpn>
<!-- But it is better to use one of these identifiers if available : EAN, ISBN or UPC, eg : -->
<g:gtin>{upc}</g:gtin>', '*', 'simple,configurable,bundle,virtual,downloadable', '1,2,3,4', '[{\"line\": \"0\", \"checked\": true, \"code\": \"price\", \"condition\": \"gt\", \"value\": \"0\"}, {\"line\": \"1\", \"checked\": true, \"code\": \"sku\", \"condition\": \"notnull\", \"value\": \"\"}, {\"line\": \"2\", \"checked\": true, \"code\": \"name\", \"condition\": \"notnull\", \"value\": \"\"}, {\"line\": \"3\", \"checked\": true, \"code\": \"short_description\", \"condition\": \"notnull\", \"value\": \"\"}, {\"line\": \"4\", \"checked\": false, \"code\": \"cost\", \"condition\": \"eq\", \"value\": \"\"}, {\"line\": \"5\", \"checked\": false, \"code\": \"cost\", \"condition\": \"eq\", \"value\": \"\"}, {\"line\": \"6\", \"checked\": false, \"code\": \"cost\", \"condition\": \"eq\", \"value\": \"\"}, {\"line\": \"7\", \"checked\": false, \"code\": \"cost\", \"condition\": \"eq\", \"value\": \"\"}, {\"line\": \"8\", \"checked\": false, \"code\": \"cost\", \"condition\": \"eq\", \"value\": \"\"}, {\"line\": \"9\", \"checked\": false, \"code\": \"cost\", \"condition\": \"eq\", \"value\": \"\"}, {\"line\": \"10\", \"checked\": false, \"code\": \"cost\", \"condition\": \"eq\", \"value\": \"\"}]'),

(2, 'GoogleShopping_configurable_products.xml', '/', NULL, 1, 'http://wwww.website.com', 'Export for configurable products', 'This template is designed to publish the different variants of all configurable, grouped or bundle products.','<g:id>{sku}</g:id>
{G:ITEM_GROUP_ID}
<title>{name,[substr],[70],[...]}</title>
<link>{url parent}</link>
<g:price>{normal_price,[USD]}</g:price>
{G:SALE_PRICE,[USD]}
<g:online_only>y</g:online_only>
<description>{description parent,[html_entity_decode],[strip_tags]}</description>
<g:condition>new</g:condition>
{G:PRODUCT_TYPE parent,[10]}
{G:GOOGLE_PRODUCT_CATEGORY parent}
{G:IMAGE_LINK parent}
<g:availability>{is_in_stock parent?[in stock]:[out of stock]}</g:availability>
<g:quantity>{qty}</g:quantity>
<g:featured_product>{is_special_price?[yes]:[no]} </g:featured_product>
<g:color>{color,[implode],[,]}</g:color>
<g:shipping_weight>{weight,[float],[2]} kilograms</g:shipping_weight>
{G:PRODUCT_REVIEW}
<g:manufacturer>{manufacturer}</g:manufacturer>
<!-- In most of cases brand + mpn are sufficient, eg. :-->
<g:brand>{manufacturer}</g:brand>
<g:mpn>{sku}</g:mpn>
<!-- But it is better to use one of these identifiers if available : EAN, ISBN or UPC, eg : -->
<g:gtin>{upc}</g:gtin>', '*', 'simple', '1', '[{\"line\": \"0\", \"checked\": true, \"code\": \"price\", \"condition\": \"gt\", \"value\": \"0\"}, {\"line\": \"1\", \"checked\": true, \"code\": \"sku\", \"condition\": \"notnull\", \"value\": \"\"}, {\"line\": \"2\", \"checked\": true, \"code\": \"name\", \"condition\": \"notnull\", \"value\": \"\"}, {\"line\": \"3\", \"checked\": true, \"code\": \"short_description\", \"condition\": \"notnull\", \"value\": \"\"}, {\"line\": \"4\", \"checked\": false, \"code\": \"cost\", \"condition\": \"eq\", \"value\": \"\"}, {\"line\": \"5\", \"checked\": false, \"code\": \"cost\", \"condition\": \"eq\", \"value\": \"\"}, {\"line\": \"6\", \"checked\": false, \"code\": \"cost\", \"condition\": \"eq\", \"value\": \"\"}, {\"line\": \"7\", \"checked\": false, \"code\": \"cost\", \"condition\": \"eq\", \"value\": \"\"}, {\"line\": \"8\", \"checked\": false, \"code\": \"cost\", \"condition\": \"eq\", \"value\": \"\"}, {\"line\": \"9\", \"checked\": false, \"code\": \"cost\", \"condition\": \"eq\", \"value\": \"\"}, {\"line\": \"10\", \"checked\": false, \"code\": \"cost\", \"condition\": \"eq\", \"value\": \"\"}]'),
        
(3, 'Special_StockInTheChannel.xml', '/', NULL, 1, 'http://wwww.website.com', 'Data feed title', 'Stock In The Channel Template', '<g:id>{sku}</g:id>
<title>{name,[substr],[70],[...]}</title>
<!-- Stock In The Channel : url -->
{SC:URL}
<g:price>{normal_price,[USD]} </g:price>
{G:SALE_PRICE,[USD]}
<g:online_only>y</g:online_only>
<!-- Stock In The Channel : description -->
{SC:DESCRIPTION,[htmlentities],[strip_tags]}
<g:condition>new</g:condition>
{G:PRODUCT_TYPE,[10]}
{G:GOOGLE_PRODUCT_CATEGORY}
<!-- Stock In The Channel : image -->
{SC:IMAGES}
<g:availability>{is_in_stock?[in stock]:[out of stock]}</g:availability>
<g:quantity>{qty}</g:quantity>
<g:featured_product>{is_special_price?[yes]:[no]} </g:featured_product>
<g:color>{color,[implode],[,]}</g:color>
<g:shipping_weight>{weight,[float],[2]} kilograms</g:shipping_weight>
{G:PRODUCT_REVIEW}
<g:manufacturer>{manufacturer}</g:manufacturer>
<g:brand>{manufacturer}</g:brand>
<g:mpn>{sku}</g:mpn>
<!-- Stock In The Channel : ean -->
{SC:EAN}', '*', 'simple', '2,3,4', '[{\"line\":\"0\",\"checked\":true,\"code\":\"price\",\"condition\":\"gt\",\"value\":\"0\"},{\"line\":\"1\",\"checked\":true,\"code\":\"sku\",\"condition\":\"notnull\",\"value\":\"\"},{\"line\":\"2\",\"checked\":true,\"code\":\"name\",\"condition\":\"notnull\",\"value\":\"\"},{\"line\":\"3\",\"checked\":true,\"code\":\"small_image\",\"condition\":\"neq\",\"value\":\"\"},{\"line\":\"4\",\"checked\":false,\"code\":\"ean\",\"condition\":\"notnull\",\"value\":\"\"},{\"line\":\"5\",\"checked\":true,\"code\":\"description\",\"condition\":\"notnull\",\"value\":\"\"},{\"line\":\"6\",\"checked\":true,\"code\":\"description\",\"condition\":\"neq\",\"value\":\"\"},{\"line\":\"7\",\"checked\":false,\"code\":\"cost\",\"condition\":\"eq\",\"value\":\"\"},{\"line\":\"8\",\"checked\":false,\"code\":\"cost\",\"condition\":\"eq\",\"value\":\"\"},{\"line\":\"9\",\"checked\":false,\"code\":\"cost\",\"condition\":\"eq\",\"value\":\"\"},{\"line\":\"10\",\"checked\":false,\"code\":\"cost\",\"condition\":\"eq\",\"value\":\"\"}]');


");
        
if(strpos($_SERVER['HTTP_HOST'],"wyomind.com"))
 $installer->run("UPDATE `{$this->getTable('simplegoogleshopping')}` SET simplegoogleshopping_categories ='[{\"line\": \"1/3\", \"checked\": false, \"mapping\": \"\"}, {\"line\": \"1/3/10\", \"checked\": false, \"mapping\": \"\"}, {\"line\": \"1/3/10/22\", \"checked\": false, \"mapping\": \"Furniture > Living Room Furniture\"}, {\"line\": \"1/3/10/23\", \"checked\": false, \"mapping\": \"Furniture > Bedroom Furniture\"}, {\"line\": \"1/3/13\", \"checked\": false, \"mapping\": \"\"}, {\"line\": \"1/3/13/12\", \"checked\": false, \"mapping\": \"Cameras & Optics\"}, {\"line\": \"1/3/13/12/25\", \"checked\": false, \"mapping\": \"Cameras & Optics > Camera & Optic Accessories\"}, {\"line\": \"1/3/13/12/26\", \"checked\": false, \"mapping\": \"Cameras & Optics > Cameras > Digital Cameras\"}, {\"line\": \"1/3/13/15\", \"checked\": false, \"mapping\": \"\"}, {\"line\": \"1/3/13/15/27\", \"checked\": false, \"mapping\": \"Electronics > Computers > Desktop Computers\"}, {\"line\": \"1/3/13/15/28\", \"checked\": false, \"mapping\": \"Electronics > Computers > Desktop Computers\"}, {\"line\": \"1/3/13/15/29\", \"checked\": false, \"mapping\": \"Electronics > Computers > Computer Accessorie\"}, {\"line\": \"1/3/13/15/30\", \"checked\": false, \"mapping\": \"Electronics > Computers > Computer Accessorie\"}, {\"line\": \"1/3/13/15/31\", \"checked\": false, \"mapping\": \"Electronics > Computers > Computer Accessorie\"}, {\"line\": \"1/3/13/15/32\", \"checked\": false, \"mapping\": \"Electronics > Computers > Computer Accessorie\"}, {\"line\": \"1/3/13/15/33\", \"checked\": false, \"mapping\": \"Electronics > Computers > Computer Accessorie\"}, {\"line\": \"1/3/13/15/34\", \"checked\": false, \"mapping\": \"Electronics > Computers > Computer Accessorie\"}, {\"line\": \"1/3/13/8\", \"checked\": false, \"mapping\": \"Electronics > Communications > Telephony > Mobile Phones\"}, {\"line\": \"1/3/18\", \"checked\": false, \"mapping\": \"\"}, {\"line\": \"1/3/18/19\", \"checked\": false, \"mapping\": \"Apparel & Accessories > Clothing > Activewear > Sweatshirts\"}, {\"line\": \"1/3/18/24\", \"checked\": false, \"mapping\": \"Apparel & Accessories > Clothing > Pants\"}, {\"line\": \"1/3/18/4\", \"checked\": false, \"mapping\": \"Apparel & Accessories > Clothing > Tops > Shirts\"}, {\"line\": \"1/3/18/5\", \"checked\": false, \"mapping\": \"Apparel & Accessories > Shoes\"}, {\"line\": \"1/3/18/5/16\", \"checked\": false, \"mapping\": \"Apparel & Accessories > Shoes\"}, {\"line\": \"1/3/18/5/17\", \"checked\": false, \"mapping\": \"Apparel & Accessories > Shoes\"}, {\"line\": \"1/3/20\", \"checked\": false, \"mapping\": \"\"}]'");

$installer->endSetup(); 



