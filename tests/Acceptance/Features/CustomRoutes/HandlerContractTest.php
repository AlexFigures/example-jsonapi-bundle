<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\CustomRoutes;

use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\Group;

use App\PgEntity\{Author, FeatureArticle};
use App\Tests\Acceptance\Support\AcceptanceTestCase;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\DataProvider;

final class HandlerContractTest extends AcceptanceTestCase
{
    private string $articleId;

    protected function setUp(): void
    {
        parent::setUp();
        $em = self::getContainer()->get(ManagerRegistry::class)->getManagerForClass(FeatureArticle::class);
        $article = new FeatureArticle();
        $article->title = 'Alpha';
        $article->author = $em->find(Author::class, $this->ids['ada']);
        $em->persist($article);
        $em->flush();
        $this->articleId = (string) $article->id;
    }

    public function testNoTransactionReadHandler(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/cookbook/features/'.$this->articleId.'/read'));
        self::assertFalse($doc['meta']['transaction_active']);
        self::assertSame($this->articleId, $doc['data']['id']);
    }

    public function testBusinessHandlerHasTransaction(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('POST', '/cookbook/features/'.$this->articleId.'/command/resource'));
        self::assertTrue($doc['meta']['transaction_active']);
    }

    public function testCriteriaBuilderComposesServerAndClientCriteria(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/cookbook/features-query?'.http_build_query(['filter' => ['author.name' => 'Ada Lovelace'], 'sort' => '-title-length', 'include' => 'author', 'fields' => ['feature-articles' => 'title,author'], 'page' => ['size' => 1]])));
        self::assertCount(1, $doc['data']);
        self::assertSame('Alpha', $doc['data'][0]['attributes']['title']);
        self::assertCount(1, $doc['included']);
        self::assertFalse($doc['meta']['transaction_active']);
    }

    public function testApplicationQueryParameterReachesCustomHandler(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/cookbook/features-query?title=Alpha'));
        self::assertCount(1, $doc['data']);
    }

    public function testPublicScopedTransactionUsesOnlyRequestedManager(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/cookbook/features/'.$this->articleId.'/scoped/pgsql'));
        self::assertTrue($doc['meta']['pgsql_transaction']);
        self::assertFalse($doc['meta']['mysql_transaction']);
    }

    public function testPublicScopedTransactionRejectsIndependentBoundaries(): void
    {
        $doc = $this->assertJsonApiError($this->requestJsonApi('GET', '/cookbook/features/'.$this->articleId.'/scoped/mixed'), 409);
        self::assertSame('unsupported-transaction-boundary', $doc['errors'][0]['code']);
    }

    #[DataProvider('forms')]
    public function testResultForms(string $form, int $status): void
    {
        $response = $this->requestJsonApi('POST', '/cookbook/features/'.$this->articleId.'/command/'.$form);
        self::assertSame($status, $response->getStatusCode(), (string) $response->getContent());
        if ($status === 204) {
            self::assertSame('', $response->getContent());
        } else {
            $this->decodeJsonApi($response, $status);
        }
    }

    public static function forms(): iterable
    {
        yield 'created' => ['created', 201];
        yield 'bad request' => ['bad-request', 400];
        yield 'forbidden' => ['forbidden', 403];
        yield 'not found' => ['not-found', 404];
        yield 'unprocessable' => ['unprocessable', 422];
        yield 'accepted' => ['accepted', 202];
        yield 'no content' => ['no-content', 204];
        yield 'conflict' => ['conflict', 409];
    }
    public function testResultModifiersAndTypePredicates(): void
    {
        $response = $this->requestJsonApi('POST', '/cookbook/features/'.$this->articleId.'/command/modifiers');
        $doc = $this->decodeJsonApi($response, 203);
        self::assertSame(['first' => true, 'second' => true], $doc['meta']);
        self::assertSame('/cookbook/help', $doc['links']['help']);
        self::assertSame('yes', $response->headers->get('X-Cookbook-Result'));
        $doc = $this->decodeJsonApi($this->requestJsonApi('POST', '/cookbook/features/'.$this->articleId.'/command/checks'));
        self::assertSame(['resource' => true, 'collection' => true, 'error' => true, 'empty' => true], $doc['meta']);
        $doc = $this->assertJsonApiError($this->requestJsonApi('POST', '/cookbook/features/'.$this->articleId.'/command/unprocessable'), 422);
        self::assertSame('/data/attributes/title', $doc['errors'][0]['source']['pointer']);
        $created = $this->requestJsonApi('POST', '/cookbook/features/'.$this->articleId.'/command/created');
        self::assertSame('/api/feature-articles/'.$this->articleId, parse_url((string) $created->headers->get('Location'), PHP_URL_PATH));
    }
    public function testRouteDefaultsRequirementsPriorityAndLegacyController(): void
    {
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', '/cookbook/route-options/'.$this->articleId.'?title=Alpha'));
        self::assertSame('normal', $doc['meta']['mode']);
        self::assertTrue($doc['meta']['has_title']);
        self::assertSame($this->articleId, $doc['data']['id']);
        self::assertSame(404, $this->requestJsonApi('GET', '/cookbook/route-options/not-an-id/normal')->getStatusCode());
        self::assertSame(404, $this->requestJsonApi('GET', '/cookbook/route-options/'.$this->articleId.'/123')->getStatusCode());
        $priority = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/feature-articles/priority'));
        self::assertSame($this->articleId, $priority['data'][0]['id']);
        $bound = $this->decodeJsonApi($this->requestJsonApi('GET', '/cookbook/typed-features/'.$this->articleId));
        self::assertSame($this->articleId, $bound['data']['id']);
        $legacy = $this->decodeJsonApi($this->requestJsonApi('GET', '/cookbook/legacy/'.$this->articleId));
        self::assertSame($this->articleId, $legacy['data']['id']);
    }
}
