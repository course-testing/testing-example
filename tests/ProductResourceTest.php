<?php

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Product;
use Coduo\PHPMatcher\PHPUnit\PHPMatcherAssertions;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;

class ProductResourceTest extends ApiTestCase
{
    use RefreshDatabaseTrait;
    use PHPMatcherAssertions;

    public function test_get_product_that_not_exists(): void
    {
        //Given There is not a product with ID 999

        //When I get product
        static::createClient()->request('GET', '/api/products/999');

        //Then I get 404 error
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    //można doawać to rpzez doctrina tylko trzeba pamiętać żeby to czyścić
    // composer require --dev "coduo/php-matcher"
    public function test_get_product(): void
    {
        //we should create client firstly because of createClient call bootKernel
        $client = static::createClient();

        //Given There is product
        //api/fixtures/product.yaml
        $iri = $this->findIriBy(Product::class, [
            'name' => '__PRODUCT_1__'
        ]);

        //When I get product
        $client->request('GET', $iri);

        //Then
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'id' => 1,
        ]);
    }
}
