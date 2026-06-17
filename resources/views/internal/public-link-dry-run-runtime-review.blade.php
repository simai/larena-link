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
    <title>Larena Public Link Dry-Run Runtime</title>
    @include('larena-link::internal.partials.public-link-review-styles', [
        'mainWidth' => '1240px',
        'subWidth' => '840px',
        'preWidth' => '800px',
    ])
</head>
<body>
<main>
    <header>
        <div>
            <h1>Larena Public Link Dry-Run Runtime</h1>
            <p class="sub">
                Developer-preview dry-run surface for future public link
                resolution. It models allow/deny decisions for active, expired,
                revoked, missing-access, replay, nonce and rate-limit cases,
                while keeping public route registration, token storage and file
                downloads disabled.
            </p>
        </div>
        <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
    </header>

    <section>
        <h2>Dry-Run Steps</h2>
        @foreach (($report['dry_run_steps'] ?? []) as $step)
            <span class="chip mono">{{ $step }}</span>
        @endforeach
    </section>

    <section>
        <h2>Runtime Contract</h2>
        <table>
            <tbody>
            <tr>
                <td>Runtime state</td>
                <td class="mono">{{ $report['runtime_contract']['runtime_state'] ?? 'not available' }}</td>
            </tr>
            <tr>
                <td>Resolution mode</td>
                <td class="mono">{{ $report['runtime_contract']['resolution_mode'] ?? 'not available' }}</td>
            </tr>
            <tr>
                <td>Future route shape</td>
                <td class="mono">{{ $report['runtime_contract']['future_route_shape'] ?? 'not available' }}</td>
            </tr>
            <tr>
                <td>Target type</td>
                <td class="mono">{{ $report['runtime_contract']['target_type'] ?? 'not available' }}</td>
            </tr>
            </tbody>
        </table>
    </section>

    <section>
        <h2>Dry-Run Cases</h2>
        <table>
            <thead>
            <tr>
                <th>Case</th>
                <th>Decision</th>
                <th>Reason</th>
                <th>Mutates</th>
            </tr>
            </thead>
            <tbody>
            @foreach (($report['dry_run_cases'] ?? []) as $case)
                <tr>
                    <td class="mono">{{ $case['id'] ?? 'unknown' }}</td>
                    <td class="mono">{{ $case['decision'] ?? 'unknown' }}</td>
                    <td>{{ $case['explanation'] ?? 'not available' }}</td>
                    <td class="{{ ($case['mutates_state'] ?? true) ? 'danger' : 'ok' }}">
                        {{ $formatBool($case['mutates_state'] ?? true) }}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>

    <section>
        <h2>Scope Boundary</h2>
        <table>
            <tbody>
            @foreach ([
                'route_registered_now' => 'Route registered now',
                'token_storage_enabled_now' => 'Token storage enabled now',
                'token_material_generated_now' => 'Token material generated now',
                'public_route' => 'Public route',
                'real_public_url_generated' => 'Real public URL generated',
                'file_download_executed' => 'File download executed',
                'real_file_mutation' => 'Real file mutation',
                'real_database_mutation' => 'Real database mutation',
                'release_ready' => 'Release ready',
            ] as $field => $label)
                <tr>
                    <td>{{ $label }}</td>
                    <td class="{{ ($report['safe_trace'][$field] ?? true) ? 'danger' : 'ok' }}">
                        {{ $formatBool($report['safe_trace'][$field] ?? true) }}
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

    <section>
        <h2>Known Limitations</h2>
        @foreach (($report['known_limitations'] ?? []) as $limitation)
            <span class="chip mono">{{ $limitation }}</span>
        @endforeach
    </section>
</main>
</body>
</html>
