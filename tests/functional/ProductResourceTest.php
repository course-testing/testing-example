<?php

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Client;
use App\Entity\Product;
use App\Event\ProductHit;
use App\Message\ProductHitMessage;
use Coduo\PHPMatcher\PHPUnit\PHPMatcherAssertions;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Debug\TraceableEventDispatcher;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class ProductResourceTest extends ApiTestCase
{
    use RefreshDatabaseTrait; // oczywiście można dodawać fixtury przez Doctrine tylko trzeba pamiętać żeby to czyścić
    use PHPMatcherAssertions; // composer require --dev "coduo/php-matcher" || https://github.com/coduo/php-matcher

    const PRODUCT_ID = 1;

    private Client $client;
    private MessageBusInterface $messageBusMock;

    protected function setUp(): void
    {
        // we should create client firstly (before setting on container) because of createClient call bootKernel
        $this->client = static::createClient();

        $this->messageBusMock = $this->getMockBuilder(MessageBusInterface::class)
            ->getMock();

        $this->messageBusMock
            ->method('dispatch')
            ->willReturn(new Envelope(new \stdClass())); // Envelope jest final class - dlatego phpUnit nie potrafi zrobić domyślnie test doubla

        static::getContainer()->set('messenger.bus.default', $this->messageBusMock);
    }

    public function test_get_product_that_not_exists(): void
    {
        //Given There is not a product with ID 999

        //When I get product
        $this->client->request('GET', '/api/products/999');

        //Then I get 404 error
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
    }

    public function test_get_product(): void
    {
        // Given There is product
        // api/fixtures/product.yaml
        $iri = $this->findIriBy(Product::class, [
            'name' => '__PRODUCT_1__'
        ]);

        // When I get product
        $response = $this->client->request('GET', $iri);

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
        // Given There is product with view stats
        // api/fixtures/product.yaml
        $iri = $this->findIriBy(Product::class, [
            'name' => '__PRODUCT_1__'
        ]);

        // When I get product
        $this->client->request('GET', $iri);

        // Then
       $this->assertEventHasBeenDispatched(ProductHit::NAME);
    }

    public function test_that_product_hit_notify()
    {
        $message = new ProductHitMessage(self::PRODUCT_ID);
        $this->messageBusMock
            ->expects($this->once())
            ->method('dispatch')
            ->with($message)
            ->willReturn(new Envelope($message));

        // Given There is product
        // api/fixtures/product.yaml
        $iri = $this->findIriBy(Product::class, [
            'name' => '__PRODUCT_1__'
        ]);

        // When I get product
        $this->client->request('GET', $iri);

        //Then product hit notification sent
    }

    private function assertEventHasBeenDispatched(string $eventName) {
        /** @var TraceableEventDispatcher $dispatcher */
        $dispatcher = static::getContainer()->get('debug.event_dispatcher');

        $foundCalls = array_filter($dispatcher->getCalledListeners(), function($registeredCall) use ($eventName) {
            return $registeredCall['event'] === $eventName;
        });

        $this->assertTrue(count($foundCalls) === 1, "${eventName} event has not been dispatched");
    }
}
