<?php

declare(strict_types=1);

namespace App\Torture\Infrastructure;

use App\DataFixtures\AcceptanceFixtures;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;

final class TortureFixtures
{
    public const SIZES = ['small' => 100, 'medium' => 10000, 'large' => 100000];

    public function __construct(private readonly ManagerRegistry $registry) {}

    public function reset(string $mode = 'small', ?int $rows = null): array
    {
        $count = $rows ?? self::SIZES[$mode] ?? throw new \InvalidArgumentException('Use small, medium, or large.');
        if ($count < 10 || $count > 1000000) { throw new \InvalidArgumentException('Rows must be between 10 and 1,000,000.'); }
        foreach (['pgsql', 'mysql', 'shard_b', 'replica'] as $name) {
            $database = $this->registry->getManager($name)->getConnection()->getDatabase();
            if (!str_contains($database, '_torture')) { throw new \LogicException('Refusing to reset a database without the _torture marker.'); }
        }
        RequestMetrics::$active = false;
        $ids = AcceptanceFixtures::reset($this->registry);
        foreach (['shard_b', 'replica'] as $name) {
            $manager = $this->registry->getManager($name);
            $metadata = $manager->getMetadataFactory()->getAllMetadata();
            $tool = new SchemaTool($manager);
            $tool->dropSchema($metadata);
            $tool->createSchema($metadata);
            $manager->clear();
        }
        $users = min(10000, max(10, intdiv($count, 10)));
        $tags = min(5000, max(5, intdiv($count, 20)));
        $projects = min(100, max(2, intdiv($count, 100)));
        $hot = min(10000, intdiv($count, 2));
        foreach (['pgsql' => 'Primary v2', 'shard_b' => 'Tenant B', 'replica' => 'Replica v1'] as $name => $label) {
            $connection = $this->registry->getManager($name)->getConnection();
            // Replica/shard snapshots need only 100 tasks; no actual replication process is operated.
            $n = $name === 'pgsql' ? $count : 100;
            $u = $name === 'pgsql' ? $users : 10;
            $t = $name === 'pgsql' ? $tags : 5;
            $p = $name === 'pgsql' ? $projects : 2;
            $h = $name === 'pgsql' ? $hot : 50;
            $connection->transactional(function ($db) use ($label, $n, $u, $t, $p, $h): void {
                $db->executeStatement("INSERT INTO torture_countries (id, name) VALUES ('GE', 'Georgia'), ('US', 'United States')");
                $db->executeStatement("INSERT INTO torture_organizations (id, name) VALUES ('org-1', ?)", [$label]);
                $db->executeStatement("INSERT INTO torture_workspace_users (id, name, organization_id, country_id) SELECT 'u-'||g, 'User '||g, 'org-1', 'GE' FROM generate_series(1, $u) g");
                $db->executeStatement("INSERT INTO torture_projects (id, name, organization_id, owner_id) SELECT 'p-'||g, ?||' project '||g, 'org-1', 'u-'||(((g-1)%$u)+1) FROM generate_series(1, $p) g", [$label]);
                $db->executeStatement("INSERT INTO torture_project_memberships (id, role, joined_at, notification_settings, project_id, user_id) SELECT 'm-'||g, CASE WHEN g%2=0 THEN 'editor' ELSE 'reader' END, '2026-01-01', '{\"email\":true}'::json, 'p-'||(((g-1)%$p)+1), 'u-'||g FROM generate_series(1, $u) g");
                // Existing acceptance tags have IDs 1..3 on primary; scale tags use a disjoint range.
                $db->executeStatement("INSERT INTO tags (id, name) SELECT 1000+g, 'Label '||g FROM generate_series(1, $t) g");
                $db->executeStatement("INSERT INTO torture_assets (id, name, kind, filename, duration) VALUES ('document-1', 'Document', 'document', 'guide.pdf', NULL), ('video-1', 'Video', 'video', NULL, 120)");
                $db->executeStatement("INSERT INTO torture_tasks (id, title, description, project_id, assignee_id, parent_id) SELECT g, 'Same', 'Details '||g, CASE WHEN g<=$h THEN 'p-1' ELSE 'p-'||(((g-$h-1)%($p-1))+2) END, 'u-'||(((g-1)%$u)+1), CASE WHEN g BETWEEN 2 AND 5 THEN 1 ELSE NULL END FROM generate_series(1, $n) g");
                $db->executeStatement("INSERT INTO torture_task_labels (task_id, tag_id) SELECT g, 1000+(((g+k-2)%$t)+1) FROM generate_series(1, $n) g CROSS JOIN generate_series(1, ".min(10, $t).") k");
                $db->executeStatement("INSERT INTO torture_task_attachments (task_id, asset_id) SELECT g, a FROM generate_series(1, $n) g CROSS JOIN (VALUES ('document-1'), ('video-1')) v(a)");
                $db->executeStatement("SELECT setval(pg_get_serial_sequence('torture_tasks', 'id'), $n)");
            });
            $connection->executeStatement('ANALYZE');
            $this->registry->getManager($name)->clear();
        }
        $db = $this->registry->getConnection('pgsql');
        $this->registry->getConnection('shard_b')->executeStatement("INSERT INTO torture_shard_notes (id, body) VALUES ('note-1', 'Tenant B original')");
        $this->registry->getConnection('shard_b')->executeStatement("INSERT INTO torture_workspace_users (id, name, organization_id) VALUES ('only-b', 'Only tenant B', 'org-1')");
        $db->executeStatement("INSERT INTO torture_tasks (id, title, description, project_id) VALUES ('9223372036854775806', 'BIGINT boundary', '', 'p-2')");
        foreach (['replicated', 'broken_replica'] as $name) { $this->registry->getManager($name)->clear(); $this->registry->getConnection($name)->close(); }
        $dataset = ['mode' => $mode, 'tasks' => $count, 'users' => $users, 'tags' => $tags, 'projects' => $projects,
            'task_label_rows' => $count * min(10, $tags), 'hot_project_tasks' => $hot, 'replica' => 'independent deterministic snapshot'];
        $directory = dirname(__DIR__, 3).'/var/torture';
        if (!is_dir($directory)) { mkdir($directory, 0775, true); }
        file_put_contents($directory.'/dataset.json', json_encode($dataset, JSON_THROW_ON_ERROR));
        return $ids + ['organization' => 'org-1', 'project' => 'p-1', 'task' => '1', 'user' => 'u-1', 'membership' => 'm-1', 'country' => 'GE'];
    }
}
