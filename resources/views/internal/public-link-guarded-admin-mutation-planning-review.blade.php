@php
    $statusClass = match ((string) ($report['status'] ?? 'unknown')) {
        'passed' => 'status-pass',
        'degraded' => 'status-warn',
        default => 'status-danger',
    };
    $formatBool = static fn (mixed $value): string => $value ? 'Yes' : 'No';
    $formatStructured = static fn (mixed $value): string => is_array($value)
        ? (string) json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        : (string) ($value ?? 'not available');
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Larena Public Link Guarded Admin Mutation Planning</title>
    @include('larena-link::internal.partials.public-link-review-styles', [
        'mainWidth' => '1320px',
        'subWidth' => '920px',
        'preWidth' => '760px',
    ])
</head>
<body>
<main>
    <header>
        <div>
            <h1>Larena Public Link Guarded Admin Mutation Planning</h1>
            <p class="sub">
                Read-only developer-preview plan for future revoke, regenerate
                and cleanup actions. The page exists to review launch-record,
                rollback, access, audit and negative-test requirements before
                any persistent public-link admin mutation is allowed.
            </p>
        </div>
        <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
    </header>

    <section>
        <h2>Mutation Plan Registry</h2>
        <table>
            <thead>
            <tr>
                <th>Action</th>
                <th>State</th>
                <th>Launch Record</th>
                <th>Access Scope</th>
                <th>Audit Events</th>
                <th>Rollback Evidence</th>
                <th>Negative Tests</th>
            </tr>
            </thead>
            <tbody>
            @foreach (($report['mutation_plan_registry'] ?? []) as $plan)
                <tr>
                    <td>
                        <strong>{{ $plan['human_label'] ?? str_replace('_', ' ', (string) ($plan['action'] ?? 'unknown')) }}</strong>
                        <div>{{ $plan['purpose'] ?? '' }}</div>
                    </td>
                    <td class="mono">{{ $plan['state'] ?? 'unknown' }}</td>
                    <td class="mono">{{ $plan['required_launch_record'] ?? 'not available' }}</td>
                    <td class="mono">{{ $plan['access_scope_ref'] ?? 'not available' }}</td>
                    <td><pre class="mono">{{ $formatStructured($plan['audit_event_refs'] ?? []) }}</pre></td>
                    <td><pre class="mono">{{ $formatStructured($plan['rollback_evidence'] ?? []) }}</pre></td>
                    <td><pre class="mono">{{ $formatStructured($plan['required_negative_tests'] ?? []) }}</pre></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>

    <section>
        <h2>Future Launch Records</h2>
        <pre class="mono">{{ $formatStructured($report['future_launch_records'] ?? []) }}</pre>
    </section>

    <section>
        <h2>Scope Boundary</h2>
        <table>
            <tbody>
            @foreach ([
                'read_only_planning_preview' => 'Read-only planning preview',
                'guarded_admin_mutation_planning_available' => 'Guarded admin mutation planning available',
                'mutation_actions_allowed' => 'Mutation actions allowed',
                'revocation_executed' => 'Revocation executed',
                'regeneration_executed' => 'Regeneration executed',
                'cleanup_executed' => 'Cleanup executed',
                'database_write_executed' => 'Database write executed',
                'queue_or_scheduler_executed' => 'Queue or scheduler executed',
                'file_download_executed' => 'File download executed',
                'file_content_returned' => 'File content returned',
                'raw_token_visible' => 'Raw token visible',
                'production_delivery' => 'Production delivery',
                'release_ready' => 'Release ready',
            ] as $field => $label)
                @php
                    $positive = in_array($field, [
                        'read_only_planning_preview',
                        'guarded_admin_mutation_planning_available',
                    ], true);
                    $value = (bool) ($report['safe_trace'][$field] ?? false);
                @endphp
                <tr>
                    <td>{{ $label }}</td>
                    <td class="{{ ($positive && $value) || (!$positive && !$value) ? 'ok' : 'danger' }}">
                        {{ $formatBool($value) }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>

    <section>
        <h2>Checks</h2>
        <table>
            <thead>
            <tr>
                <th>Check</th>
                <th>Status</th>
                <th>Details</th>
            </tr>
            </thead>
            <tbody>
            @foreach (($report['checks'] ?? []) as $name => $check)
                <tr>
                    <td><strong>{{ str_replace('_', ' ', (string) $name) }}</strong></td>
                    <td class="mono">{{ strtoupper((string) ($check['status'] ?? 'unknown')) }}</td>
                    <td><pre class="mono">{{ $formatStructured($check) }}</pre></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>
</main>
</body>
</html>
