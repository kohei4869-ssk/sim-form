<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * 媒体（YouTube・Meta・YG・リスティング）ごとに1シート分を組み立てるクラス。
 * データそのもの（$rows）は SubmitController 側で組み立てて、
 * このクラスは「もらったデータをどう見た目よく出力するか」だけを担当する。
 *
 * シートのレイアウト（上から）：
 *   1行目：空白
 *   2行目：クライアント名／案件名（太字・サイズ10・列全体を結合）
 *   3行目：空白
 *   4行目：テーブルの見出し行（ネイビー背景・白文字・太字・サイズ8）
 *   5行目以降：データ本体（奇数列だけ薄い黄色、「—」のセルは条件付き書式で薄いグレーに塗る。罫線なし）
 */
class PlatformPatternSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths, WithColumnFormatting
{
    public const TITLE_ROW = 2;
    public const HEADER_ROW = 4;
    public const DATA_START_ROW = 5;

    /**
     * @param array<int, array<int, mixed>> $rows 1行分の配列を、パターンの数だけ並べたもの。
     *        値が "=" で始まる文字列の場合は、ダッシュボードの自動計算式と同じ内容のExcel数式として書き込まれる。
     * @param array<int, string> $headings 列見出し（$rows の各要素と同じ並び順にすること）
     * @param string $title シートのタブに表示される名前
     * @param string $headerColor 見出し行の背景色（ARGB形式。例: 'FF1B2A4A'＝ネイビー）
     * @param array<int, string|null> $columnTypes $headings と同じ並び順で、各列が
     *        'currency'（¥＋カンマ区切り）／'percent'（％表示）／'number'（カンマ区切りの数値）／null（書式なし・文字列扱い）
     *        のどれに当たるかを示す配列。ダッシュボード側の表示ルール（$currencyKeys／$percentKeys）と揃えている。
     * @param string $summaryLine 2行目に表示する「クライアント名／案件名」の文字列
     */
    public function __construct(
        private array $rows,
        private array $headings,
        private string $title,
        private string $headerColor,
        private array $columnTypes = [],
        private string $summaryLine = '',
    ) {}

    public function array(): array
    {
        $columnCount = max(count($this->headings), 1);
        $blankRow = array_fill(0, $columnCount, null);

        $summaryRow = array_fill(0, $columnCount, null);
        $summaryRow[0] = $this->summaryLine;

        // 1行目：空白／2行目：クライアント名・案件名／3行目：空白／4行目：見出し／5行目〜：データ
        return array_merge(
            [$blankRow, $summaryRow, $blankRow, $this->headings],
            $this->rows
        );
    }

    public function title(): string
    {
        return $this->title;
    }

    public function columnFormats(): array
    {
        $formats = [];

        foreach ($this->columnTypes as $i => $type) {
            if (!$type) {
                continue; // 文字列の列は書式指定しない（Excel標準の「標準」表示のまま）
            }

            $columnLetter = Coordinate::stringFromColumnIndex($i + 1);

            $formats[$columnLetter] = match ($type) {
                'currency' => '"¥"#,##0',   // 例: ¥1,200,000
                'percent'  => '0.00%',      // 例: 4.20%（セルの値は0.042のような小数のまま持たせる）
                'number'   => '#,##0',      // 例: 12,345
                default    => NumberFormat::FORMAT_GENERAL,
            };
        }

        return $formats;
    }

    public function columnWidths(): array
    {
        $widths = [];
        foreach ($this->headings as $i => $heading) {
            // Coordinate::stringFromColumnIndex は 1始まりなので +1 する（A, B, ... Z, AA, AB ...）
            $columnLetter = Coordinate::stringFromColumnIndex($i + 1);
            $widths[$columnLetter] = 16;
        }
        return $widths;
    }

    public function styles(Worksheet $sheet)
    {
        $columnCount = max(count($this->headings), 1);
        $lastColumn  = Coordinate::stringFromColumnIndex($columnCount);
        $titleRow    = self::TITLE_ROW;
        $headerRow   = self::HEADER_ROW;
        $dataStart   = self::DATA_START_ROW;
        $lastRow     = max($dataStart + count($this->rows) - 1, $headerRow);

        // 2行目：クライアント名／案件名（太字・サイズ10）。列全体を1つのセルとして結合する
        if ($columnCount > 1) {
            $sheet->mergeCells("A{$titleRow}:{$lastColumn}{$titleRow}");
        }
        $sheet->getStyle("A{$titleRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
        ]);

        // 見出し行：ネイビー背景に白文字・太字・サイズ8
        $sheet->getStyle("A{$headerRow}:{$lastColumn}{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 8, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['argb' => $this->headerColor],
            ],
            'alignment' => ['vertical' => 'center', 'wrapText' => true],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(26);

        // データ本体もサイズ8で統一する（見出しだけでなくテーブル全体を小さめのフォントに）
        if ($lastRow >= $dataStart) {
            $sheet->getStyle("A{$dataStart}:{$lastColumn}{$lastRow}")->applyFromArray([
                'font' => ['size' => 8],
            ]);

            // 奇数列（1,3,5...列目）だけ薄い黄色に塗る（行の縞模様ではなく列の縞模様）
            for ($col = 1; $col <= $columnCount; $col++) {
                if ($col % 2 === 1) {
                    $letter = Coordinate::stringFromColumnIndex($col);
                    $sheet->getStyle("{$letter}{$dataStart}:{$letter}{$lastRow}")->applyFromArray([
                        'fill' => [
                            'fillType'   => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFFFF6D6'],
                        ],
                    ]);
                }
            }

            // 「—」（対象外・未確定）のセルは、数式の計算結果であっても条件付き書式で薄いグレーに塗る
            $dataRange = "A{$dataStart}:{$lastColumn}{$lastRow}";
            $naConditional = new Conditional();
            $naConditional->setConditionType(Conditional::CONDITION_CELLIS);
            $naConditional->setOperatorType(Conditional::OPERATOR_EQUAL);
            $naConditional->addCondition('"—"');
            $naConditional->getStyle()->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFD9D9D9');
            $sheet->getStyle($dataRange)->setConditionalStyles(
                array_merge($sheet->getStyle($dataRange)->getConditionalStyles(), [$naConditional])
            );
        }

        // 罫線なし（ダッシュボード側の見た目に寄せてすっきりさせる）

        // 下にスクロールしても、クライアント名・案件名と見出し行が常に見えるようにする
        $sheet->freezePane('A' . $dataStart);

        return [];
    }
}