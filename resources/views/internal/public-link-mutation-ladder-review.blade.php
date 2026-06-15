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
    <title>Larena Public Link Mutation Ladder Review</title>
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
        main { width: min(1360px, calc(100% - 32px)); margin: 32px auto; }
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
        a { color: #1d4ed8; }
        .mono { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 13px; }
        .hint { color: var(--muted); max-width: 320px; }
        .ok { color: var(--ok); font-weight: 700; }
        .danger { color: var(--danger); font-weight: 700; }
    </style>
</head>
<body>
<main>
    <header>
        <div>
            <h1>Larena Public Link Mutation Ladder Review</h1>
            <p class="sub">
                Consolidated local/testing review for planning, revoke,
                regenerate and cleanup. It turns the separate machine reports
                into one operator action matrix without enabling production
                mutation, public delivery or release-ready runtime.
            </p>
        </div>
        <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
    </header>

    <section>
        <h2>Operator Action Matrix</h2>
        <table>
            <thead>
            <tr>
                <th>Action</th>
                <th>State</th>
                <th>Purpose</th>
                <th>Review</th>
                <th>Next Action</th>
                <th>Boundary</th>
            </tr>
            </thead>
            <tbody>
            @foreach (($report['operator_action_matrix'] ?? []) as $row)
                <tr>
                    <td>
                        <strong>{{ $row['label'] ?? 'Unknown action' }}</strong><br>
                        <span class="mono">{{ $row['action'] ?? 'unknown' }}</span>
                    </td>
                    <td>
                        <strong>{{ $row['state_label'] ?? 'Unknown' }}</strong><br>
                        <span class="mono">{{ $row['state'] ?? 'unknown' }}</span>
                        <p class="hint">{{ $row['state_hint'] ?? '' }}</p>
                    </td>
                    <td>{{ $row['purpose'] ?? 'not available' }}</td>
                    <td>
                        <a href="{{ $row['review_href'] ?? '#' }}">Human review</a><br>
                        <a href="{{ $row['machine_href'] ?? '#' }}">Machine detail</a><br>
                        <span class="mono">{{ $row['smoke_command'] ?? '' }}</span>
                    </td>
                    <td>{{ $row['next_action'] ?? 'not available' }}</td>
                    <td>
                        @php $boundary = $row['safe_boundary'] ?? []; @endphp
                        Production mutation:
                        <span class="{{ ($boundary['production_mutates_state'] ?? true) ? 'danger' : 'ok' }}">
                            {{ $formatBool($boundary['production_mutates_state'] ?? true) }}
                        </span><br>
                        Public runtime:
                        <span class="{{ ($boundary['production_runtime'] ?? true) ? 'danger' : 'ok' }}">
                            {{ $formatBool($boundary['production_runtime'] ?? true) }}
                        </span><br>
                        Release ready:
                        <span class="{{ ($boundary['release_ready'] ?? true) ? 'danger' : 'ok' }}">
                            {{ $formatBool($boundary['release_ready'] ?? true) }}
                        </span>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>

    <section>
        <h2>Status Meaning</h2>
        <table>
            <thead>
            <tr>
                <th>Code</th>
                <th>Human Label</th>
                <th>Meaning</th>
            </tr>
            </thead>
            <tbody>
            @foreach (($report['status_semantics'] ?? []) as $status)
                <tr>
                    <td class="mono">{{ $status['code'] ?? 'unknown' }}</td>
                    <td><strong>{{ $status['label'] ?? 'Unknown' }}</strong></td>
                    <td>{{ $status['hint'] ?? '' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </section>

    <section>
        <h2>Scope Boundaries</h2>
        <table>
            <tbody>
            @foreach (($report['scope_boundaries'] ?? []) as $field => $value)
                @php
                    $positive = in_array($field, [
                        'local_testing_only',
                        'consolidated_review_only',
                    ], true);
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
