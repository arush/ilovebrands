<?php

/**
 * This rewrites the corresponding block in TBT_Rewards to filter out the Instore rules from the grid
 */
class TBT_Rewardsinstore_Block_Rewards_Manage_Promo_Quote_Distributions_Grid extends TBT_Rewardsinstore_Block_Rewards_Manage_Promo_Quote_Grid {
    
    public function __construct() {
        parent::__construct ( TBT_Rewards_Helper_Rule_Type::DISTRIBUTION );
    }
    
}
