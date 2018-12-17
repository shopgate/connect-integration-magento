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

class Shopgate_Cloudapi_Model_Api2_Customers_Validator extends Shopgate_Cloudapi_Model_Api2_Validator
{
    /**
     * @var int
     */
    protected $websiteId;

    /**
     * For some reason password is being filtered out
     * when it is not supposed to be. We just bring it back.
     *
     * @param array $data
     *
     * @return array
     */
    public function filter(array $data)
    {
        $newData = parent::filter($data);
        if (isset($data['password'])) {
            $newData['password'] = $data['password'];
        }

        if (isset($this->websiteId) && !isset($data['website_id'])) {
            $newData['website_id'] = $this->websiteId;
        }

        if (!isset($data['group_id'])) {
            $groupId             = Mage::getStoreConfig(Mage_Customer_Model_Group::XML_PATH_DEFAULT_ID);
            $newData['group_id'] = $groupId ? : 1;
        }

        return $newData;
    }

    /**
     * @param int $websiteId
     *
     * @return Shopgate_Cloudapi_Model_Api2_Customers_Validator
     */
    public function setWebsiteId($websiteId)
    {
        $this->websiteId = $websiteId;

        return $this;
    }

}
