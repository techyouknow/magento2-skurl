<?php
namespace Techyouknow\Skurl\Controller\Media;

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
        if(isset($data['sku']) && isset($data['position']) && is_numeric($data['position'])) {
            $sku = $data['sku'];
            $position = $data['position'];
            if($this->getProductImageUrlByPositionBySku($sku, $position)) {
                return $this->_redirect($this->getProductImageUrlByPositionBySku($sku, $position));
            } else {
                $this->_redirect('');
            }
        } elseif (isset($data['sku'])) {
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

    public function getProductImageUrlByPositionBySku($sku, $position)
    {
        try {
            $product = $this->productRepository->get($sku);
            $images = $product->getMediaGalleryEntries();
            $imagesCount = count($images);
            $position = $position - 1 < 0 ? 0 : $position - 1;
            if($position > $imagesCount - 1) {
                $position = $imagesCount - 1;
            }
            $imagePath = $images[$position]->getFile();
            $productImageUrl = $this->imageHelper->init($product, 'product_page_image_large')->setImageFile($imagePath)->getUrl();
            if($images[$position]->getMediaType() == 'external-video') {
                $productImageUrl = $images[$position]->getExtensionAttributes()->getVideoContent()['video_url'];
            }
    
        } catch (NoSuchEntityException $noSuchEntityException) {
            $productImageUrl = null;
        }
        return $productImageUrl;
    }

    
}