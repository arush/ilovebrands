<?php
class Evogue_CatalogEvent_Block_Adminhtml_Event_Grid_Column_Renderer_Bitmask
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text {
    public function render(Varien_Object $row)
    {
        $value = (int) $row->getData($this->getColumn()->getIndex());
        $result = array();
        foreach ($this->getColumn()->getOptions() as $option=>$label) {
            if (($value & $option) == $option) {
                $result[] = $label;
            }
        }

        return $this->htmlEscape(implode(', ', $result));
    }
}
