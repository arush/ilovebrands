<?php

class Wyomind_Simplegoogleshopping_Block_Adminhtml_Simplegoogleshopping extends Mage_Adminhtml_Block_Widget_Grid_Container
{

    public function __construct()
    {
     //on indique ou va se trouver le controller
     $this->_controller = 'adminhtml_simplegoogleshopping';
	 $this->_blockGroup = 'simplegoogleshopping';
     //le texte du header qui s�affichera dans l�admin
     $this->_headerText = 'Google Shopping';
     //le nom du bouton pour ajouter une un contact
     $this->_addButtonLabel = $this->__('Create a new data feed');
     parent::__construct();
     }
}