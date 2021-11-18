<?php

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Product;
use Coduo\PHPMatcher\PHPUnit\PHPMatcherAssertions;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;

class ProductResourceTest extends ApiTestCase
{
    use RefreshDatabaseTrait; // oczywiście można dodawać fixtury przez Doctrine tylko trzeba pamiętać żeby to czyścić
    use PHPMatcherAssertions; // composer require --dev "coduo/php-matcher" || https://github.com/coduo/php-matcher

    public function test_get_product_that_not_exists(): void
    {
        //Given There is not a product with ID 999

        //When I get product
        static::createClient()->request('GET', '/api/products/999');

        //Then I get 404 error
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function test_get_product(): void
    {
        // we should create client firstly because of createClient call bootKernel
        $client = static::createClient();

        // Given There is product
        // api/fixtures/product.yaml
        $iri = $this->findIriBy(Product::class, [
            'name' => '__PRODUCT_1__'
        ]);

        // When I get product
        $response = $client->request('GET', $iri);

        // Then
        $this->assertResponseIsSuccessful();
        $this->assertMatchesPattern([
                "@context" => "/api/contexts/products",
                "@id" => "/api/products/1",
                "@type" => "products",
                "id" => 1,
                "price" => [
                    "@type" => "Money",
                    "@id" => "@string@",
                    "amount" => 1000,
                    "currency" => "PLN",
                ],
                "formattedPrice" => '10.00 zł',
                "name" => "__PRODUCT_1__",
                "category" => [
                    0 => "main"
                ],
                "created" => "@datetime@",
                'stats' => [
                    "@type" => "Stats",
                    "@id" => "@string@",
                    'viewed' => 1001,
                    'addedToCart' => 89,
                    'bought' => 43
                ]
            ],
            $response->toArray()
        );

        // zalety: testujemy cały response, test nie przejdzie po dodaniu nowego pola
        // wady: testujemy cały response, jeśli interesuje nas tylko wycinek musimy podać wszystkie pola lub użyć
        // matchera dla konkretnej wartości - test wygląda mniej spójnie
    }
}
