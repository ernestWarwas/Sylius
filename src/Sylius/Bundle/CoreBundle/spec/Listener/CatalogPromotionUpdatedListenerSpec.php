<?php

/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\Sylius\Bundle\CoreBundle\Listener;

use Doctrine\ORM\EntityManagerInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use SM\Factory\FactoryInterface;
use Sylius\Bundle\CoreBundle\Processor\AllCatalogPromotionsProcessorInterface;
use Sylius\Bundle\CoreBundle\Processor\CatalogPromotionStateProcessorInterface;
use Sylius\Component\Core\Model\CatalogPromotionInterface;
use Sylius\Component\Promotion\Event\CatalogPromotionUpdated;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class CatalogPromotionUpdatedListenerSpec extends ObjectBehavior
{
    function let(
        AllCatalogPromotionsProcessorInterface $catalogPromotionReprocessor,
        CatalogPromotionStateProcessorInterface $catalogPromotionStateProcessor,
        RepositoryInterface $catalogPromotionRepository,
        EntityManagerInterface $entityManager,
    ): void {
        $this->beConstructedWith($catalogPromotionReprocessor, $catalogPromotionStateProcessor, $catalogPromotionRepository, $entityManager);
    }

    function it_processes_catalog_promotion_that_has_just_been_updated(
        AllCatalogPromotionsProcessorInterface $catalogPromotionReprocessor,
        CatalogPromotionStateProcessorInterface $catalogPromotionStateProcessor,
        RepositoryInterface $catalogPromotionRepository,
        EntityManagerInterface $entityManager,
        CatalogPromotionInterface $catalogPromotion
    ): void {
        $catalogPromotionRepository->findOneBy(['code' => 'WINTER_MUGS_SALE'])->willReturn($catalogPromotion);

        $catalogPromotionReprocessor->process()->shouldBeCalled();
        $catalogPromotionStateProcessor->process($catalogPromotion)->shouldBeCalled();

        $entityManager->flush()->shouldBeCalled();

        $this(new CatalogPromotionUpdated('WINTER_MUGS_SALE'));
    }

    function it_does_nothing_if_there_is_no_catalog_promotion_with_given_code(
        AllCatalogPromotionsProcessorInterface $catalogPromotionReprocessor,
        CatalogPromotionStateProcessorInterface $catalogPromotionStateProcessor,
        RepositoryInterface $catalogPromotionRepository,
        EntityManagerInterface $entityManager
    ): void {
        $catalogPromotionRepository->findOneBy(['code' => 'WINTER_MUGS_SALE'])->willReturn(null);
        $catalogPromotionRepository->findAll()->shouldNotBeCalled();

        $catalogPromotionReprocessor->process()->shouldNotBeCalled();
        $catalogPromotionStateProcessor->process(Argument::any())->shouldNotBeCalled();

        $entityManager->flush()->shouldNotBeCalled();

        $this(new CatalogPromotionUpdated('WINTER_MUGS_SALE'));
    }
}
