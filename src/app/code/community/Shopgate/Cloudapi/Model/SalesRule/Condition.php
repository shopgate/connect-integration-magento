<?php

/**
 * Copyright Shopgate Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    Shopgate Inc, 804 Congress Ave, Austin, Texas 78701 <interfaces@shopgate.com>
 * @copyright Shopgate Inc
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

class Shopgate_Cloudapi_Model_SalesRule_Condition extends Mage_Rule_Model_Condition_Abstract
{
    /**
     * Define identifier for cart type
     */
    const CART_TYPE = 'shopgate_cart_type';

    /**
     * Define identifier for select
     */
    const DEFAULT_IDENTIFIER_SELECT = 'select';

    /**
     * @return Shopgate_Cloudapi_Model_SalesRule_Condition
     */
    public function loadAttributeOptions()
    {
        $this->setData('attribute_option',
            array(
                self::CART_TYPE => Mage::helper('shopgate_cloudapi')->__('Shopgate Cart Type')
            )
        );

        return $this;
    }

    /**
     * Makes the label "Shopgate Cart Type" show as text
     * instead of a dropdown
     *
     * @return mixed
     */
    public function getAttributeElement()
    {
        /** @var Varien_Data_Form_Element_Select $element */
        $element = parent::getAttributeElement();
        $element->setData('show_as_text', true);

        return $element;
    }

    /**
     * @return string
     */
    public function getInputType()
    {
        if ($this->getData('attribute') === self::CART_TYPE) {
            return self::DEFAULT_IDENTIFIER_SELECT;
        }

        return parent::getInputType();
    }

    /**
     * @return string
     */
    public function getValueElementType()
    {
        if ($this->getData('attribute') === self::CART_TYPE) {
            return self::DEFAULT_IDENTIFIER_SELECT;
        }

        return parent::getValueElementType();
    }

    /**
     * @return mixed
     */
    public function getValueSelectOptions()
    {
        if (!$this->hasData('value_select_options')) {
            $options = $this->getData('attribute') === self::CART_TYPE
                ? Mage::getModel('shopgate_cloudapi/system_config_source_cart_types')->toOptionArray()
                : array();
            $this->setData('value_select_options', $options);
        }

        return $this->getData('value_select_options');
    }
}
