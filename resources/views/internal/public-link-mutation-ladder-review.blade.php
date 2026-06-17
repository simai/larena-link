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
    @include('larena-link::internal.partials.public-link-review-styles', [
        'mainWidth' => '1360px',
        'subWidth' => '920px',
        'preWidth' => '860px',
    ])
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
