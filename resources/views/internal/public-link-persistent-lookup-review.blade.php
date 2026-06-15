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
    <title>Larena Public Link Persistent Lookup</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f7f8fa;
            --panel: #ffffff;
            --line: #d8dde5;
            --text: #111827;
            --muted: #5b6472;
            --ok: #0f7b45;
            --warn: #9a6200;
            --danger: #b42318;
            --chip: #eef1f5;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font: 14px/1.5 -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        main { width: min(1240px, calc(100% - 32px)); margin: 32px auto; }
        header {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        h1 { margin: 0 0 6px; font-size: 28px; letter-spacing: 0; }
        h2 { margin: 0 0 12px; font-size: 18px; letter-spacing: 0; }
        .sub { margin: 0; color: var(--muted); max-width: 900px; }
        .badge, .chip {
            display: inline-block;
            border-radius: 6px;
            padding: 5px 8px;
            font-weight: 700;
            white-space: nowrap;
        }
        .badge { color: #fff; }
        .status-pass { background: var(--ok); }
        .status-warn { background: var(--warn); }
        .status-danger { background: var(--danger); }
        section {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 18px;
            margin-bottom: 16px;
        }
        table { width: 100%; border-collapse: collapse; }
        th, td {
            padding: 10px 0;
            border-top: 1px solid var(--line);
            text-align: left;
            vertical-align: top;
        }
        th {
            color: var(--muted);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }
        .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 13px; }
        pre.mono {
            white-space: pre-wrap;
            overflow-wrap: anywhere;
            margin: 0;
            max-width: 860px;
        }
        .chip { background: var(--chip); margin: 2px 4px 2px 0; }
        .ok { color: var(--ok); font-weight: 700; }
        .danger { color: var(--danger); font-weight: 700; }
    </style>
</head>
<body>
<main>
    <header>
        <div>
            <h1>Larena Public Link Persistent Lookup</h1>
            <p class="sub">
                Developer-preview persistent hashed lookup for future public
                links. It may create and seed a local/testing table, but it
                still forbids raw token storage, production lookup, one-time
                consumption and public file delivery.
            </p>
        </div>
        <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
    </header>

    <section>
        <h2>Schema And Seed</h2>
        <table>
            <tbody>
            <tr>
                <td>Table</td>
                <td class="mono">{{ $report['schema_state']['table'] ?? 'not available' }}</td>
            </tr>
            <tr>
                <td>Table exists</td>
                <td class="{{ ($report['schema_state']['table_exists'] ?? false) ? 'ok' : 'danger' }}">
                    {{ $formatBool($report['schema_state']['table_exists'] ?? false) }}
                </td>
            </tr>
            <tr>
                <td>Created now</td>
                <td class="mono">{{ $formatBool($report['schema_state']['created_now'] ?? false) }}</td>
            </tr>
            <tr>
                <td>Seeded records</td>
                <td class="mono">{{ $report['seed_state']['seeded_count'] ?? 0 }}</td>
            </tr>
            </tbody>
        </table>
    </section>

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
        <h2>Lookup Result</h2>
        <pre class="mono">{{ $formatStructured($report['lookup_result'] ?? []) }}</pre>
    </section>

    <section>
        <h2>Persistence Contract</h2>
        <table>
            <tbody>
            <tr>
                <td>Stored fields</td>
                <td>
                    @foreach (($report['persistence_contract']['stored_fields'] ?? []) as $field)
                        <span class="chip mono">{{ $field }}</span>
                    @endforeach
                </td>
            </tr>
            <tr>
                <td>Forbidden fields</td>
                <td>
                    @foreach (($report['persistence_contract']['forbidden_fields'] ?? []) as $field)
                        <span class="chip mono">{{ $field }}</span>
                    @endforeach
                </td>
            </tr>
            <tr>
                <td>Rollback boundary</td>
                <td><pre class="mono">{{ $formatStructured($report['persistence_contract']['rollback_boundary'] ?? []) }}</pre></td>
            </tr>
            </tbody>
        </table>
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
                'file_content_returned' => 'File content returned',
                'one_time_consumption_runtime' => 'One-time consumption runtime',
                'real_file_mutation' => 'Real file mutation',
                'production_database_mutation' => 'Production database mutation',
                'release_ready' => 'Release ready',
            ] as $field => $label)
                <tr>
                    <td>{{ $label }}</td>
                    <td class="{{ ($report['safe_trace'][$field] ?? true) && !in_array($field, ['persistent_token_table', 'database_migration'], true) ? 'danger' : 'ok' }}">
                        {{ $formatBool($report['safe_trace'][$field] ?? false) }}
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
