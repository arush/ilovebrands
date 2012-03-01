<?php

$installer = $this;

$installer->startSetup();

$blockId = $installer->getConnection()->fetchOne($installer->getConnection()->select()
    ->from($this->getTable('cms/block'), 'block_id')
    ->where('identifier = ?', 'catalog_events_lister'));

if ($blockId) {
    $installer->getConnection()->delete(
        $this->getTable('cms/block_store'),
        $installer->getConnection()->quoteInto('block_id = ?', $blockId)
    );

    $installer->getConnection()->insert(
        $this->getTable('cms/block_store'),
        array(
            'block_id' => $blockId,
            'store_id' => 0
        )
    );
}

$installer->endSetup();
