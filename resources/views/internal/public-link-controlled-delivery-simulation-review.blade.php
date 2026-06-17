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
    <title>Larena Public Link Controlled Delivery Simulation</title>
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
            <h1>Larena Public Link Controlled Delivery Simulation</h1>
            <p class="sub">
                Developer-preview response envelope for future public link
                delivery. It shows the status, safe headers and sandbox target
                metadata that would be used, while file body delivery remains
                explicitly blocked.
            </p>
        </div>
        <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
    </header>

    <section>
        <h2>Simulated Response</h2>
        <table>
            <tbody>
            <tr>
                <td>Simulation state</td>
                <td class="mono">{{ $report['simulated_response']['simulation_state'] ?? 'unknown' }}</td>
            </tr>
            <tr>
                <td>Decision</td>
                <td class="mono">{{ $report['simulated_response']['decision'] ?? 'unknown' }}</td>
            </tr>
            <tr>
                <td>HTTP status preview</td>
                <td class="mono">{{ $report['simulated_response']['http_status_preview'] ?? 'unknown' }}</td>
            </tr>
            <tr>
                <td>File delivery</td>
                <td class="mono">{{ $report['simulated_response']['file_delivery'] ?? 'unknown' }}</td>
            </tr>
            <tr>
                <td>Body included</td>
                <td class="{{ ($report['simulated_response']['body_included'] ?? true) ? 'danger' : 'ok' }}">
                    {{ $formatBool($report['simulated_response']['body_included'] ?? true) }}
                </td>
            </tr>
            <tr>
                <td>File content returned</td>
                <td class="{{ ($report['simulated_response']['file_content_returned'] ?? true) ? 'danger' : 'ok' }}">
                    {{ $formatBool($report['simulated_response']['file_content_returned'] ?? true) }}
                </td>
            </tr>
            </tbody>
        </table>
    </section>

    <section>
        <h2>Response Metadata</h2>
        <pre class="mono">{{ $formatStructured($report['simulated_response'] ?? []) }}</pre>
    </section>

    <section>
        <h2>Delivery State And Target</h2>
        <table>
            <tbody>
            <tr>
                <td>Delivery state</td>
                <td class="mono">{{ $report['delivery_state']['state'] ?? 'unknown' }}</td>
            </tr>
            <tr>
                <td>Logical file id</td>
                <td class="mono">{{ $report['target_proof']['logical_file_id'] ?? 'not available' }}</td>
            </tr>
            <tr>
                <td>Target fingerprint</td>
                <td class="mono">{{ $report['target_proof']['target_fingerprint'] ?? 'not available' }}</td>
            </tr>
            <tr>
                <td>Sandbox storage ref</td>
                <td class="mono">{{ $report['target_proof']['sandbox_storage_ref'] ?? 'not available' }}</td>
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
                'controlled_delivery_simulation_available' => 'Controlled delivery simulation available',
                'simulated_response_only' => 'Simulated response only',
                'would_deliver_sandbox_target' => 'Would deliver sandbox target',
                'response_body_included' => 'Response body included',
                'production_delivery' => 'Production delivery',
                'file_download_executed' => 'File download executed',
                'file_content_returned' => 'File content returned',
                'one_time_consumption_runtime' => 'One-time consumption runtime',
                'release_ready' => 'Release ready',
            ] as $field => $label)
                @php
                    $positive = in_array($field, [
                        'controlled_delivery_simulation_available',
                        'simulated_response_only',
                        'would_deliver_sandbox_target',
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
