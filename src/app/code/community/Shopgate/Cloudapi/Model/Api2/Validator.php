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

class Shopgate_Cloudapi_Model_Api2_Validator extends Mage_Api2_Model_Resource_Validator_Eav
{
    /** @var array */
    private $detailedErrors = array();

    /**
     * @param string $error
     * @param string $attributeCode
     *
     * @return Shopgate_Cloudapi_Model_Api2_Validator
     */
    protected function addDetailedError($error, $attributeCode = 'misc')
    {
        $this->detailedErrors[$attributeCode][] = $error;

        return $this;
    }

    /**
     * @param array  $errors
     * @param string $attributeCode
     *
     * @return Shopgate_Cloudapi_Model_Api2_Validator
     */
    protected function addDetailedErrors(array $errors, $attributeCode = 'misc')
    {
        foreach ($errors as $error) {
            $this->addDetailedError($error, $attributeCode);
        }

        return $this;
    }

    /**
     * Retrieve the errors with attribute codes
     *
     * @return array
     */
    public function getDetailedErrors()
    {
        return $this->detailedErrors;
    }

    /**
     * Copied over from Mage and added detailed validation printing
     *
     * @inheritdoc
     * @throws Mage_Core_Exception
     */
    public function isValidData(array $data, $partial = false)
    {
        $errors = array();
        /** @var Mage_Eav_Model_Attribute $attribute */
        foreach ($this->_eavForm->getAttributes() as $attribute) {
            if ($partial && !array_key_exists($attribute->getAttributeCode(), $data)) {
                continue;
            }
            if ($this->_eavForm->ignoreInvisible() && !$attribute->getIsVisible()) {
                continue;
            }
            $attrValue = isset($data[$attribute->getAttributeCode()]) ? $data[$attribute->getAttributeCode()] : null;

            $result = Mage_Eav_Model_Attribute_Data::factory($attribute, $this->_eavForm->getEntity())
                                                   ->setExtractedData($data)
                                                   ->validateValue($attrValue);

            if ($result !== true) {
                /** @noinspection SlowArrayOperationsInLoopInspection */
                $errors = array_merge($errors, $result);
                /** @noinspection PhpParamsInspection */
                $this->addDetailedErrors($result, $attribute->getAttributeCode());
            } else {
                $result = $this->_validateAttributeWithSource($attribute, $attrValue);

                if (true !== $result) {
                    /** @noinspection SlowArrayOperationsInLoopInspection */
                    $errors = array_merge($errors, $result);
                    /** @noinspection PhpParamsInspection */
                    $this->addDetailedErrors($result, $attribute->getAttributeCode());
                }
            }
        }
        $this->_setErrors($errors);

        return $errors ? false : true;
    }
}
