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
    <title>Larena Public Link Runtime Planning</title>
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
        main { width: min(1180px, calc(100% - 32px)); margin: 32px auto; }
        header {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        h1 { margin: 0 0 6px; font-size: 28px; letter-spacing: 0; }
        h2 { margin: 0 0 12px; font-size: 18px; letter-spacing: 0; }
        .sub { margin: 0; color: var(--muted); max-width: 820px; }
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
            max-width: 760px;
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
            <h1>Larena Public Link Runtime Planning</h1>
            <p class="sub">
                Developer-preview planning surface for the future public link
                runtime. It defines route, token, expiry, access, audit,
                revocation and replay/nonce/rate-limit gates while keeping all
                public runtime behavior blocked.
            </p>
        </div>
        <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
    </header>

    <section>
        <h2>Planning Steps</h2>
        @foreach (($report['planning_steps'] ?? []) as $step)
            <span class="chip mono">{{ $step }}</span>
        @endforeach
    </section>

    <section>
        <h2>Future Runtime Contract</h2>
        <table>
            <tbody>
            <tr>
                <td>Future route shape</td>
                <td class="mono">{{ $report['runtime_contract']['future_route_shape'] ?? 'not available' }}</td>
            </tr>
            <tr>
                <td>Runtime state</td>
                <td class="mono">{{ $report['runtime_contract']['runtime_state'] ?? 'not available' }}</td>
            </tr>
            <tr>
                <td>Target type</td>
                <td class="mono">{{ $report['runtime_contract']['target_type'] ?? 'not available' }}</td>
            </tr>
            <tr>
                <td>Audience</td>
                <td class="mono">{{ $report['runtime_contract']['audience'] ?? 'not available' }}</td>
            </tr>
            </tbody>
        </table>
    </section>

    <section>
        <h2>Scope Boundary</h2>
        <table>
            <tbody>
            <tr>
                <td>Route registered now</td>
                <td class="{{ ($report['safe_trace']['route_registered_now'] ?? true) ? 'danger' : 'ok' }}">
                    {{ $formatBool($report['safe_trace']['route_registered_now'] ?? true) }}
                </td>
            </tr>
            <tr>
                <td>Token storage enabled now</td>
                <td class="{{ ($report['safe_trace']['token_storage_enabled_now'] ?? true) ? 'danger' : 'ok' }}">
                    {{ $formatBool($report['safe_trace']['token_storage_enabled_now'] ?? true) }}
                </td>
            </tr>
            <tr>
                <td>Token material generated now</td>
                <td class="{{ ($report['safe_trace']['token_material_generated_now'] ?? true) ? 'danger' : 'ok' }}">
                    {{ $formatBool($report['safe_trace']['token_material_generated_now'] ?? true) }}
                </td>
            </tr>
            <tr>
                <td>Public route</td>
                <td class="{{ ($report['safe_trace']['public_route'] ?? true) ? 'danger' : 'ok' }}">
                    {{ $formatBool($report['safe_trace']['public_route'] ?? true) }}
                </td>
            </tr>
            <tr>
                <td>Real public URL generated</td>
                <td class="{{ ($report['safe_trace']['real_public_url_generated'] ?? true) ? 'danger' : 'ok' }}">
                    {{ $formatBool($report['safe_trace']['real_public_url_generated'] ?? true) }}
                </td>
            </tr>
            <tr>
                <td>Release ready</td>
                <td class="ok">No</td>
            </tr>
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
