<?php
class Evogue_Customer_Helper_Data extends Mage_Core_Helper_Abstract {

    protected $_userDefinedAttributeCodes = array();

    public function getAttributeFormTypeIds($attribute) {
        $types = Mage::getResourceModel('eav/form_type')
            ->getFormTypesByAttribute($attribute);
        $typesIds = array();
        foreach ($types as $type) {
            $typesIds[] = $type['type_id'];
        }
        return $typesIds;
    }

    public function getCustomerAttributeFormOptions() {
        return array(
            array(
                'label' => Mage::helper('evogue_customer')->__('Customer Checkout Register'),
                'value' => 'checkout_register'
            ),
            array(
                'label' => Mage::helper('evogue_customer')->__('Customer Registration'),
                'value' => 'customer_account_create'
            ),
            array(
                'label' => Mage::helper('evogue_customer')->__('Customer Account Edit'),
                'value' => 'customer_account_edit'
            ),
            array(
                'label' => Mage::helper('evogue_customer')->__('Admin Checkout'),
                'value' => 'adminhtml_checkout'
            ),
        );
    }

    public function getCustomerAddressAttributeFormOptions() {
        return array(
            array(
                'label' => Mage::helper('evogue_customer')->__('Customer Address Registration'),
                'value' => 'customer_register_address'
            ),
            array(
                'label' => Mage::helper('evogue_customer')->__('Customer Account Address'),
                'value' => 'customer_address_edit'
            ),
        );
    }

    public function getAttributeInputTypes($inputType = null) {
        $inputTypes = array(
            'text'          => array(
                'label'             => Mage::helper('evogue_customer')->__('Text Field'),
                'manage_options'    => false,
                'validate_types'    => array(
                    'min_text_length',
                    'max_text_length',
                ),
                'validate_filters'  => array(
                    'alphanumeric',
                    'numeric',
                    'alpha',
                    'url',
                    'email',
                ),
                'filter_types'      => array(
                    'striptags',
                    'escapehtml'
                ),
                'backend_type'      => 'varchar',
                'default_value'     => 'text',
            ),
            'textarea'      => array(
                'label'             => Mage::helper('evogue_customer')->__('Text Area'),
                'manage_options'    => false,
                'validate_types'    => array(
                    'min_text_length',
                    'max_text_length',
                ),
                'validate_filters'  => array(),
                'filter_types'      => array(
                    'striptags',
                    'escapehtml'
                ),
                'backend_type'      => 'text',
                'default_value'     => 'textarea',
            ),
            'multiline'     => array(
                'label'             => Mage::helper('evogue_customer')->__('Multiple Line'),
                'manage_options'    => false,
                'validate_types'    => array(
                    'min_text_length',
                    'max_text_length',
                ),
                'validate_filters'  => array(
                    'alphanumeric',
                    'numeric',
                    'alpha',
                    'url',
                    'email',
                ),
                'filter_types'      => array(
                    'striptags',
                    'escapehtml'
                ),
                'backend_type'      => 'text',
                'default_value'     => 'text',
            ),
            'date'          => array(
                'label'             => Mage::helper('evogue_customer')->__('Date'),
                'manage_options'    => false,
                'validate_types'    => array(),
                'validate_filters'  => array(
                    'date'
                ),
                'filter_types'      => array(
                    'date'
                ),
                'backend_model'     => 'eav/entity_attribute_backend_datetime',
                'backend_type'      => 'datetime',
                'default_value'     => 'date',
            ),
            'select'        => array(
                'label'             => Mage::helper('evogue_customer')->__('Dropdown'),
                'manage_options'    => true,
                'option_default'    => 'radio',
                'validate_types'    => array(),
                'validate_filters'  => array(),
                'filter_types'      => array(),
                'source_model'      => 'eav/entity_attribute_source_table',
                'backend_type'      => 'int',
                'default_value'     => false,
            ),
            'multiselect'   => array(
                'label'             => Mage::helper('evogue_customer')->__('Multiple Select'),
                'manage_options'    => true,
                'option_default'    => 'checkbox',
                'validate_types'    => array(),
                'filter_types'      => array(),
                'validate_filters'  => array(),
                'backend_model'     => 'eav/entity_attribute_backend_array',
                'source_model'      => 'eav/entity_attribute_source_table',
                'backend_type'      => 'varchar',
                'default_value'     => false,
            ),
            'boolean'       => array(
                'label'             => Mage::helper('evogue_customer')->__('Yes/No'),
                'manage_options'    => false,
                'validate_types'    => array(),
                'validate_filters'  => array(),
                'filter_types'      => array(),
                'source_model'      => 'eav/entity_attribute_source_boolean',
                'backend_type'      => 'int',
                'default_value'     => 'yesno',
            ),
            'file'          => array(
                'label'             => Mage::helper('evogue_customer')->__('File (attachment)'),
                'manage_options'    => false,
                'validate_types'    => array(
                    'max_file_size',
                    'file_extensions'
                ),
                'validate_filters'  => array(),
                'filter_types'      => array(),
                'backend_type'      => 'varchar',
                'default_value'     => false,
            ),
            'image'         => array(
                'label'             => Mage::helper('evogue_customer')->__('Image File'),
                'manage_options'    => false,
                'validate_types'    => array(
                    'max_file_size',
                    'max_image_width',
                    'max_image_heght',
                ),
                'validate_filters'  => array(),
                'filter_types'      => array(),
                'backend_type'      => 'varchar',
                'default_value'     => false,
            ),
        );

        if (is_null($inputType)) {
            return $inputTypes;
        } else if (isset($inputTypes[$inputType])) {
            return $inputTypes[$inputType];
        }
        return array();
    }

    public function getFrontendInputOptions() {
        $inputTypes = $this->getAttributeInputTypes();
        $options    = array();
        foreach ($inputTypes as $k => $v) {
            $options[] = array(
                'value' => $k,
                'label' => $v['label']
            );
        }

        return $options;
    }

    public function getAttributeValidateFilters() {
        return array(
            'alphanumeric'  => Mage::helper('evogue_customer')->__('Alphanumeric'),
            'numeric'       => Mage::helper('evogue_customer')->__('Numeric Only'),
            'alpha'         => Mage::helper('evogue_customer')->__('Alpha Only'),
            'url'           => Mage::helper('evogue_customer')->__('URL'),
            'email'         => Mage::helper('evogue_customer')->__('Email'),
            'date'          => Mage::helper('evogue_customer')->__('Date'),
        );
    }

    public function getAttributeFilterTypes() {
        return array(
            'striptags'     => Mage::helper('evogue_customer')->__('Strip HTML Tags'),
            'escapehtml'    => Mage::helper('evogue_customer')->__('Escape HTML Entities'),
            'date'          => Mage::helper('evogue_customer')->__('Normalize Date')
        );
    }

    public function getAttributeElementScopes() {
        return array(
            'is_required'            => 'website',
            'is_visible'             => 'website',
            'multiline_count'        => 'website',
            'default_value_text'     => 'website',
            'default_value_yesno'    => 'website',
            'default_value_date'     => 'website',
            'default_value_textarea' => 'website'
        );
    }

    public function getAttributeDefaultValueByInput($inputType) {
        $inputTypes = $this->getAttributeInputTypes();
        if (isset($inputTypes[$inputType])) {
            $value = $inputTypes[$inputType]['default_value'];
            if ($value) {
                return 'default_value_' . $value;
            }
        }
        return false;
    }

    public function getAttributeValidateRules($inputType, array $data) {
        $inputTypes = $this->getAttributeInputTypes();
        $rules      = array();
        if (isset($inputTypes[$inputType])) {
            foreach ($inputTypes[$inputType]['validate_types'] as $validateType) {
                if (!empty($data[$validateType])) {
                    $rules[$validateType] = $data[$validateType];
                }
            }
            if (!empty($inputTypes[$inputType]['validate_filters']) && !empty($data['input_validation'])) {
                if (in_array($data['input_validation'], $inputTypes[$inputType]['validate_filters'])) {
                    $rules['input_validation'] = $data['input_validation'];
                }
            }
        }
        return $rules;
    }

    public function getAttributeBackendModelByInputType($inputType) {
        $inputTypes = $this->getAttributeInputTypes();
        if (!empty($inputTypes[$inputType]['backend_model'])) {
            return $inputTypes[$inputType]['backend_model'];
        }
        return null;
    }

    public function getAttributeSourceModelByInputType($inputType) {
        $inputTypes = $this->getAttributeInputTypes();
        if (!empty($inputTypes[$inputType]['source_model'])) {
            return $inputTypes[$inputType]['source_model'];
        }
        return null;
    }

    public function getAttributeBackendTypeByInputType($inputType) {
        $inputTypes = $this->getAttributeInputTypes();
        if (!empty($inputTypes[$inputType]['backend_type'])) {
            return $inputTypes[$inputType]['backend_type'];
        }
        return null;
    }

    protected function _getUserDefinedAttributeCodes($entityTypeCode) {
        if (empty($this->_userDefinedAttributeCodes[$entityTypeCode])) {
            $this->_userDefinedAttributeCodes[$entityTypeCode] = array();
            $config = Mage::getSingleton('eav/config');
            foreach ($config->getEntityAttributeCodes($entityTypeCode) as $attributeCode) {
                $attribute = $config->getAttribute($entityTypeCode, $attributeCode);
                if ($attribute && $attribute->getIsUserDefined()) {
                    $this->_userDefinedAttributeCodes[$entityTypeCode][] = $attributeCode;
                }
            }
        }
        return $this->_userDefinedAttributeCodes[$entityTypeCode];
    }

    public function getCustomerUserDefinedAttributeCodes() {
        return $this->_getUserDefinedAttributeCodes('customer');
    }

    public function getCustomerAddressUserDefinedAttributeCodes() {
        return $this->_getUserDefinedAttributeCodes('customer_address');
    }
}
