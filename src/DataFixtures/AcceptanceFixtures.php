<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\MysqlEntity\Comment;
use App\PgEntity\{Article, Author, AuditLog, Category, Newsletter, Subscription, Tag};
use App\Enum\ArticleStatus;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;

final class AcceptanceFixtures
{
    /** @return array<string, string> */
    public static function reset(ManagerRegistry $registry): array
    {
        foreach (['pgsql', 'mysql'] as $name) {
            $manager = $registry->getManager($name);
            $metadata = $manager->getMetadataFactory()->getAllMetadata();
            $tool = new SchemaTool($manager);
            $tool->dropSchema($metadata);
            $tool->createSchema($metadata);
            $manager->clear();
        }

        $pg = $registry->getManager('pgsql');
        $ada = (new Author())->setName('Ada Lovelace')->setEmail('ada@example.test');
        $grace = (new Author())->setName('Grace Hopper')->setEmail('grace@example.test');
        $empty = (new Author())->setName('Empty Author');
        $php = (new Tag())->setName('PHP');
        $api = (new Tag())->setName('API');
        $unused = (new Tag())->setName('Unused');
        $objects = ['ada' => $ada, 'grace' => $grace, 'empty-author' => $empty, 'php' => $php, 'api' => $api, 'unused-tag' => $unused];
        for ($i = 1; $i <= 12; ++$i) {
            $article = (new Article())
                ->setTitle($i <= 2 ? 'Shared title' : sprintf('Article %02d', $i))
                ->setSlug(sprintf('article-%02d', $i))
                ->setContent("Body $i")
                ->setStatus($i % 2 === 0 ? ArticleStatus::PUBLISHED : ArticleStatus::DRAFT)
                ->setMetadata(['sequence' => $i, 'labels' => ['acceptance', 'demo']])
                ->setViews($i * 10)->setRating(4.5)->setFeatured($i % 2 === 0)
                ->setPublishedAt($i % 2 === 0 ? new \DateTimeImmutable('2026-01-02T12:00:00+00:00') : null)
                ->setCreatedAt(new \DateTimeImmutable(sprintf('2026-01-%02dT00:00:00+00:00', $i)))
                ->setUpdatedAt(new \DateTimeImmutable('2026-01-15T00:00:00+00:00'));
            ($i <= 8 ? $ada : $grace)->addArticle($article);
            if ($i <= 3) {
                $article->setEditor($grace)->setReviewedBy($ada);
                $php->addArticle($article);
                $api->addArticle($article);
            }
            $objects['article-'.$i] = $article;
        }
        $root = (new Category())->setName('Engineering');
        $child = (new Category())->setName('Backend');
        $leaf = (new Category())->setName('Symfony');
        $root->addChild($child);
        $child->addChild($leaf);
        $objects += ['archived' => (new Category())->setName('Archived')->setDeletedAt(new \DateTimeImmutable('2026-01-01T00:00:00+00:00')), 'root' => $root, 'child' => $child, 'leaf' => $leaf,
            'audit' => (new AuditLog())->record('Article imported'),
            'subscription' => (new Subscription())->setEmail('subscriber@example.test')];
        $objects['newsletter'] = (new Newsletter())->setSubject('Weekly Symfony news')->setRecipient($objects['subscription']);
        $objects['previous-newsletter'] = (new Newsletter())->setSubject('Previous edition');
        $objects['newsletter']->setPreviousEdition($objects['previous-newsletter']);
        foreach ($objects as $object) {
            $pg->persist($object);
        }
        $pg->flush();
        $ids = [];
        foreach ($objects as $key => $object) {
            $ids[$key] = (string) $object->getId();
        }
        $my = $registry->getManager('mysql');
        $comment = (new Comment())->setBody('Useful article')->setArticleId((int) $ids['article-1'])->setAuthorName('Reader');
        $my->persist($comment);
        $my->flush();
        $ids['comment'] = (string) $comment->getId();
        $pg->clear();
        $my->clear();

        return $ids;
    }
}
