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
    <title>Larena Public Link Revoke Action Foundation</title>
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
            <h1>Larena Public Link Revoke Action Foundation</h1>
            <p class="sub">
                Local/testing guarded revoke preview. This page proves the
                request, before/after state snapshots, rollback plan and
                negative guards before any production public-link revocation is
                allowed.
            </p>
        </div>
        <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
    </header>

    <section>
        <h2>Revoke Request</h2>
        <pre class="mono">{{ $formatStructured($report['revoke_request'] ?? []) }}</pre>
    </section>

    <section>
        <h2>Before And After State</h2>
        <table>
            <thead>
            <tr>
                <th>Snapshot</th>
                <th>Lifecycle</th>
                <th>Delivery Allowed</th>
                <th>Audit Event</th>
            </tr>
            </thead>
            <tbody>
            @foreach (['before_state_snapshot' => 'Before', 'after_state_snapshot' => 'After'] as $key => $label)
                @php $snapshot = $report[$key] ?? []; @endphp
                <tr>
                    <td><strong>{{ $label }}</strong></td>
                    <td class="mono">{{ $snapshot['lifecycle_state'] ?? 'unknown' }}</td>
                    <td>{{ $formatBool((bool) ($snapshot['delivery_allowed'] ?? false)) }}</td>
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
                'guarded_revoke_preview' => 'Guarded revoke preview',
                'local_testing_state_transition_executed' => 'Local/testing state transition executed',
                'production_mutates_state' => 'Production mutates state',
                'persistent_production_revocation' => 'Persistent production revocation',
                'raw_token_visible' => 'Raw token visible',
                'raw_token_persisted' => 'Raw token persisted',
                'file_download_executed' => 'File download executed',
                'file_content_returned' => 'File content returned',
                'public_delivery_enabled' => 'Public delivery enabled',
                'queue_or_scheduler_executed' => 'Queue or scheduler executed',
                'production_runtime' => 'Production runtime',
                'release_ready' => 'Release ready',
            ] as $field => $label)
                @php
                    $positive = in_array($field, [
                        'guarded_revoke_preview',
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
