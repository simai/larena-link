@php
    $statusClass = match ((string) ($report['status'] ?? 'unknown')) {
        'passed' => 'status-pass',
        'degraded' => 'status-warn',
        default => 'status-danger',
    };
    $formatBool = static fn (mixed $value): string => $value ? 'Yes' : 'No';
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Larena Public Link Delivery Contract Hardening</title>
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
        main { width: min(1400px, calc(100% - 32px)); margin: 32px auto; }
        header {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        h1 { margin: 0 0 6px; font-size: 28px; letter-spacing: 0; }
        h2 { margin: 0 0 12px; font-size: 18px; letter-spacing: 0; }
        .sub { margin: 0; color: var(--muted); max-width: 940px; }
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
        .hint { color: var(--muted); max-width: 360px; }
        .ok { color: var(--ok); font-weight: 700; }
        .danger { color: var(--danger); font-weight: 700; }
        code {
            background: #eef2f7;
            border-radius: 5px;
            padding: 1px 5px;
        }
    </style>
</head>
<body>
<main>
    <header>
        <div>
            <h1>Larena Public Link Delivery Contract Hardening</h1>
            <p class="sub">
                Developer-preview contract for future public link delivery.
                It defines state decisions, HTTP status policy, safe headers,
                body policy, access/audit recheck points and negative guards
                without streaming file content or enabling production delivery.
            </p>
        </div>
        <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
    </header>

    <section>
        <h2>Delivery Decision Matrix</h2>
        <table>
            <thead>
            <tr>
                <th>State</th>
                <th>Decision</th>
                <th>HTTP</th>
                <th>Reason</th>
                <th>Access / Audit</th>
                <th>Body</th>
            </tr>
            </thead>
            <tbody>
            @foreach (($report['delivery_decision_matrix'] ?? []) as $row)
                <tr>
                    <td>
                        <strong>{{ $row['label'] ?? 'Unknown state' }}</strong><br>
                        <span class="mono">{{ $row['state'] ?? 'unknown' }}</span>
                        <p class="hint">{{ $row['state_hint'] ?? '' }}</p>
                    </td>
                    <td>
                        <strong>{{ $row['decision'] ?? 'unknown' }}</strong><br>
                        <span class="mono">{{ $row['adapter_state'] ?? '' }}</span>
                    </td>
                    <td class="mono">{{ $row['http_status'] ?? 'n/a' }}</td>
                    <td>{{ $row['reason'] ?? 'not available' }}</td>
                    <td>
                        <span class="mono">{{ $row['access_scope_ref'] ?? '' }}</span><br>
                        <span class="mono">{{ $row['audit_event_ref'] ?? '' }}</span>
                    </td>
                    <td>
                        <span class="mono">{{ $row['body_kind'] ?? '' }}</span><br>
                        File body:
                        <span class="{{ ($row['body_policy']['file_body_included'] ?? true) ? 'danger' : 'ok' }}">
                            {{ $formatBool($row['body_policy']['file_body_included'] ?? true) }}
                        </span>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>

    <section>
        <h2>Safe Header Policy</h2>
        <table>
            <tbody>
            @foreach (($report['safe_header_policy'] ?? []) as $name => $value)
                <tr>
                    <td class="mono">{{ $name }}</td>
                    <td class="mono">{{ $value }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>

    <section>
        <h2>Scope Boundary</h2>
        <table>
            <tbody>
            @foreach (($report['scope_boundary'] ?? []) as $field => $value)
                @php
                    $positive = in_array($field, ['local_testing_only', 'delivery_contract_only'], true);
                @endphp
                <tr>
                    <td>{{ str_replace('_', ' ', (string) $field) }}</td>
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
                    <td class="mono">{{ $name }}</td>
                    <td class="{{ ($check['status'] ?? '') === 'passed' ? 'ok' : 'danger' }}">
                        {{ strtoupper((string) ($check['status'] ?? 'unknown')) }}
                    </td>
                    <td class="mono">{{ json_encode($check, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>
</main>
</body>
</html>
