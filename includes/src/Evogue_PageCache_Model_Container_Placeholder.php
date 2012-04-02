<?php
class Evogue_PageCache_Model_Container_Placeholder {

    const HTML_NAME_PATTERN = '/<!--\{(.*?)\}-->/i';

    protected static $_definitionMap = array();

    protected $_definition;

    protected $_name;

    protected $_attributes = array();

    public function __construct($definition) {
        if ($definition && array_key_exists($definition, self::$_definitionMap)) {
            $definition = self::$_definitionMap[$definition];
        }
        $this->_definition = $definition;
        $definition     = explode(' ', $definition);
        $this->_name    = $definition[0];
        $count = count($definition);
        if ($count>1) {
            for ($i=1; $i<$count; $i++) {
                $info = explode('=', $definition[$i]);
                $this->_attributes[$info[0]] = isset($info[1]) ? trim($info[1], '"\'') : null;
            }
        }
    }

    public function getName() {
        return $this->_name;
    }

    public function getDefinition() {
        return $this->_definition;
    }

    public function getAttribute($code) {
        return isset($this->_attributes[$code]) ? $this->_attributes[$code] : null;
    }

    public function getPattern() {
        return '/' . preg_quote($this->getStartTag(), '/') . '(.*?)' . preg_quote($this->getEndTag(), '/') . '/ims';
    }

    public function getReplacer() {
        $def = $this->_definition;
        $container = $this->getAttribute('container');
        $containerClass = 'container="'.$this->getContainerClass().'"';
        $def = str_replace('container="'.$container.'"', $containerClass, $def);
        $def = str_replace('container=\''.$container.'\'', $containerClass, $def);
        return '<!--{' . $def . '}-->';
    }

    public function getContainerClass() {
        $class = $this->getAttribute('container');
        if (strpos($class, '/') !== false) {
            return Mage::getConfig()->getModelClassName($class);
        }
        return $class;
    }

    protected function _getDefinitionHash() {
        $definition = $this->getDefinition();
        $result = array_search($definition, self::$_definitionMap);
        if ($result === false) {
            $result = $this->getName() . '_' . md5($definition);
            self::$_definitionMap[$result] = $definition;
        }
        return $result;
    }

    public function getStartTag() {
        return '<!--{' . $this->_getDefinitionHash() . '}-->';
    }

    public function getEndTag() {
        return '<!--/{' . $this->_getDefinitionHash() . '}-->';
    }
}
