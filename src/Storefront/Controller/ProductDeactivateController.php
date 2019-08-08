<?php declare(strict_types=1);

namespace Swag\StorefrontController\Storefront\Controller;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\Routing\Annotation\Route;

class ProductDeactivateController extends StorefrontController
{
    /**
     * @var EntityRepositoryInterface
     */
    private $productRepository;

    public function __construct(EntityRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     * @Route("/product/deactivate/{id}", name="storefront.deactivateProduct", options={"seo"="false"}, methods={"GET"})
     */
    public function deactivateProduct(string $id, SalesChannelContext $context)
    {
        $this->productRepository->update([
            [
                'id' => $id,
                'active' => false
            ]
        ], $context->getContext());

        return $this->forwardToRoute('frontend.home.page');
    }
}
