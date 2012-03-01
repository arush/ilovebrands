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

<div class="account-box ad-reviews mypoints-summary">
    <p>

    <div class="head">
        <h4><?php echo $this->__('Points Summary'); ?></h4>
    </div>
    <div class="usable-points"><?php echo $this->__('There are %s in this account.', $this->getCustomerPointsSummary()); ?></div>
    <?php if ($this->hasPendingPoints()): ?>
        <div class="pending-points"><?php echo $this->__('%s are pending approval.', $this->getCustomerPendingPointsSummary()); ?></div>
    <?php endif; ?>
    <?php if ($this->hasOnHoldPoints()): ?>
        <div class="onhold-points"><?php echo $this->__('%s are on hold.', $this->getCustomerOnHoldPointsSummary()); ?></div>
    <?php endif; ?>
</p>
</div>
