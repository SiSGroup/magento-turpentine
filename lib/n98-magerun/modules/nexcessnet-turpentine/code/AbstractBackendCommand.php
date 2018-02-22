<?php

namespace Nexcessnet\Turpentine\Command\Varnish;

use N98\Magento\Command\AbstractMagentoCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;

class AbstractBackendCommand extends AbstractMagentoCommand
{
    const BACKEND_NODES_CONFIG_SCOPE    = 'default';
    const BACKEND_NODES_CONFIG_SCOPE_ID = 0;
    const BACKEND_NODES_CONFIG_PATH     = 'turpentine_vcl/backend/backend_nodes';
    const BACKEND_NODES_CONFIG_EVENT    = 'admin_system_config_changed_section_turpentine_vcl';

    /**
     * @param boolean $cached
     * @return array
     */
    protected function _getBackendNodes($cached = false)
    {
        if ($cached) {
            return $this->_cleanExplode( \Mage::getStoreConfig(self::BACKEND_NODES_CONFIG_PATH) );
        }

        /* @var $collection \Mage_Core_Model_Resource_Db_Collection_Abstract */
        $collection = $this->_getConfigDataModel()->getCollection();
        $collection->addFieldToFilter('path',     array('like' => self::BACKEND_NODES_CONFIG_PATH));
        $collection->addFieldToFilter('scope',    array('eq' => self::BACKEND_NODES_CONFIG_SCOPE));
        $collection->addFieldToFilter('scope_id', array('eq' => self::BACKEND_NODES_CONFIG_SCOPE_ID));

        $count = $collection->count();
        if ($count > 1) {
            throw new \LogicException('More than one config value for specific scope ('.self::BACKEND_NODES_CONFIG_SCOPE.'), scope-id ('.self::BACKEND_NODES_CONFIG_SCOPE_ID.'), and path ('.self::BACKEND_NODES_CONFIG_PATH.')');
        }

        $nodes = "";
        foreach ($collection as $item) {
            $nodes = $item->getValue();
            break;
        }

        return $this->_cleanExplode($nodes);
    }

    /**
     * @param array $nodes
     * @param boolean $cleanCache
     * @return bool
     */
    protected function _setBackendNodes($nodes, $cleanCache = true, $sendEvent = true)
    {
        $config = $this->_getConfigModel();
        if (!$config->getResourceModel()) {
            return false;
        }

        $value = join("\r\n", $nodes);

        $config->saveConfig(
            self::BACKEND_NODES_CONFIG_PATH,
            $value,
            self::BACKEND_NODES_CONFIG_SCOPE,
            self::BACKEND_NODES_CONFIG_SCOPE_ID
        );

        if ($cleanCache) {
            $config->cleanCache();
            if ($sendEvent) {
                \Mage::app()->loadAreaPart('global', 'events');
                \Mage::dispatchEvent(self::BACKEND_NODES_CONFIG_EVENT);
            }
        }

        return true;
    }

    /**
      * @return \Mage_Core_Model_Abstract
      */
    protected function _getConfigDataModel()
    {
        return $this->_getModel('core/config_data', 'Mage_Core_Model_Config_Data');
    }

    /**
     * @return \Mage_Core_Model_Config
     */
    protected function _getConfigModel()
    {
        return $this->_getModel('core/config', 'Mage_Core_Model_Config');
    }

    /**
     * @param string $string
     * @return array
     */
    private function _cleanExplode($string = "")
    {
        if (empty($string)) {
            return array();
        }
        return \Mage::helper('turpentine/data')->cleanExplode(PHP_EOL, $string);
    }
}
