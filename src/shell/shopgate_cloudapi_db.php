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

class Shopgate_Cloudapi_Db_Shell extends Mage_Shell_Abstract
{
    /**
     * Run SG script
     *
     * @throws Exception
     */
    public function run()
    {
        if ($db = $this->getArg('db')) {
            if ('set' === $this->getArg('action')) {
                echo (int) $this->writeDatabase($db, $this->getColumnValues());
            } elseif ('get' === $this->getArg('action')) {
                if ($this->hasLookup()) {
                    $data = $this->readDatabase($db, $this->getColumnValues());
                    echo (int) !empty($data);
                } else {
                    /** @noinspection ForgottenDebugOutputInspection */
                    echo json_encode($this->readDatabase($db));
                }
            } else {
                throw new RuntimeException('Incorrect action provided');
            }

            return;
        }

        die($this->usageHelp());
    }

    /**
     * @param string $table
     * @param array  $where
     *
     * @return array
     */
    private function readDatabase($table, array $where = array())
    {
        $connection = Mage::getModel('core/resource')->getConnection('core_read');
        /** @noinspection SqlResolve */
        $sql = "SELECT * FROM shopgate_{$table}";

        if (!empty($where)) {
            $sql .= ' WHERE ';
            foreach ($where as $col => $value) {
                $sql .= "$col=$value AND ";
            }
            $sql = rtrim($sql, ' AND');
        }

        return $connection->fetchAll($sql);
    }

    /**
     * @param string $table
     * @param array  $set
     *
     * @return array
     */
    private function writeDatabase($table, array $set)
    {
        if (empty($set)) {
            throw new RuntimeException('There were no column=value parameters provided for this call');
        }

        $connection = Mage::getModel('core/resource')->getConnection('core_write');

        return $connection->insertArray("shopgate_{$table}", array_keys($set), array($set));
    }

    /**
     * @return bool
     */
    private function hasLookup()
    {
        return count($this->_args) > 2;
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
     * Returns a list of column value pairs,
     * as long as they were provided in the right order
     *
     * @return array ('column' => 'value')
     */
    private function getColumnValues()
    {
        return array_slice($this->_args, 2);
    }

    /**
     * @return string
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f shopgate_cloudapi_db.php -- [options]
  db [db_name] action get [col] [value]    Name of SG database table, e.g 'customer' will access shopgate_customer, column value for search
  db [db_name] action set [col] [value]    Saves column/values to the DB name provided
  -h                            Short alias for help
  help                          This help
USAGE;
    }
}

$shell = new Shopgate_Cloudapi_Db_Shell();
/** @noinspection PhpUnhandledExceptionInspection */
$shell->run();
