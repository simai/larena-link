<?php

declare(strict_types=1);

namespace Larena\Link\Runtime;

final class FileManagerLinkSharingRuntimePreview
{
    /**
     * @param array<string, array<string, mixed>> $checks
     * @param array<string, mixed> $safeTrace
     * @return array<string, mixed>
     */
    public static function run(array $checks, array $safeTrace, ?string $outputPath = null): array
    {
        $report = [
            'schema' => 'larena.file_manager_link_sharing_runtime_preview.v1',
            'status' => self::status($checks),
            'generated_at' => gmdate('c'),
            'mutates_state' => false,
            'cluster' => 'data-content-foundation',
            'scenario' => 'file_manager_link_sharing_runtime',
            'packages' => [
                'larena/filesystem',
                'larena/file-manager',
                'larena/link',
                'larena/access',
                'larena/audit',
            ],
            'checks' => $checks,
            'safe_trace' => $safeTrace,
            'known_limitations' => [
                'developer_testable_file_manager_link_sharing_only',
                'no_public_route',
                'no_public_ui',
                'no_real_public_url',
                'no_token_storage_runtime',
                'no_one_time_consumption_runtime',
                'no_real_file_mutation',
                'no_real_database_mutation',
                'not_release_ready',
            ],
            'next_recommended_step' => 'developer_preview_review_or_next_guarded_data_content_batch',
        ];

        if ($outputPath !== null && $outputPath !== '') {
            self::writeJson($outputPath, $report);
        }

        return $report;
    }

    /**
     * @param array<string, array<string, mixed>> $checks
     */
    private static function status(array $checks): string
    {
        foreach ($checks as $check) {
            if (($check['status'] ?? null) !== 'passed') {
                return 'failed';
            }
        }

        return 'passed';
    }

    /**
     * @param array<string, mixed> $payload
     */
    private static function writeJson(string $outputPath, array $payload): void
    {
        $directory = dirname($outputPath);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        file_put_contents($outputPath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
    }
}
