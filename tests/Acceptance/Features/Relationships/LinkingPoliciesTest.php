<?php

declare(strict_types=1);

namespace App\Tests\Acceptance\Features\Relationships;

use App\Tests\Acceptance\Support\AcceptanceTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class LinkingPoliciesTest extends AcceptanceTestCase
{
    #[DataProvider('policies')]
    public function testPolicyAcceptsExistingTargetAndRejectsMissingTarget(string $type): void
    {
        $attributes = $type === 'articles' ? ['title' => 'Link policy article', 'slug' => 'link-policy-article'] : ['title' => 'Link policy reference'];
        $body = ['data' => ['type' => $type, 'attributes' => $attributes, 'relationships' => ['author' => ['data' => $this->identifier('ada', 'authors')]]]];
        $created = $this->decodeJsonApi($this->requestJsonApi('POST', '/api/'.$type, $body), 201);
        self::assertSame($this->ids['ada'], $created['data']['relationships']['author']['data']['id']);
        $body['data']['relationships']['author']['data']['id'] = '999999999';
        if ($type === 'articles') { $body['data']['attributes']['slug'] = 'missing-link-policy'; }
        $response = $this->requestJsonApi('POST', '/api/'.$type, $body);
        // The policy controls lookup timing. Both paths must produce a controlled client error.
        self::assertContains($response->getStatusCode(), [404, 409, 422], (string) $response->getContent());
        $doc = $this->decodeJsonApi($response, $response->getStatusCode());
        self::assertArrayHasKey('errors', $doc);
        self::assertStringNotContainsString('SQLSTATE', json_encode($doc, JSON_THROW_ON_ERROR));
        $db = self::getContainer()->get(\Doctrine\Persistence\ManagerRegistry::class)->getConnection('pgsql');
        $count = (int) $db->fetchOne($type === 'articles' ? 'SELECT COUNT(*) FROM articles' : 'SELECT COUNT(*) FROM feature_articles');
        self::assertSame($type === 'articles' ? 13 : 1, $count, 'Rejected association must not leave a partial resource.');
    }


    #[DataProvider('policies')]
    public function testPolicyAlsoAppliesToUpdateAndAtomicRelationshipMutation(string $type): void
    {
        $attributes = $type === 'articles' ? ['title' => 'Mutable policy article', 'slug' => 'mutable-link-policy'] : ['title' => 'Mutable reference article'];
        $created = $this->decodeJsonApi($this->requestJsonApi('POST', '/api/'.$type, ['data' => ['type' => $type, 'attributes' => $attributes, 'relationships' => ['author' => ['data' => $this->identifier('ada', 'authors')]]]]), 201)['data'];
        $url = '/api/'.$type.'/'.$created['id'];
        $doc = $this->decodeJsonApi($this->requestJsonApi('PATCH', $url.'/relationships/author', ['data' => $this->identifier('ada', 'authors')]));
        self::assertSame($this->ids['ada'], $doc['data']['id']);
        $response = $this->atomic([['op' => 'update', 'ref' => ['type' => $type, 'id' => $created['id'], 'relationship' => 'author'], 'data' => $this->identifier('grace', 'authors')]]);
        self::assertSame(200, $response->getStatusCode(), (string) $response->getContent());
        $doc = $this->decodeJsonApi($this->requestJsonApi('GET', $url));
        self::assertSame($this->ids['grace'], $doc['data']['relationships']['author']['data']['id']);
        $response = $this->requestJsonApi('PATCH', $url.'/relationships/author', ['data' => ['type' => 'authors', 'id' => '999999999']]);
        self::assertContains($response->getStatusCode(), [404, 409, 422], (string) $response->getContent());
        self::assertSame($this->ids['grace'], $this->decodeJsonApi($this->requestJsonApi('GET', $url))['data']['relationships']['author']['data']['id']);
    }

    public static function policies(): iterable
    {
        yield 'VERIFY' => ['articles'];
        yield 'REFERENCE' => ['feature-articles'];
    }
}
