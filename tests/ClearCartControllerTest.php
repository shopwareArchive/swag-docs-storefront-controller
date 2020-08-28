<?php declare(strict_types=1);

namespace Swag\ExtendJsPlugin\tests;

use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Cart\LineItem\LineItem;
use Shopware\Core\Checkout\Cart\SalesChannel\CartService;
use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;
use Shopware\Core\Framework\Test\TestCaseBase\SalesChannelApiTestBehaviour;
use Shopware\Core\PlatformRequest;
use Shopware\Core\SalesChannelRequest;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextFactory;
use Shopware\Storefront\Framework\Routing\RequestTransformer;
use Shopware\Storefront\Test\Controller\StorefrontControllerTestBehaviour;
use Swag\StorefrontController\Storefront\Controller\ClearCartController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class ClearCartControllerTest extends TestCase
{
    use IntegrationTestBehaviour;
    use StorefrontControllerTestBehaviour;
    use SalesChannelApiTestBehaviour;

    public function testClearCart(): void
    {
        $token = 'test-sales-channel-token';
        $productId = 'cdea64f678da42b1b36c8cccfb247829';

        $salesChannel = $this->createSalesChannel();
        $salesChannelContext = $this->getContainer()->get(SalesChannelContextFactory::class)->create($token, $salesChannel['id']);

        $this->createProduct($productId, $salesChannelContext->getContext());

        /** @var CartService $cartService */
        $cartService = $this->getContainer()->get(CartService::class);
        $cartService->createNew($token);

        $productLineItem = new LineItem($productId, LineItem::PRODUCT_LINE_ITEM_TYPE);
        $productLineItem->setRemovable(true);

        $cart = $cartService->getCart($token, $salesChannelContext);
        $cart->add($productLineItem);

        $request = new Request();
        $request->attributes->set(SalesChannelRequest::ATTRIBUTE_IS_SALES_CHANNEL_REQUEST, true);
        $request->attributes->set(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_ID, $salesChannel['id']);
        $request->attributes->set(PlatformRequest::ATTRIBUTE_SALES_CHANNEL_CONTEXT_OBJECT, $salesChannelContext);
        $request->attributes->set(RequestTransformer::STOREFRONT_URL, 'localhost');

        /** @var RequestStack $requestStack */
        $requestStack = $this->getContainer()->get('request_stack');
        $requestStack->push($request);

        static::assertCount(1, $cart->getLineItems());

        $this->getController($cartService)->clearCart($salesChannelContext);
        $cart = $cartService->getCart($token, $salesChannelContext);

        static::assertCount(0, $cart->getLineItems());
    }

    private function createProduct(string $productId, Context $context): void
    {
        $product = [
            'id' => $productId,
            'name' => 'Test product',
            'productNumber' => '123456789',
            'stock' => 10,
            'price' => [
                ['currencyId' => Defaults::CURRENCY, 'gross' => 11.90, 'net' => 10, 'linked' => false],
            ],
            'manufacturer' => ['id' => $productId, 'name' => 'shopware AG'],
            'tax' => ['id' => $this->getValidTaxId(), 'name' => 'testTaxRate', 'taxRate' => 19],
            'categories' => [
                ['id' => $productId, 'name' => 'Test category'],
            ],
            'visibilities' => [
                [
                    'id' => $productId,
                    'salesChannelId' => Defaults::SALES_CHANNEL,
                    'visibility' => ProductVisibilityDefinition::VISIBILITY_ALL,
                ],
            ],
        ];
        $this->getContainer()->get('product.repository')->create([$product], $context);
    }

    private function getController(CartService $cartService): ClearCartController
    {
        $controller = new ClearCartController(
            $cartService
        );
        $controller->setContainer($this->getContainer());
        return $controller;
    }
}
