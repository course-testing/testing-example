<?php

namespace App\Tests\Service\PriceFormatter;

use App\Entity\VO\Money;
use App\Service\BasicPriceFormatter;
use App\Service\PriceFormatter\PriceFormatter;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;

class BasicPriceFormatterTest extends TestCase
{
    private PriceFormatter $priceFormatter;

    protected function setUp(): void
    {
        $this->priceFormatter = new BasicPriceFormatter();
    }

    public function provideMoneyToFormat(): iterable
    {
        $pricePLN = new Money();
        $pricePLN->amount = 1234;
        $pricePLN->currency = 'PLN';
        yield 'format PLN' => [
            $pricePLN,
            '12.34 zł'
        ];

        $priceUSD = new Money();
        $priceUSD->amount = 5678;
        $priceUSD->currency = 'USD';
        yield 'format USD' => [
            $priceUSD,
            '$ 56.78'
        ];
    }

    /**
     * @dataProvider provideMoneyToFormat
     */
    public function test_that_returns_formatted_price($priceToFormat, $expectedFormattedPrice) {
        Assert::assertEquals(
            $expectedFormattedPrice,
            $this->priceFormatter->format($priceToFormat)
        );
    }
}
