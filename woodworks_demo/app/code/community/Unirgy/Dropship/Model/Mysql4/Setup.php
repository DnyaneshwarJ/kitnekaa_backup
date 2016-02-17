<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_Dropship_Model_Mysql4_Setup extends Mage_Index_Model_Mysql4_Setup
{
    public function createDependConfigPath($dependPath, $dependentPaths)
    {
        $targetPath = $dependPath;
        $paths = $dependentPaths;
        $paths[] = $targetPath;

        $conn = $this->_conn;

        $rawValues = $conn->fetchAll(
            $conn->select()
                ->from(array('ccd' => $this->getTable('core/config_data')), array('scope', 'scope_id', 'path', 'value'))
                ->joinLeft(array('cs'=>$this->getTable('core/store')), "cs.store_id=ccd.scope_id and ccd.scope='stores'", array('website_id'=>'cs.website_id'))
                ->where($conn->quoteInto('ccd.path in (?)', $paths))
        );

        $groupedValues = array();
        foreach ($rawValues as $rvRow) {
            $groupKey = implode('-', array($rvRow['scope'], $rvRow['scope_id']));
            $groupedValues[$groupKey][$rvRow['path']] = $rvRow;
            if ($rvRow['scope'] == 'stores') {
                $websiteGKs[$groupKey] = 'websites-'.$rvRow['website_id'];
            }
        }
        $extendedValues = array();
        $defaultGK = 'default-0';
        foreach ($groupedValues as $groupKey=>$gvRows) {
            reset($gvRows);
            $_rowTpl = current($gvRows);
            $websiteGK = isset($websiteGKs[$groupKey]) ? $websiteGKs[$groupKey] : null;
            foreach ($paths as $path) {
                $extendedValueFlag = $extendedValue = false;
                if (!isset($gvRows[$path])) {
                    if (0 === strpos($groupKey, 'stores-')) {
                        if ($websiteGK && isset($groupedValues[$websiteGK][$path])) {
                            $extendedValue = $groupedValues[$websiteGK][$path]['value'];
                            $extendedValueFlag = true;
                        } elseif (isset($groupedValues[$defaultGK][$path])) {
                            $extendedValue = $groupedValues[$defaultGK][$path]['value'];
                            $extendedValueFlag = true;
                        }
                    } elseif (0 === strpos($groupKey, 'websites-')) {
                        if (isset($groupedValues[$defaultGK][$path])) {
                            $extendedValue = $groupedValues[$defaultGK][$path]['value'];
                            $extendedValueFlag = true;
                        }
                    }
                } else {
                    $extendedValue = $gvRows[$path]['value'];
                    $extendedValueFlag = true;
                }
                if ($extendedValueFlag) {
                    $extendedValues[$groupKey][$path] = $_rowTpl;
                    $extendedValues[$groupKey][$path]['path'] = $path;
                    $extendedValues[$groupKey][$path]['value'] = $extendedValue;
                }
            }
        }

        $rowsToInsert = array();
        foreach ($extendedValues as $groupKey => $evRow) {
            $valueToInsert = false;
            foreach ($paths as $path) {
                if (isset($evRow[$path])) {
                    $_value = $evRow[$path]['value'];
                    $_value = preg_replace('/^[\s,]/', '', $_value);
                    $_value = preg_replace('/[\s,]$/', '', $_value);
                    $valueToInsert = $valueToInsert || $_value;
                }
            }
            if (!empty($evRow)) {
                reset($evRow);
                $rowsToInsert[$groupKey] = current($evRow);
                $rowsToInsert[$groupKey]['value'] = $valueToInsert ? 1 : 0;
            }
        }
        $_rowsToInsert = array();
        foreach ($rowsToInsert as $groupKey => $rowToInsert) {
            $websiteGK = $doInsert = false;
            if ('stores' == $rowToInsert['scope']) {
                $websiteGK = isset($websiteGKs[$groupKey]) ? $websiteGKs[$groupKey] : null;
                if ($websiteGK && isset($rowsToInsert[$websiteGK])) {
                    if ($rowsToInsert[$websiteGK]['value'] != $rowToInsert['value']) {
                        $doInsert = true;
                    }
                } elseif (isset($rowsToInsert[$defaultGK])) {
                    if ($rowsToInsert[$defaultGK]['value'] != $rowToInsert['value']) {
                        $doInsert = true;
                    }
                } else {
                    $doInsert = true;
                }
            } elseif ('websites' == $rowToInsert['scope']) {
                if (isset($rowsToInsert[$defaultGK])) {
                    if ($rowsToInsert[$defaultGK]['value'] != $rowToInsert['value']) {
                        $doInsert = true;
                    }
                } else {
                    $doInsert = true;
                }
            } elseif ('default' == $rowToInsert['scope']) {
                $doInsert = true;
            }
            $doInsert = $doInsert && !isset($extendedValues[$groupKey][$targetPath]);
            if ($doInsert) {
                $_rowsToInsert[$groupKey] = $rowToInsert;
            }
        }

        if (!empty($_rowsToInsert)) {
            $sqlKeys = array('scope','scope_id','path','value');
            $_rowsSql = array();
            foreach ($_rowsToInsert as $groupKey => $_rowToInsert) {
                $_rowToInsert['path'] = $targetPath;
                $_rowSql = array();
                foreach ($sqlKeys as $sqlKey) {
                    $_rowSql[] = $_rowToInsert[$sqlKey];
                }
                $_rowsSql[] = $conn->quote($_rowSql);
            }
            $conn->query(sprintf(
                "insert ignore into %s (%s) values (%s)",
                $conn->quoteIdentifier($this->getTable('core/config_data')),
                implode(',', array_map(array($conn, 'quoteIdentifier'), $sqlKeys)),
                implode('),(', $_rowsSql)
            ));
        }
    }
}