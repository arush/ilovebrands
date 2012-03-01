<?php

require_once ('app/code/community/TBT/Rewardsinstore/controllers/RewardsinstoreController.php');
class TBT_Rewardsinstore_Manage_TodoController extends TBT_Rewardsinstore_RewardsinstoreController {

    protected function _initAction() {
        $this->loadLayout()
            ->_setActiveMenu('rewards/instore');

        return $this;
    }
    
    public function indexAction()
    {
        $this->loadLayout();
        
        $block = $this->_initAction()
            ->getLayout()
            ->createBlock('core/text')
            ->setText("<h1>Feature coming soon!</h1>");           
        $this->_addContent($block);
        
        $this->renderLayout();
    }
}
    
?>