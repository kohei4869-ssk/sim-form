<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * PlatformPatternSheet（1媒体分のシート）を複数まとめて、
 * 1つのExcelファイル（ブック）として出力するためのクラス。
 */
class EstimateWorkbookExport implements WithMultipleSheets
{
    /**
     * @param array<int, PlatformPatternSheet> $sheets
     */
    public function __construct(private array $sheets) {}

    public function sheets(): array
    {
        return $this->sheets;
    }
}