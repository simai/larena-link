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
    <title>Larena Public Link Operator Lifecycle Management</title>
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
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: var(--bg);
            color: var(--text);
            font: 14px/1.5 -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        main { width: min(1320px, calc(100% - 32px)); margin: 32px auto; }
        header {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        h1 { margin: 0 0 6px; font-size: 28px; letter-spacing: 0; }
        h2 { margin: 0 0 12px; font-size: 18px; letter-spacing: 0; }
        .sub { margin: 0; color: var(--muted); max-width: 920px; }
        .badge {
            display: inline-block;
            border-radius: 6px;
            padding: 5px 8px;
            color: #fff;
            font-weight: 700;
            white-space: nowrap;
        }
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
            padding: 10px 8px 10px 0;
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
        .ok { color: var(--ok); font-weight: 700; }
        .danger { color: var(--danger); font-weight: 700; }
    </style>
</head>
<body>
<main>
    <header>
        <div>
            <h1>Larena Public Link Operator Lifecycle Management</h1>
            <p class="sub">
                Read-only developer-preview registry for public link lifecycle
                states and blocked delivery decisions. It shows what an
                operator may review today and which actions require future
                guarded launch records.
            </p>
        </div>
        <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
    </header>

    <section>
        <h2>Operator Registry</h2>
        <table>
            <thead>
            <tr>
                <th>Case</th>
                <th>Lifecycle</th>
                <th>Adapter</th>
                <th>Decision</th>
                <th>Reason</th>
                <th>Allowed Actions</th>
                <th>Blocked Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach (($report['operator_registry'] ?? []) as $record)
                <tr>
                    <td><strong>{{ str_replace('_', ' ', (string) ($record['case_id'] ?? 'unknown')) }}</strong></td>
                    <td class="mono">{{ $record['lifecycle_state'] ?? 'unknown' }}</td>
                    <td class="mono">{{ $record['adapter_state'] ?? 'unknown' }}</td>
                    <td class="mono">{{ $record['decision'] ?? 'unknown' }}</td>
                    <td class="mono">{{ $record['reason'] ?? 'unknown' }}</td>
                    <td><pre class="mono">{{ $formatStructured($record['allowed_actions'] ?? []) }}</pre></td>
                    <td><pre class="mono">{{ $formatStructured($record['blocked_actions'] ?? []) }}</pre></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>

    <section>
        <h2>Action Policy</h2>
        <pre class="mono">{{ $formatStructured($report['operator_action_policy'] ?? []) }}</pre>
    </section>

    <section>
        <h2>Scope Boundary</h2>
        <table>
            <tbody>
            @foreach ([
                'read_only_operator_preview' => 'Read-only operator preview',
                'operator_lifecycle_management_available' => 'Operator lifecycle management available',
                'mutation_actions_allowed' => 'Mutation actions allowed',
                'adapter_stream_invoked' => 'Adapter stream invoked',
                'file_download_executed' => 'File download executed',
                'file_content_returned' => 'File content returned',
                'persistent_consumed_at_write' => 'Persistent consumed_at write',
                'raw_token_visible' => 'Raw token visible',
                'raw_token_persisted' => 'Raw token persisted',
                'production_delivery' => 'Production delivery',
                'release_ready' => 'Release ready',
            ] as $field => $label)
                @php
                    $positive = in_array($field, [
                        'read_only_operator_preview',
                        'operator_lifecycle_management_available',
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
