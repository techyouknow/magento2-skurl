<?php
namespace Techyouknow\Skurl\Controller;


class Router implements \Magento\Framework\App\RouterInterface
{
    /**
     * @var \Magento\Framework\App\ActionFactory
     */
    protected $actionFactory;

    /**
     * Response
     *
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;
    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\ActionFactory $actionFactory
     * @param \Magento\Framework\App\ResponseInterface $response
     */
    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
        \Magento\Framework\App\ResponseInterface $response
    ){
        $this->actionFactory = $actionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->_response = $response;
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface|void
     */
    public function match(\Magento\Framework\App\RequestInterface $request)
    {
        $identifier = trim($request->getPathInfo() , '/');
        if ($request->getModuleName() === 'techyouknowskurl') {
            return;
        }
        if ($this->isModuleEnabled() && strpos($identifier, $this->getCustomRouterName()) !== false)
        {
            $request->setModuleName('techyouknowskurl');
        }
        else
        {
            return;
        }

        return $this
            ->actionFactory
            ->create('Magento\Framework\App\Action\Forward', ['request' => $request]);
    }

    public function isModuleEnabled() {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('techyouknowskurl/general/enable', $storeScope);
    }

    public function getCustomRouterName() {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue('techyouknowskurl/general/cutomrouter', $storeScope);
    }
}