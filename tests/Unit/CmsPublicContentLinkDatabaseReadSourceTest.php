<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Larena\Link\Runtime\CmsPublicContentLinkReadModel;

require_once __DIR__ . '/../bootstrap.php';

function cms_link_test_connection(bool $withTable = true): \Illuminate\Database\Connection
{
    $capsule = new Capsule();
    $capsule->addConnection([
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
    ]);
    $capsule->setAsGlobal();

    $connection = $capsule->getConnection();

    if ($withTable) {
        $connection->statement(
            'create table larena_public_link_lookup (
                id integer primary key autoincrement,
                token_hash_ref varchar(96) not null,
                link_identity_ref varchar(255),
                logical_file_id varchar(255),
                access_scope_ref varchar(255),
                audit_event_ref varchar(255) not null,
                status varchar(32) not null,
                expires_at datetime null,
                revoked_at datetime null,
                preview_metadata text null,
                created_at datetime null,
                updated_at datetime null
            )'
        );
    }

    return $connection;
}

$connection = cms_link_test_connection();
$connection->insert(
    'insert into larena_public_link_lookup (token_hash_ref, link_identity_ref, logical_file_id, access_scope_ref, audit_event_ref, status, expires_at, revoked_at, preview_metadata, created_at, updated_at) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
    [
        'hash-ref-demo',
        CmsPublicContentLinkReadModel::DEMO_ID,
        'content.page:from-db',
        'access.scope:cms-public-content-link-db',
        'audit.event:cms-public-content-link-db',
        'active',
        null,
        null,
        json_encode([
            'title' => 'Database public content link',
            'target_type' => 'content_page',
            'visibility' => 'authenticated_preview_only',
            'public_url_preview' => '/preview/cms/content-links/from-db',
        ], JSON_THROW_ON_ERROR),
        '2026-06-19 00:00:00',
        '2026-06-19 00:00:00',
    ],
);

$report = CmsPublicContentLinkReadModel::fromDatabase($connection, CmsPublicContentLinkReadModel::DEMO_ID);

assert($report['schema'] === 'larena.cms_public_content_link_read_model.v1');
assert($report['status'] === 'passed');
assert($report['scenario'] === 'cms_public_content_link_database_read_model');
assert($report['read_model']['id'] === CmsPublicContentLinkReadModel::DEMO_ID);
assert($report['read_model']['title'] === 'Database public content link');
assert($report['read_model']['target_ref'] === 'content.page:from-db');
assert($report['checks']['database_read_boundary']['database_read_executed'] === true);
assert($report['checks']['database_read_boundary']['database_write_executed'] === false);
assert($report['safe_trace']['database_read'] === true);
assert($report['safe_trace']['database_write'] === false);
assert($report['safe_trace']['file_streaming'] === false);
assert($report['safe_trace']['token_material_generation'] === false);
assert($report['safe_trace']['public_delivery_adapter'] === false);
assert($report['safe_trace']['admin_crud'] === false);
assert($report['safe_trace']['release_ready'] === false);

$unknown = CmsPublicContentLinkReadModel::fromDatabase($connection, 'cms-link:unknown');

assert($unknown['status'] === 'not_found');
assert($unknown['checks']['fails_closed']['handler_may_run'] === false);
assert($unknown['checks']['fails_closed']['database_write_executed'] === false);
assert($unknown['safe_trace']['database_read'] === true);
assert($unknown['safe_trace']['database_write'] === false);

$missingId = CmsPublicContentLinkReadModel::fromDatabase($connection, '');

assert($missingId['status'] === 'validation_error');
assert($missingId['safe_trace']['database_read'] === false);
assert($missingId['safe_trace']['database_write'] === false);

$missingTable = CmsPublicContentLinkReadModel::fromDatabase(cms_link_test_connection(withTable: false), CmsPublicContentLinkReadModel::DEMO_ID);

assert($missingTable['status'] === 'failed_closed');
assert($missingTable['checks']['fails_closed']['handler_may_run'] === false);
assert($missingTable['checks']['fails_closed']['database_write_executed'] === false);
assert($missingTable['safe_trace']['database_read'] === true);
assert($missingTable['safe_trace']['database_write'] === false);
assert($missingTable['safe_trace']['release_ready'] === false);

echo "CmsPublicContentLinkDatabaseReadSourceTest passed.\n";
