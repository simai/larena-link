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
    <title>Larena Public Link Regenerate Action Foundation</title>
    @include('larena-link::internal.partials.public-link-review-styles', [
        'mainWidth' => '1320px',
        'subWidth' => '920px',
        'preWidth' => '860px',
    ])
</head>
<body>
<main>
    <header>
        <div>
            <h1>Larena Public Link Regenerate Action Foundation</h1>
            <p class="sub">
                Local/testing guarded regenerate preview. This page proves the
                request, old/new fingerprint snapshots, rollback to previous
                token hash plan and negative guards before any production
                public-link regeneration is allowed.
            </p>
        </div>
        <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
    </header>

    <section>
        <h2>Regenerate Request</h2>
        <pre class="mono">{{ $formatStructured($report['regenerate_request'] ?? []) }}</pre>
    </section>

    <section>
        <h2>Old And New Fingerprints</h2>
        <table>
            <thead>
            <tr>
                <th>Snapshot</th>
                <th>Lifecycle</th>
                <th>Delivery Allowed</th>
                <th>Token Fingerprint</th>
                <th>Audit Event</th>
            </tr>
            </thead>
            <tbody>
            @foreach (['old_fingerprint_snapshot' => 'Old', 'new_fingerprint_snapshot' => 'New'] as $key => $label)
                @php $snapshot = $report[$key] ?? []; @endphp
                <tr>
                    <td><strong>{{ $label }}</strong></td>
                    <td class="mono">{{ $snapshot['lifecycle_state'] ?? 'unknown' }}</td>
                    <td>{{ $formatBool((bool) ($snapshot['delivery_allowed'] ?? false)) }}</td>
                    <td class="mono">{{ $snapshot['token_fingerprint'] ?? 'not available' }}</td>
                    <td class="mono">{{ $snapshot['audit_event_ref'] ?? 'not available' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>

    <section>
        <h2>Rollback Plan</h2>
        <pre class="mono">{{ $formatStructured($report['rollback_plan'] ?? []) }}</pre>
    </section>

    <section>
        <h2>Negative Guards</h2>
        <pre class="mono">{{ $formatStructured($report['negative_guards'] ?? []) }}</pre>
    </section>

    <section>
        <h2>Scope Boundary</h2>
        <table>
            <tbody>
            @foreach ([
                'guarded_regenerate_preview' => 'Guarded regenerate preview',
                'local_testing_state_transition_executed' => 'Local/testing state transition executed',
                'production_mutates_state' => 'Production mutates state',
                'persistent_production_regeneration' => 'Persistent production regeneration',
                'raw_token_visible' => 'Raw token visible',
                'raw_token_persisted' => 'Raw token persisted',
                'raw_regenerated_token_returned' => 'Raw regenerated token returned',
                'file_download_executed' => 'File download executed',
                'file_content_returned' => 'File content returned',
                'public_delivery_enabled_by_this_action' => 'Public delivery enabled by this action',
                'queue_or_scheduler_executed' => 'Queue or scheduler executed',
                'production_runtime' => 'Production runtime',
                'release_ready' => 'Release ready',
            ] as $field => $label)
                @php
                    $positive = in_array($field, [
                        'guarded_regenerate_preview',
                        'local_testing_state_transition_executed',
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
