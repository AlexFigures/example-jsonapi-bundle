<?php

declare(strict_types=1);

namespace App\JsonApi\DataLayer;

use AlexFigures\Symfony\Contract\Data\{ResourceRepository, ResourceProcessor, RelationshipReader, Slice, SliceIds, ChangeSet};
use AlexFigures\Symfony\Contract\Tx\TransactionManager;
use AlexFigures\Symfony\Http\Exception\{NotFoundException, ConflictException, UnprocessableEntityException};
use AlexFigures\Symfony\Query\{Criteria, Pagination};
use App\FeatureMemory\MemoryArticle;

/** A deterministic source, not a Doctrine adapter; reset on each kernel boot. */
final class MemoryArticleProvider implements ResourceRepository, ResourceProcessor, RelationshipReader, TransactionManager
{
    /** @var array<string, MemoryArticle> */
    private array $articles;

    public function __construct()
    {
        $second = new MemoryArticle('two', 'Related article');
        $this->articles = ['one' => new MemoryArticle(related: $second), 'two' => $second];
    }

    public function findCollection(string $type, Criteria $criteria): Slice
    {
        $this->assertType($type);
        $page = $criteria->pagination;
        return new Slice(array_slice(array_values($this->articles), ($page->number - 1) * $page->size, $page->size), $page->number, $page->size, count($this->articles));
    }

    public function findOne(string $type, string $id, Criteria $criteria): ?object
    {
        $this->assertType($type);
        return $this->articles[$id] ?? null;
    }

    public function findRelated(string $type, string $relationship, array $identifiers): iterable
    {
        foreach ($identifiers as $identifier) {
            if (isset($this->articles[$identifier->id])) {
                yield $this->articles[$identifier->id];
            }
        }
    }

    public function processCreate(string $type, ChangeSet $changes, ?string $clientId = null): object
    {
        $this->assertType($type);
        $id = $clientId ?? 'created';
        if (isset($this->articles[$id])) { throw new ConflictException('Memory article identifier already exists.'); }
        $article = new MemoryArticle($id, $this->checkedTitle($changes->attributes['title'] ?? 'New article'));
        return $this->articles[$article->id] = $article;
    }

    public function processUpdate(string $type, string $id, ChangeSet $changes): object
    {
        $this->assertType($type);
        $article = $this->articles[$id] ?? throw new NotFoundException('Missing memory article');
        $article->title = $this->checkedTitle($changes->attributes['title'] ?? $article->title);
        return $article;
    }

    public function processDelete(string $type, string $id): void
    {
        $this->assertType($type);
        if (!isset($this->articles[$id])) { throw new NotFoundException('Missing memory article'); }
        unset($this->articles[$id]);
    }

    public function getToOneId(string $type, string $id, string $rel): ?string
    {
        return ($this->articles[$id] ?? null)?->related?->id;
    }

    public function getToManyIds(string $type, string $id, string $rel, Pagination $pagination): SliceIds
    {
        return new SliceIds([], 1, 20, 0);
    }

    public function getRelatedResource(string $type, string $id, string $rel): ?object
    {
        return ($this->articles[$id] ?? null)?->related;
    }

    public function getRelatedCollection(string $type, string $id, string $rel, Criteria $criteria): Slice
    {
        return new Slice([], 1, 20, 0);
    }

    private function checkedTitle(mixed $title): string
    {
        if (!is_string($title) || strlen(trim($title)) < 3) {
            throw new UnprocessableEntityException('Memory article title requires at least three bytes.');
        }
        return $title;
    }

    private function assertType(string $type): void
    {
        if ($type !== 'memory-articles') { throw new NotFoundException('The example source only serves memory-articles.'); }
    }

    public function transactional(callable $callback): mixed
    {
        $snapshot = serialize($this->articles);
        try {
            return $callback();
        } catch (\Throwable $error) {
            $this->articles = unserialize($snapshot, ['allowed_classes' => [MemoryArticle::class]]);
            throw $error;
        }
    }
}
