<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   design_default
 * @package    Mage
 * @copyright  Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
?>


<div class="content-header">
    <table cellspacing="0">
        <tr>
            <td style="width:50%;">
                <h4 class="icon-head head-products">
                    <?php echo $this->__('Customer Point Balances') ?></h4></td>
        </tr>
        <tr> <td> <?php echo $this->getChildHtml('tab_points_summary'); ?> </td> </tr>
        <tr>
            <td style="width:50%;">
                <h4 class="icon-head head-products">
                    <?php echo $this->__('Customer Point Transfers') ?></h4></td>
        </tr>
        <tr> <td> <?php echo $this->getChildHtml('tab_points_grid'); ?> </td> </tr>
    </table>
