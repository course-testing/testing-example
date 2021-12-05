<?php

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\EventLog;
use App\Entity\Product;
use Coduo\PHPMatcher\PHPUnit\PHPMatcherAssertions;
use Doctrine\DBAL\ParameterType;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;

class ProductResourceTest extends ApiTestCase
{
    use RefreshDatabaseTrait; // oczywiście można dodawać fixtury przez Doctrine tylko trzeba pamiętać żeby to czyścić
    use PHPMatcherAssertions; // composer require --dev "coduo/php-matcher" || https://github.com/coduo/php-matcher

    /** @var \Doctrine\ORM\EntityManager */
    private $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

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
                    'viewed' => 100,
                    'addedToCart' => 200,
                    'bought' => 300
                ]
            ],
            $response->toArray()
        );

        // zalety: testujemy cały response, test nie przejdzie po dodaniu nowego pola
        // wady: testujemy cały response, jeśli interesuje nas tylko wycinek musimy podać wszystkie pola lub użyć
        // matchera dla konkretnej wartości - test wygląda mniej spójnie
    }

    public function test_that_register_product_hit_when_get()
    {
        // we should create client firstly because of createClient call bootKernel
        $client = static::createClient();

        // Given There is product with view stats
        // api/fixtures/product.yaml
        $iri = $this->findIriBy(Product::class, [
            'name' => '__PRODUCT_1__'
        ]);

        $response = $client->request('GET', $iri)
            ->toArray();

        $viewedCounter = $this->getViewedCounterByProductId($response['id']);

        // When I get product
        $client->request('GET', $iri);

        // Then
        $viewedCounterAfterGet = $this->getViewedCounterByProductId($response['id']);
        $this->assertEquals($viewedCounter + 1, $viewedCounterAfterGet);

        // zalety: nie testujemy ponownie logiki pobierania produktu
        //         testujemy cały proces dodawania loga
        //         nie couplujemy się z implementacją związaną z dodawaniem loga (gdy usuniemy mechanizm eventów i zamienimy na wywołanie metody ::save na repozytorium test nie będzie wymagał zmian)
        // wady: bardzo mocny coupling z implementacją na potrzeby asercji

        // a co jeśli dodawanie statystyk jest "ciężkim" procesem
        // lub znajduje się w osobnym module z którym nie chcemy się couplować?
    }

    private function getViewedCounterByProductId(int $productId): int
    {
        $viewedStats = $this->entityManager->createQueryBuilder()
            ->select('count(e.productId) as eventsNumber')
            ->from(EventLog::class, 'e')
            ->andWhere('e.productId = :productId')
            ->andWhere('e.eventType = :eventType')
            ->setParameter('productId', $productId, ParameterType::INTEGER)
            ->setParameter('eventType', EventLog::TYPE_VIEWED, ParameterType::INTEGER)
            ->getQuery()
            ->getResult();

        return $viewedStats[0]['eventsNumber'];
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
