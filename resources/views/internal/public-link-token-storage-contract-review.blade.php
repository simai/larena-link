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
    <title>Larena Public Link Token Storage Contract</title>
    @include('larena-link::internal.partials.public-link-review-styles', [
        'mainWidth' => '1240px',
        'subWidth' => '860px',
        'preWidth' => '820px',
    ])
</head>
<body>
<main>
    <header>
        <div>
            <h1>Larena Public Link Token Storage Contract</h1>
            <p class="sub">
                Developer-preview contract for future public link token lookup.
                It models hash-only storage and fail-closed lookup decisions,
                while keeping raw token storage, database migration, production
                lookup and file delivery disabled.
            </p>
        </div>
        <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
    </header>

    <section>
        <h2>Candidate Lookup</h2>
        <table>
            <tbody>
            <tr>
                <td>Token fingerprint</td>
                <td class="mono">{{ $report['candidate_lookup']['token_fingerprint'] ?? 'not available' }}</td>
            </tr>
            <tr>
                <td>Lookup status</td>
                <td class="mono">{{ $report['candidate_lookup']['lookup_status'] ?? 'unknown' }}</td>
            </tr>
            <tr>
                <td>Decision</td>
                <td class="mono">{{ $report['candidate_lookup']['decision'] ?? 'unknown' }}</td>
            </tr>
            <tr>
                <td>Raw token visible</td>
                <td class="{{ ($report['candidate_lookup']['raw_token_visible'] ?? true) ? 'danger' : 'ok' }}">
                    {{ $formatBool($report['candidate_lookup']['raw_token_visible'] ?? true) }}
                </td>
            </tr>
            </tbody>
        </table>
    </section>

    <section>
        <h2>Storage Contract</h2>
        <table>
            <tbody>
            <tr>
                <td>Runtime state</td>
                <td class="mono">{{ $report['storage_contract']['runtime_state'] ?? 'not available' }}</td>
            </tr>
            <tr>
                <td>Hash algorithm</td>
                <td class="mono">{{ $report['storage_contract']['hash_algorithm'] ?? 'not available' }}</td>
            </tr>
            <tr>
                <td>Stored fields</td>
                <td>
                    @foreach (($report['storage_contract']['stored_fields'] ?? []) as $field)
                        <span class="chip mono">{{ $field }}</span>
                    @endforeach
                </td>
            </tr>
            <tr>
                <td>Forbidden fields</td>
                <td>
                    @foreach (($report['storage_contract']['forbidden_fields'] ?? []) as $field)
                        <span class="chip mono">{{ $field }}</span>
                    @endforeach
                </td>
            </tr>
            </tbody>
        </table>
    </section>

    <section>
        <h2>Lookup Result</h2>
        <pre class="mono">{{ $formatStructured($report['lookup_result'] ?? []) }}</pre>
    </section>

    <section>
        <h2>Scope Boundary</h2>
        <table>
            <tbody>
            @foreach ([
                'raw_token_visible' => 'Raw token visible',
                'raw_token_persisted' => 'Raw token persisted',
                'persistent_token_table' => 'Persistent token table',
                'database_migration' => 'Database migration',
                'production_lookup' => 'Production lookup',
                'file_download_executed' => 'File download executed',
                'one_time_consumption_runtime' => 'One-time consumption runtime',
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
</main>
</body>
</html>
