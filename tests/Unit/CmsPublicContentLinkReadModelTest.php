<?php

declare(strict_types=1);

use Larena\Link\Runtime\CmsPublicContentLinkReadModel;

require_once __DIR__ . '/../../vendor/autoload.php';

$outputPath = sys_get_temp_dir() . '/larena-link-cms-public-content-link-read-model-' . bin2hex(random_bytes(4)) . '.json';
$report = CmsPublicContentLinkReadModel::forId(CmsPublicContentLinkReadModel::DEMO_ID, $outputPath);

assert($report['schema'] === 'larena.cms_public_content_link_read_model.v1');
assert($report['status'] === 'passed');
assert($report['owner_package'] === 'larena/link');
assert($report['input_contract_owner'] === 'larena/rest');
assert($report['mutates_state'] === false);
assert($report['production_mutates_state'] === false);
assert($report['read_model']['id'] === 'cms-link:demo');
assert($report['read_model']['target_type'] === 'content_page');
assert($report['read_model']['visibility'] === 'authenticated_preview_only');
assert($report['checks']['operation_contract']['status'] === 'passed');
assert($report['checks']['fixture_boundary']['database_query_executed'] === false);
assert($report['checks']['runtime_guards']['database_write'] === false);
assert($report['safe_trace']['database_read'] === false);
assert($report['safe_trace']['database_write'] === false);
assert($report['safe_trace']['file_streaming'] === false);
assert($report['safe_trace']['token_material_generation'] === false);
assert($report['safe_trace']['public_delivery_adapter'] === false);
assert($report['safe_trace']['admin_crud'] === false);
assert($report['safe_trace']['production_runtime'] === false);
assert($report['safe_trace']['release_ready'] === false);
assert(is_file($outputPath));

$missing = CmsPublicContentLinkReadModel::forId('');

assert($missing['status'] === 'validation_error');
assert($missing['safe_trace']['database_read'] === false);
assert($missing['safe_trace']['database_write'] === false);
assert($missing['checks']['fails_closed']['handler_may_run'] === false);

$unknown = CmsPublicContentLinkReadModel::forId('cms-link:unknown');

assert($unknown['status'] === 'not_found');
assert($unknown['safe_trace']['public_delivery_adapter'] === false);
assert($unknown['safe_trace']['release_ready'] === false);

echo "CmsPublicContentLinkReadModelTest passed.\n";
