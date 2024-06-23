<?php

namespace App\Tests\Controller;

use Faker\Factory;
use Faker\Generator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Uid\Ulid;

class AuthorControllerTest extends WebTestCase
{
    private static Generator $faker;
    private $client = null;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$faker = Factory::create();
    }

    public function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testEndpointAvailable(): void
    {
        $this->client->request('GET', '/authors');

        $this->assertResponseIsSuccessful();
    }

    public function testAddNewAuthorWithNoPayload(): void
    {
        $this->client->request('POST', '/authors');
        $this->assertResponseStatusCodeSame(422);
        $response = $this->client->getResponse();
        $this->assertResponseHeaderSame('content-type', 'application/json');
        $this->assertJson($response->getContent());
        $responseContent = json_decode($response->getContent());
        $this->assertObjectHasProperty('type', $responseContent);
        $this->assertEquals($responseContent->type, 'https://tools.ietf.org/html/rfc2616#section-10');
    }

    public function testAddNewAuthorWithEmptyPayload(): void
    {
        $this->client->jsonRequest('POST', '/authors');
        $this->assertResponseStatusCodeSame(422);
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $responseContent = json_decode($response->getContent());
        // error type is validation error
        $this->assertObjectHasProperty('type', $responseContent);
        $this->assertEquals($responseContent->type, 'https://symfony.com/errors/validation');
        // 3 violations for current entity
        $this->assertIsArray($responseContent->violations);
        $this->assertCount(3, $responseContent->violations);
    }

    public function testAddNewAuthorWithValidPayload(): array
    {
        $payload = [
            "name" => "John Doe",
            "bio" => "writer",
            "birth_date" => "1990-01-01",
        ];
        $this->client->jsonRequest(
            'POST',
            '/authors',
            $payload,
        );
        $this->assertResponseStatusCodeSame(201);
        $response = $this->client->getResponse()->getContent();
        $this->assertJson($response);
        $responseContent = json_decode($response, true);
        $this->assertArrayHasKey('id', $responseContent);
        $this->assertTrue(Ulid::isValid(Ulid::fromString($responseContent['id'])));
        // move id to payload and check if result is the same
        $payload['id'] = $responseContent['id'];
        $this->assertEqualsCanonicalizing($payload, $responseContent);
        return $payload;
    }

    /**
     * @depends testAddNewAuthorWithValidPayload
     */
    public function testAddedAuthorGetPersisted(array $payload): void
    {
        $this->client->jsonRequest('GET', '/authors/' . $payload['id']);
        $this->assertResponseStatusCodeSame(200);
        $response = $this->client->getResponse();
        $this->assertJson($response->getContent());
        $responseContent = json_decode($response->getContent(), true);
        $this->assertEqualsCanonicalizing($payload, $responseContent);
    }
}
