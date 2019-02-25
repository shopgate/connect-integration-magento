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
/** @noinspection PhpIncludeInspection */

require_once __DIR__ . '/abstract.php';

/** @noinspection AutoloadingIssuesInspection */

class Shopgate_Cloudapi_Config_Shell extends Mage_Shell_Abstract
{
    /**
     * Run SG script
     *
     * @throws Exception
     */
    public function run()
    {
        $configValue = $this->getArg('value');
        $path = $this->getArg('config');
        if ($path  && $configValue !== false) {
            Mage::getModel('core/config')->saveConfig($path, $configValue);
            return;
        }

        die($this->usageHelp());
    }

    /**
     * Parse input arguments
     *
     * @return Mage_Shell_Abstract
     */
    protected function _parseArgs()
    {
        if ($_SERVER['argv'][1] === 'help') {
            $this->_args['help'] = true;

            return $this;
        }
        if (count($_SERVER['argv']) % 2 === 0) {
            throw new RuntimeException('Need to have an even amount of parameters passed to this shell');
        }

        $size = count($_SERVER['argv']) - 1;
        for ($i = 1; $i <= $size; $i += 2) {
            $value1               = $_SERVER['argv'][$i];
            $value2               = $_SERVER['argv'][$i + 1];
            $this->_args[$value1] = $value2;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f shopgate_cloudapi.php -- [options]
  config [STRING] value [STRING]    Injects a config specified by path and value.
  help                              This help
USAGE;
    }
}

$shell = new Shopgate_Cloudapi_Config_Shell();
$shell->run();
