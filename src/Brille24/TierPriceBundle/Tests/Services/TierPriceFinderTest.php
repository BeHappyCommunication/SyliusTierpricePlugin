<?php
/**
 * This file is part of the Brille24 tierprice plugin.
 *
 * (c) Brille24 GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Brille24\TierPriceBundle\Tests\Services;

use Brille24\TierPriceBundle\Entity\ProductVariant;
use Brille24\TierPriceBundle\Entity\TierPrice;
use Brille24\TierPriceBundle\Repository\TierPriceRepository;
use Brille24\TierPriceBundle\Services\TierPriceFinder;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Core\Model\ChannelInterface;

class TierPriceFinderTest extends \PHPUnit_Framework_TestCase
{

    /** @var TierPriceFinder */
    private $tierPriceFinder;

    /** @var ProductVariant */
    private $testProductVariant;

    /** @var ChannelInterface */
    private $testChannel;

    /** @var TierPriceRepository */
    private $tierPriceRepo;

    public function __construct(
        ?string $name = null,
        array $data = [],
        string $dataName = ''
    ) {
        parent::__construct($name, $data, $dataName);

        $this->tierPriceRepo = $this->createMock(TierPriceRepository::class);
        
        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->willReturn($this->tierPriceRepo);

        $this->tierPriceFinder    = new TierPriceFinder($em);
        $this->testProductVariant = new ProductVariant();

        $this->testChannel = $this->createMock(ChannelInterface::class);
    }

    public function testCalculateWithNotEnoughQuantity()
    {
        ### PREPARE
        $tierPrice = $this->createMock(TierPrice::class);
        $tierPrice->method('getPrice')->willReturn(1);
        $tierPrice->method('getQty')->willReturn(20);

        $productVariant = $this->createMock(ProductVariant::class);
        $this->tierPriceRepo->method('getSortedTierPrices')->willReturn([$tierPrice]);

        ### EXECUTE
        $tierPriceFound = $this->tierPriceFinder->find($productVariant, $this->testChannel, 10);

        ### CHECK
        $this->assertEquals(null, $tierPriceFound);
    }

    public function testCalculateWithOneTierPrice()
    {
        ### PREPARE
        $tierPrice = $this->createMock(TierPrice::class);
        $tierPrice->method('getPrice')->willReturn(1);
        $tierPrice->method('getQty')->willReturn(5);

        $productVariant = $this->createMock(ProductVariant::class);
        $this->tierPriceRepo->method('getSortedTierPrices')->willReturn([$tierPrice]);

        ### EXECUTE
        $tierPriceFound = $this->tierPriceFinder->find($productVariant, $this->testChannel, 10);

        ### CHECK
        $this->assertEquals($tierPriceFound, $tierPrice);
    }

    public function testCalculateWithHighestTierPrice()
    {
        ### PREPARE
        $tierPrice1 = $this->createMock(TierPrice::class);
        $tierPrice1->method('getPrice')->willReturn(10);
        $tierPrice1->method('getQty')->willReturn(50);

        $tierPrice2 = $this->createMock(TierPrice::class);
        $tierPrice2->method('getPrice')->willReturn(5);
        $tierPrice2->method('getQty')->willReturn(10);

        $productVariant = $this->createMock(ProductVariant::class);
        $this->tierPriceRepo->method('getSortedTierPrices')->willReturn([$tierPrice1, $tierPrice2]);

        ### EXECUTE
        $tierPriceFound = $this->tierPriceFinder->find($productVariant, $this->testChannel, 10);

        ### CHECK
        $this->assertEquals($tierPriceFound, $tierPrice2);
    }
}
