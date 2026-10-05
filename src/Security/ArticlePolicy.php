<?php

declare(strict_types=1);

namespace App\Security;

use AlexFigures\Symfony\Http\Exception\ForbiddenException;
use AlexFigures\Symfony\Http\Exception\NotFoundException;
use App\PgEntity\Article;
use Doctrine\Persistence\ManagerRegistry;

final class ArticlePolicy
{
    public function __construct(private PublishingContext $context, private ManagerRegistry $doctrine)
    {
    }

    public function article(string $id): Article
    {
        if (!ctype_digit($id) || filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 2147483647]]) === false) {
            throw new NotFoundException('Article not found.');
        }
        return $this->doctrine->getManagerForClass(Article::class)->find(Article::class, (int) $id)
            ?? throw new NotFoundException('Article not found.');
    }

    public function requireRole(): void
    {
        if ($this->context->enabled && $this->context->identity()->role === 'reader') {
            throw new ForbiddenException('Readers cannot manage articles.');
        }
    }

    public function authorize(Article $article, string $operation): void
    {
        if (!$this->context->enabled) {
            return;
        }
        $user = $this->context->identity();
        if ($user->role === 'admin') {
            return;
        }
        if ($article->getAuthor()?->getEmail() !== $user->authorEmail) {
            throw new ForbiddenException('Article belongs to another author.');
        }
        if ($operation !== 'read') {
            $this->requireRole();
        }
    }

    public function requireAdmin(): void
    {
        if ($this->context->enabled && $this->context->identity()->role !== 'admin') {
            throw new ForbiddenException('Only administrators can manage reference resources.');
        }
    }

    public function newOwner(string $id): void
    {
        $author = $this->doctrine->getManager('pgsql')->find(\App\PgEntity\Author::class, $id);
        if ($author !== null && $author->getEmail() !== $this->context->identity()->authorEmail) {
            throw new ForbiddenException('Editors may create only their own articles.');
        }
    }

    public function association(Article $article, string $relationship, array $targets): void
    {
        $this->authorize($article, 'relationship');
        if (!$this->context->enabled || $this->context->identity()->role === 'admin') {
            return;
        }
        if ($relationship === 'author') {
            throw new ForbiddenException('Only administrators can reassign authors.');
        }
        if ($relationship === 'editor') {
            foreach ($targets as $target) {
                $author = $this->doctrine->getManager('pgsql')->find(\App\PgEntity\Author::class, $target->id);
                if ($author !== null && $author->getEmail() !== $this->context->identity()->authorEmail) {
                    throw new ForbiddenException('Editors can assign only themselves.');
                }
            }
        }
    }
}
