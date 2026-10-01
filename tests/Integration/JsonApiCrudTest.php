<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

final class JsonApiCrudTest extends WebTestCase
{
    private const JSON_API = 'application/vnd.api+json';

    public static function setUpBeforeClass(): void
    {
        self::bootKernel();

        /** @var ManagerRegistry $registry */
        $registry = self::getContainer()->get(ManagerRegistry::class);

        foreach (['pgsql', 'mysql'] as $managerName) {
            /** @var EntityManagerInterface $entityManager */
            $entityManager = $registry->getManager($managerName);
            $metadata = $entityManager->getMetadataFactory()->getAllMetadata();

            if ($metadata === []) {
                continue;
            }

            $schemaTool = new SchemaTool($entityManager);
            $schemaTool->dropSchema($metadata);
            $schemaTool->createSchema($metadata);
            $entityManager->clear();
        }

        self::ensureKernelShutdown();
    }

    public function testJsonApiCrudFlow(): void
    {
        $client = static::createClient();
        $client->setServerParameter('CONTENT_TYPE', self::JSON_API);
        $client->setServerParameter('HTTP_ACCEPT', self::JSON_API);

        // Create author
        $authorPayload = [
            'data' => [
                'type' => 'authors',
                'attributes' => [
                    'name' => 'Ivan Petrov',
                ],
            ],
        ];

        $client->request('POST', '/api/authors', server: [], content: $this->encode($authorPayload));
        $authorResponse = $this->decodeJsonApiResponse($client->getResponse(), Response::HTTP_CREATED);
        $authorId = $authorResponse['data']['id'];
        self::assertSame('Ivan Petrov', $authorResponse['data']['attributes']['name']);

        // Create article linked to the author
        $articlePayload = [
            'data' => [
                'type' => 'articles',
                'attributes' => [
                    'title' => 'Hello JSON:API',
                    'content' => 'Demonstration article body.',
                ],
                'relationships' => [
                    'author' => [
                        'data' => [
                            'type' => 'authors',
                            'id' => $authorId,
                        ],
                    ],
                ],
            ],
        ];

        $client->request('POST', '/api/articles', server: [], content: $this->encode($articlePayload));
        $articleResponse = $this->decodeJsonApiResponse($client->getResponse(), Response::HTTP_CREATED);
        $articleId = $articleResponse['data']['id'];
        self::assertSame('authors', $articleResponse['data']['relationships']['author']['data']['type']);
        self::assertSame($authorId, $articleResponse['data']['relationships']['author']['data']['id']);

        // Fetch article with include and sparse fieldsets
        $client->request('GET', sprintf('/api/articles/%s?include=author&fields[articles]=title,createdAt', $articleId));
        $fetchedArticle = $this->decodeJsonApiResponse($client->getResponse(), Response::HTTP_OK);
        self::assertSame('Hello JSON:API', $fetchedArticle['data']['attributes']['title']);
        self::assertArrayNotHasKey('content', $fetchedArticle['data']['attributes']);
        self::assertArrayHasKey('included', $fetchedArticle);
        self::assertCount(1, $fetchedArticle['included']);
        self::assertSame($authorId, $fetchedArticle['included'][0]['id']);

        // Update the article title
        $updatePayload = [
            'data' => [
                'type' => 'articles',
                'id' => $articleId,
                'attributes' => [
                    'title' => 'Updated JSON:API title',
                ],
            ],
        ];

        $client->request('PATCH', sprintf('/api/articles/%s', $articleId), server: [], content: $this->encode($updatePayload));
        $updatedArticle = $this->decodeJsonApiResponse($client->getResponse(), Response::HTTP_OK);
        self::assertSame('Updated JSON:API title', $updatedArticle['data']['attributes']['title']);

        // Relationship endpoint should expose linkage
        $client->request('GET', sprintf('/api/articles/%s/relationships/author', $articleId));
        $relationship = $this->decodeJsonApiResponse($client->getResponse(), Response::HTTP_OK);
        self::assertSame($authorId, $relationship['data']['id']);

        // Create comment stored in MySQL entity manager
        $commentPayload = [
            'data' => [
                'type' => 'comments',
                'attributes' => [
                    'body' => 'Great write up!',
                    'articleId' => (int) $articleId,
                    'authorName' => 'Json Tester',
                ],
            ],
        ];

        $client->request('POST', '/api/comments', server: [], content: $this->encode($commentPayload));
        $commentResponse = $this->decodeJsonApiResponse($client->getResponse(), Response::HTTP_CREATED);
        $commentId = $commentResponse['data']['id'];
        self::assertSame('Great write up!', $commentResponse['data']['attributes']['body']);
        self::assertSame((int) $articleId, $commentResponse['data']['attributes']['articleId']);

        // Fetch comment from MySQL backed resource
        $client->request('GET', sprintf('/api/comments/%s', $commentId));
        $fetchedComment = $this->decodeJsonApiResponse($client->getResponse(), Response::HTTP_OK);
        self::assertSame('Json Tester', $fetchedComment['data']['attributes']['authorName']);

        // Delete the article and ensure it disappears
        $client->request('DELETE', sprintf('/api/articles/%s', $articleId));
        self::assertSame(Response::HTTP_NO_CONTENT, $client->getResponse()->getStatusCode());
        self::assertSame('', $client->getResponse()->getContent());

        $client->request('GET', sprintf('/api/articles/%s', $articleId));
        self::assertSame(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
    }

    public function testRejectsInvalidContentType(): void
    {
        $client = static::createClient();
        $payload = [
            'data' => [
                'type' => 'authors',
                'attributes' => [
                    'name' => 'Invalid Payload',
                ],
            ],
        ];

        $client->request('POST', '/api/authors', server: ['CONTENT_TYPE' => 'application/json'], content: $this->encode($payload));
        $response = $client->getResponse();
        self::assertContains($response->getStatusCode(), [Response::HTTP_UNSUPPORTED_MEDIA_TYPE, Response::HTTP_NOT_ACCEPTABLE]);
        $body = json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('errors', $body);
    }

    /**
     * @param array<mixed> $payload
     */
    private function encode(array $payload): string
    {
        return json_encode($payload, JSON_THROW_ON_ERROR);
    }

    /**
     * @return array<mixed>
     */
    private function decodeJsonApiResponse(Response $response, int $expectedStatus): array
    {
        self::assertSame($expectedStatus, $response->getStatusCode(), $response->getContent());
        self::assertNotFalse($response->headers->get('Content-Type'));
        self::assertStringContainsString(self::JSON_API, $response->headers->get('Content-Type'));

        return json_decode($response->getContent(), true, 512, JSON_THROW_ON_ERROR);
    }
}
