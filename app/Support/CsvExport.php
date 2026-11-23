<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * CSV export without extra dependencies.
 *
 * The byte-order mark keeps Arabic readable when the file is opened in Excel,
 * which otherwise assumes the system codepage.
 */
class CsvExport
{
    /**
     * @param  array<int, string>  $headings
     * @param  iterable<int, array<int, mixed>>  $rows
     */
    public static function stream(string $filename, array $headings, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headings, $rows) {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headings);

            foreach ($rows as $row) {
                fputcsv($handle, array_map(
                    fn ($value) => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                    $row,
                ));
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
