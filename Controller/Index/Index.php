<?php
namespace Techyouknow\Skurl\Controller\Index;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $request;
    protected $productRepository;
    protected $_messageManager;
    protected $cart;
    protected $configurable;
    protected $grouped;
    private $storeManager;
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        ProductRepositoryInterface $productRepository,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Magento\GroupedProduct\Model\Product\Type\Grouped $grouped,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ){
        
		parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->request = $request;
        $this->_messageManager = $messageManager;
        $this->productRepository = $productRepository;
        $this->cart = $cart;
        $this->configurable = $configurable;
        $this->grouped = $grouped;
        $this->storeManager = $storeManager;
	}

	public function execute()
	{
		$data = $this->request->getParams();
        if (isset($data['sku'])) {
            $sku = $data['sku'];
            if($this->getProductUrlBySku($sku)) {
                if(isset($data['qty']) && $data['qty'] > 0) {
                    $product = $this->productRepository->get($sku);
                    $child = $product;
                    $childId = $product->getId();
                    $parentId = $this->getParentId($childId);
                    $parent = $this->productRepository->getById($parentId);
                    $qty = $data['qty'];
                    $minQty = $product->getData('custom_min_qty');
                    if($qty < $minQty) {
                       $qty = $minQty?$minQty:1;
                    }
                    try {

                        $params = array();
                        $params['qty'] = $qty;
                        $params['product'] = $parentId;
                        if($this->getParentProductType($childId) == 'configurable') {
                            $options = [];
                            $productAttributeOptions = $parent->getTypeInstance(true)->getConfigurableAttributesAsArray($parent);
                            foreach ($productAttributeOptions as $option) {
                                $options[$option['attribute_id']] = $child->getData($option['attribute_code']);
                            }
                            $params['super_attribute'] = $options;
                            $product = $parent;
                        } elseif($this->getParentProductType($childId) == 'grouped') {
                            $super_group = [];
                            $children = [];
                            $groupedProduct = $parent;                       
                            $_associatedProducts = $groupedProduct->getTypeInstance()->getAssociatedProducts($groupedProduct);
                            $_hasAssociatedProducts = count($_associatedProducts) > 0;
                            if ($_hasAssociatedProducts) {
                                foreach ($_associatedProducts as $_item) {
                                    $children[] = $_item->getId();
                                }
                            }
                            if(count($children) > 0) {
                                foreach($children as $childProductId){
                                    if(intval($childProductId)){
                                        if($childProductId == $childId) {
                                            $super_group[$childProductId] = $qty;
                                        } else {
                                            $super_group[$childProductId] = 0;
                                        }
                                    }
                                }
                            }
                            $params['super_group'] = $super_group;
                            $product = $parent;
                        }
                        //print_r($params);die;
                        $this->cart->addProduct($product, $params);
                        $this->cart->save();
                        $this->messageManager->addSuccess(__('Added to cart successfully.'));
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                        $this->messageManager->addException(
                            $e,
                            __('%1', $e->getMessage())
                        );
                    } catch (\Exception $e) {
                        $this->messageManager->addException($e, __('error.'));
                    }
                    $this->getResponse()->setRedirect('/checkout/cart/index');
                    
                } else {
                    $this->_redirect($this->getProductUrlBySku($sku));
                }
            } else {
                $message = 'Product "'.$sku.'" Not Found';
                $this->_messageManager->addError($message);
                $this->_redirect('');
            }
        } else {
            $this->_redirect('');
        }
	}

    public function getProductUrlBySku($sku): ?string
    {
        try {
            //$productUrl = $this->productRepository->get($sku)->getProductUrl();
            $childId = $this->productRepository->get($sku)->getId();
            $parentId = $this->getParentId($childId);
            $parentSku = $this->productRepository->getById($parentId)->getSku();
            $productUrl = $this->productRepository->get($parentSku)->getProductUrl();
    
        } catch (NoSuchEntityException $noSuchEntityException) {
            $productUrl = null;
        }
        return $productUrl;
    }

    public function getParentId($childId){
        $currentWebsiteId = $this->storeManager->getStore()->getWebsiteId();
        $parentIds = $this->grouped->getParentIdsByChild($childId);
        if(count($parentIds) > 0){
            foreach ($parentIds as $parentId) {
                if(isset($parentId)){
                    $websiteIds = $this->productRepository->getById($parentId)->getWebsiteIds();
                    if(in_array($currentWebsiteId, $websiteIds)){
                        return $parentId;
                    }
                }
            }
        } else {
            if(isset($parentIds[0])){
                return $parentIds[0];
            }
        }

        /* for simple product of configurable product */
        $parentProduct = $this->configurable->getParentIdsByChild($childId);
        if(count($parentProduct) > 0){
            foreach ($parentProduct as $parentProductId) {
                if(isset($parentProductId)){
                    $websiteIds = $this->productRepository->getById($parentProductId)->getWebsiteIds();
                    if(in_array($currentWebsiteId, $websiteIds)){
                        return $parentProductId;
                    }
                }
            }
        } else {
            if(isset($parentIds[0])){
                return $parentIds[0];
            }
        }
       
        return $childId;
    }

    public function getParentProductType($childId) {
        $parentIds = $this->grouped->getParentIdsByChild($childId);
        if(isset($parentIds[0])){
            return 'grouped';
        }

        $parentProduct = $this->configurable->getParentIdsByChild($childId);
        if(isset($parentProduct[0])){
            return 'configurable';
        }

        return 'simple';
    }
}