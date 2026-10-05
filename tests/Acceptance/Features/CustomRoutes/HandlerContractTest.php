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

    #[Group('bundle-gap')]
    #[ExpectedBundleGap('CUSTOM-ACTION-QUERY-PARAMETER')]
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
        yield 'accepted' => ['accepted', 202];
        yield 'no content' => ['no-content', 204];
        yield 'conflict' => ['conflict', 409];
    }
}
