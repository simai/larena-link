<?php

declare(strict_types=1);

use Larena\Link\Admin\PublicLinkAdminSurfaceContributor;

require __DIR__ . '/../bootstrap.php';

function expect_invalid_public_link_admin_contributor(array $descriptors, string $expectedMessage): void
{
    try {
        PublicLinkAdminSurfaceContributor::validate($descriptors);
        throw new RuntimeException('Expected public link admin contributor validation to fail.');
    } catch (InvalidArgumentException $exception) {
        assert(str_contains($exception->getMessage(), $expectedMessage), $exception->getMessage());
    }
}

$contributions = PublicLinkAdminSurfaceContributor::contributions();
assert(count($contributions) === 17);
assert($contributions[0]['id'] === 'public_content_link_flow');
assert($contributions[16]['id'] === 'public_link_delivery_contract_hardening');
assert(array_count_values(array_column($contributions, 'contribution_type')) === [
    'screen' => 11,
    'diagnostic' => 1,
    'action' => 5,
]);

$ids = [];
foreach ($contributions as $contribution) {
    $id = (string) $contribution['id'];
    assert(!isset($ids[$id]), "Duplicate contribution id [{$id}].");
    $ids[$id] = true;
    assert($contribution['owner_package'] === 'larena/link');
    assert($contribution['href'] !== '');
    assert($contribution['machine_href'] !== '');
    assert(in_array($contribution['contribution_type'], ['screen', 'action', 'diagnostic'], true));
}

assert($contributions[6]['id'] === 'public_link_guarded_delivery_readiness');
assert($contributions[6]['contribution_type'] === 'diagnostic');
assert($contributions[11]['id'] === 'public_link_guarded_admin_mutation_planning');
assert($contributions[11]['contribution_type'] === 'action');
assert($contributions[15]['id'] === 'public_link_mutation_ladder_review');
assert($contributions[15]['contribution_type'] === 'action');

$duplicate = $contributions;
$duplicate[] = $contributions[0];
expect_invalid_public_link_admin_contributor($duplicate, 'Duplicate public link admin contribution id');

$ownerMismatch = $contributions;
$ownerMismatch[0]['owner_package'] = 'larena/admin';
expect_invalid_public_link_admin_contributor($ownerMismatch, 'must be owned by larena/link');

$missingType = $contributions;
unset($missingType[0]['contribution_type']);
expect_invalid_public_link_admin_contributor($missingType, 'missing contribution_type');

$missingHref = $contributions;
$missingHref[0]['machine_href'] = '';
expect_invalid_public_link_admin_contributor($missingHref, 'missing href or machine_href');

$unsafeMethod = $contributions;
$unsafeMethod[0]['method'] = 'POST';
expect_invalid_public_link_admin_contributor($unsafeMethod, 'exposes unsafe method');

$writeCapable = $contributions;
$writeCapable[0]['write_capable'] = true;
expect_invalid_public_link_admin_contributor($writeCapable, 'must stay read-only');

echo "PublicLinkAdminSurfaceContributorTest passed.\n";
