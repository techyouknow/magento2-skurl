<?php
namespace Techyouknow\Skurl\Controller\Image;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Helper\Image;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $request;
    protected $productRepository;
    private $storeManager;
    protected $imageHelper;
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\App\RequestInterface $request,
        ProductRepositoryInterface $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Image $imageHelper
    ){
        
		parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->request = $request;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
        $this->imageHelper = $imageHelper;
	}

	public function execute()
	{
		$data = $this->request->getParams();
        if (isset($data['sku'])) {
            $sku = $data['sku'];
            if($this->getProductImageUrlBySku($sku)) {
                $this->_redirect($this->getProductImageUrlBySku($sku));
            } else {
                $this->_redirect('');
            }
        } else {
            $this->_redirect('');
        }
	}

    public function getProductImageUrlBySku($sku): ?string
    {
        try {
            $product = $this->productRepository->get($sku);
            $productImageUrl = $this->imageHelper->init($product, 'product_page_image_large')->getUrl();
    
        } catch (NoSuchEntityException $noSuchEntityException) {
            $productImageUrl = null;
        }
        return $productImageUrl;
    }

    
}