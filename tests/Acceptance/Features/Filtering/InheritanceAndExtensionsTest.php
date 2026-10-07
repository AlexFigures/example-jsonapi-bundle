<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Filtering;

use App\PgEntity\Author;
use App\PgEntity\FeatureArticle;
use App\Tests\Acceptance\Support\AcceptanceTestCase;
use App\Tests\Acceptance\Support\ExpectedBundleGap;
use PHPUnit\Framework\Attributes\Group;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\DataProvider;

final class InheritanceAndExtensionsTest extends AcceptanceTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $em = self::getContainer()->get(ManagerRegistry::class)->getManagerForClass(FeatureArticle::class);
        foreach ([['Alpha', 'ada'], ['A longer title', 'grace'], ['Beta', 'ada']] as [$title, $author]) {
            $article = new FeatureArticle();
            $article->title = $title;
            $article->author = $em->find(Author::class, $this->ids[$author]);
            $em->persist($article);
        }
        $em->flush();
    }

    #[DataProvider('queries')]
    public function testPublicQueryExtensions(array $query, array $titles): void
    {
        $document = $this->decodeJsonApi($this->requestJsonApi('GET', '/api/feature-articles?'.http_build_query($query)));
        self::assertSame($titles, array_column(array_column($document['data'], 'attributes'), 'title'));
    }

    public static function queries(): iterable
    {
        yield 'inherited name' => [['filter' => ['author.name' => 'Ada Lovelace'], 'sort' => 'title'], ['Alpha', 'Beta']];
        yield 'inherited email' => [['filter' => ['author.email' => 'grace@example.test']], ['A longer title']];
        yield 'alias inherited name' => [['filter' => ['editor.name' => 'Ada Lovelace'], 'sort' => 'title'], ['Alpha', 'Beta']];
        yield 'custom operator' => [['filter' => ['title' => ['starts_with' => 'Al']]], ['Alpha']];
        yield 'custom sort ascending' => [['sort' => 'title-length'], ['Beta', 'Alpha', 'A longer title']];
        yield 'custom sort descending' => [['sort' => '-title-length'], ['A longer title', 'Alpha', 'Beta']];
        yield 'composed sort pagination' => [['sort' => 'author.name,-title-length', 'page' => ['size' => 1, 'number' => 2]], ['Beta']];
        yield 'logical custom operator' => [['filter' => ['or' => [['title' => ['starts_with' => 'Al']], ['title' => 'Beta']]], 'sort' => 'title'], ['Alpha', 'Beta']];
        yield 'bound SQL literal' => [['filter' => ['title' => ['starts_with' => "' OR 1=1 --"]]], []];
    }

    #[DataProvider('invalidQueries')]
    public function testExcludedFieldsAndInvalidOperands(array $query): void
    {
        $this->assertJsonApiError($this->requestJsonApi('GET', '/api/feature-articles?'.http_build_query($query)), 400);
    }

    public static function invalidQueries(): iterable
    {
        yield 'excluded filter' => [['filter' => ['editor.email' => 'grace@example.test']]];
        yield 'excluded sort' => [['sort' => 'editor.email']];
        yield 'invalid custom operand' => [['filter' => ['title' => ['starts_with' => ['Alpha', 'Beta']]]]];
        yield 'operator not whitelisted' => [['filter' => ['author.name' => ['starts_with' => 'Ada']]]];
    }
}
