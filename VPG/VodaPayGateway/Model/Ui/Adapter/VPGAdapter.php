<?php

namespace VPG\VodaPayGateway\Model\Adapter;

use Magento\Framework\Module\ModuleListInterface;
use VPG\VodaPayGateway\Gateway\Config\Config;

class VPGAdapter 
{
    /**
     * @var Config
     */
    private $config;
    
    /**
     * @var ModuleListInterface
     */
    private $moduleList;

    /**
     * @param Config $config
     * @param ModuleListInterface $moduleList
     */
    public function __construct(
        Config $config,
        ModuleListInterface $moduleList
    ) {
        $this->config = $config;
        $this->moduleList = $moduleList;
        
        $this->initCredentials();
    }
}