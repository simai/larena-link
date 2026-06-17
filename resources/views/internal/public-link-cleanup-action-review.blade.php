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
    <title>Larena Public Link Cleanup Action Foundation</title>
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
            <h1>Larena Public Link Cleanup Action Foundation</h1>
            <p class="sub">
                Local/testing guarded cleanup preview. This page proves the
                candidate set, would-clean snapshot, retention-policy reference,
                rollback/replay plan and negative guards before any production
                public-link cleanup is allowed.
            </p>
        </div>
        <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
    </header>

    <section>
        <h2>Cleanup Request</h2>
        <pre class="mono">{{ $formatStructured($report['cleanup_request'] ?? []) }}</pre>
    </section>

    <section>
        <h2>Candidate Set</h2>
        <table>
            <thead>
            <tr>
                <th>Link</th>
                <th>Lifecycle</th>
                <th>Reason</th>
                <th>Decision</th>
            </tr>
            </thead>
            <tbody>
            @foreach (($report['candidate_set_snapshot']['cleanup_candidates'] ?? []) as $candidate)
                <tr>
                    <td class="mono">{{ $candidate['link_ref'] ?? 'unknown' }}</td>
                    <td class="mono">{{ $candidate['lifecycle_state'] ?? 'unknown' }}</td>
                    <td>{{ $candidate['reason'] ?? 'not available' }}</td>
                    <td class="ok">Would clean in dry-run only</td>
                </tr>
            @endforeach
            @foreach (($report['candidate_set_snapshot']['excluded_active_links'] ?? []) as $candidate)
                <tr>
                    <td class="mono">{{ $candidate['link_ref'] ?? 'unknown' }}</td>
                    <td class="mono">{{ $candidate['lifecycle_state'] ?? 'unknown' }}</td>
                    <td>{{ $candidate['reason'] ?? 'not available' }}</td>
                    <td class="danger">Excluded from cleanup</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>

    <section>
        <h2>Would-Clean Snapshot</h2>
        <pre class="mono">{{ $formatStructured($report['would_clean_snapshot'] ?? []) }}</pre>
    </section>

    <section>
        <h2>Rollback / Replay Plan</h2>
        <pre class="mono">{{ $formatStructured($report['rollback_replay_plan'] ?? []) }}</pre>
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
                'guarded_cleanup_preview' => 'Guarded cleanup preview',
                'local_testing_state_transition_executed' => 'Local/testing state transition executed',
                'dry_run_only' => 'Dry-run only',
                'production_mutates_state' => 'Production mutates state',
                'persistent_production_cleanup' => 'Persistent production cleanup',
                'active_link_cleanup' => 'Active link cleanup',
                'scheduler_executed' => 'Scheduler executed',
                'queue_executed' => 'Queue executed',
                'production_database_delete' => 'Production database delete',
                'file_deletion_executed' => 'File deletion executed',
                'file_content_returned' => 'File content returned',
                'public_delivery_enabled_by_this_action' => 'Public delivery enabled by this action',
                'production_runtime' => 'Production runtime',
                'release_ready' => 'Release ready',
            ] as $field => $label)
                @php
                    $positive = in_array($field, [
                        'guarded_cleanup_preview',
                        'local_testing_state_transition_executed',
                        'dry_run_only',
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
