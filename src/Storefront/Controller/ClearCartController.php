<?php declare(strict_types=1);

namespace Swag\StorefrontController\Storefront\Controller;

use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;


class ClearCartController extends StorefrontController
{
    /**
     * @var CartService
     */
    private $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    /**
     * @RouteScope(scopes={"storefront"})
     * @Route("/cart/clear", name="frontend.checkout.clearCart", options={"seo"="false"}, methods={"GET"})
     */
    public function clearCart(SalesChannelContext $context)
    {
        $cart = $this->cartService->getCart($context->getToken(), $context);

        foreach ($cart->getLineItems() as $lineItem) {
            $this->cartService->remove($cart, $lineItem->getId(), $context);
        }

        return $this->forwardToRoute('frontend.checkout.cart.page');
    }
}
