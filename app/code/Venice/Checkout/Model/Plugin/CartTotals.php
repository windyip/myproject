<?php

namespace Venice\Checkout\Model\Plugin;

use Magento\Checkout\Block\Cart\CartTotalsProcessor;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Checkout\Model\Cart\Interceptor;

class CartTotals
{

    protected $cartRepository;
    protected $productRepository;
    protected $cart;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        ProductRepository $productRepository,
        Interceptor $cart
    ) {
        $this->cartRepository = $cartRepository;
        $this->productRepository = $productRepository;
        $this->cart = $cart;
    }


    public function afterProcess(CartTotalsProcessor $subject, array $jsLayout)
    {

        $quote = $this->cartRepository->get($this->cart->getQuote()->getEntityId());
        $items = $quote->getItems();
        $totalRetailPrice = 0;
        foreach ($items as $item){
            $product = $this->productRepository->getById($item->getProductId());
            if (!$product->getRetailPrice())
                $totalRetailPrice += intval($product->getPrice()) * ($item->getQty());
            else
                $totalRetailPrice += intval($product->getRetailPrice()) * ($item->getQty());
        }
        $subtotal = $quote->getSubtotal();
        if($subtotal > 0 && $totalRetailPrice >0){
            $saved = (int) 100 - intval(($subtotal/$totalRetailPrice) * 100);

        }else{
            $saved = 0 ;
        }

        if ($saved > 0){
            $jsLayout['components']['block-totals']['children']['save']['config']['componentDisabled'] = false;
            $jsLayout['components']['block-totals']['children']['save']['sortOrder']= 15;
            $jsLayout['components']['block-totals']['children']['save']['config']['value'] = $saved."%";;
        }



        return $jsLayout;



    }

}