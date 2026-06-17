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
    <title>Larena Public Link Guarded Real Delivery Adapter</title>
    @include('larena-link::internal.partials.public-link-review-styles', [
        'mainWidth' => '1240px',
        'subWidth' => '920px',
        'preWidth' => '860px',
    ])
</head>
<body>
<main>
    <header>
        <div>
            <h1>Larena Public Link Guarded Real Delivery Adapter</h1>
            <p class="sub">
                Developer-preview adapter contract for public link delivery. It
                proves which adapter would be selected after the one-time
                lifecycle gate, but does not stream file bytes, consume tokens
                or enable production delivery.
            </p>
        </div>
        <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
    </header>

    <section>
        <h2>Adapter Decision</h2>
        <table>
            <tbody>
            <tr>
                <td>Adapter State</td>
                <td class="mono">{{ $report['adapter_decision']['adapter_state'] ?? 'unknown' }}</td>
            </tr>
            <tr>
                <td>Decision</td>
                <td class="mono">{{ $report['adapter_decision']['decision'] ?? 'unknown' }}</td>
            </tr>
            <tr>
                <td>Adapter ID</td>
                <td class="mono">{{ $report['adapter_decision']['adapter_id'] ?? 'not selected' }}</td>
            </tr>
            <tr>
                <td>Reason</td>
                <td class="mono">{{ $report['adapter_decision']['reason'] ?? 'unknown' }}</td>
            </tr>
            </tbody>
        </table>
    </section>

    <section>
        <h2>Adapter Contract</h2>
        <pre class="mono">{{ $formatStructured($report['adapter_decision'] ?? []) }}</pre>
    </section>

    <section>
        <h2>Scope Boundary</h2>
        <table>
            <tbody>
            @foreach ([
                'raw_token_visible' => 'Raw token visible',
                'raw_token_persisted' => 'Raw token persisted',
                'guarded_real_delivery_adapter_available' => 'Guarded adapter available',
                'real_delivery_adapter_contract_only' => 'Adapter contract only',
                'adapter_stream_invoked' => 'Adapter stream invoked',
                'stream_now' => 'Stream now',
                'file_download_executed' => 'File download executed',
                'file_content_returned' => 'File content returned',
                'file_body_included' => 'File body included',
                'persistent_consumed_at_write' => 'Persistent consumed_at write',
                'production_delivery' => 'Production delivery',
                'release_ready' => 'Release ready',
            ] as $field => $label)
                @php
                    $positive = in_array($field, [
                        'guarded_real_delivery_adapter_available',
                        'real_delivery_adapter_contract_only',
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
