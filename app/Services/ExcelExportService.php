<?php

namespace App\Services;

use RuntimeException;
use ZipArchive;

class ExcelExportService
{
    public function build(array $headers, iterable $rows, string $sheetName = 'Reporte'): string
    {
        $data = [array_values($headers)];
        foreach ($rows as $row) {
            $data[] = array_values((array) $row);
        }

        $temporary = tempnam(sys_get_temp_dir(), 'codeplay-xlsx-');
        if ($temporary === false) {
            throw new RuntimeException('No fue posible preparar el archivo de Excel.');
        }

        $zip = new ZipArchive;
        if ($zip->open($temporary, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($temporary);
            throw new RuntimeException('No fue posible crear el archivo de Excel.');
        }

        $sheetName = $this->safeSheetName($sheetName);
        $zip->addFromString('[Content_Types].xml', $this->contentTypes());
        $zip->addFromString('_rels/.rels', $this->rootRelationships());
        $zip->addFromString('xl/workbook.xml', $this->workbook($sheetName));
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelationships());
        $zip->addFromString('xl/styles.xml', $this->styles());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->worksheet($data));
        $zip->close();

        $contents = file_get_contents($temporary);
        @unlink($temporary);

        if ($contents === false) {
            throw new RuntimeException('No fue posible leer el archivo de Excel generado.');
        }

        return $contents;
    }

    private function worksheet(array $rows): string
    {
        $maximumColumns = max(1, ...array_map('count', $rows));
        $lastRow = max(1, count($rows));
        $lastColumn = $this->columnName($maximumColumns);
        $widths = array_fill(0, $maximumColumns, 12);
        $rowXml = [];

        foreach ($rows as $rowIndex => $row) {
            $number = $rowIndex + 1;
            $cells = [];
            foreach ($row as $columnIndex => $value) {
                $widths[$columnIndex] = min(48, max($widths[$columnIndex], mb_strlen((string) ($value ?? '')) + 2));
                $reference = $this->columnName($columnIndex + 1).$number;
                $cells[] = $this->cell($reference, $value, $rowIndex === 0 ? 1 : 0);
            }
            $rowXml[] = '<row r="'.$number.'">'.implode('', $cells).'</row>';
        }

        $columns = [];
        foreach ($widths as $index => $width) {
            $position = $index + 1;
            $columns[] = '<col min="'.$position.'" max="'.$position.'" width="'.$width.'" customWidth="1"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            .'<cols>'.implode('', $columns).'</cols>'
            .'<sheetData>'.implode('', $rowXml).'</sheetData>'
            .'<autoFilter ref="A1:'.$lastColumn.$lastRow.'"/>'
            .'</worksheet>';
    }

    private function cell(string $reference, mixed $value, int $style): string
    {
        if (is_int($value) || is_float($value)) {
            return '<c r="'.$reference.'" s="'.$style.'" t="n"><v>'.$value.'</v></c>';
        }

        if (is_bool($value)) {
            return '<c r="'.$reference.'" s="'.$style.'" t="b"><v>'.($value ? '1' : '0').'</v></c>';
        }

        $text = preg_replace('/[^\P{C}\t\r\n]/u', '', (string) ($value ?? '')) ?? '';

        return '<c r="'.$reference.'" s="'.$style.'" t="inlineStr"><is><t xml:space="preserve">'
            .$this->xml($text).'</t></is></c>';
    }

    private function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'</Types>';
    }

    private function rootRelationships(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    private function workbook(string $sheetName): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="'.$this->xml($sheetName).'" sheetId="1" r:id="rId1"/></sheets>'
            .'</workbook>';
    }

    private function workbookRelationships(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    private function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="2"><font><sz val="11"/><name val="Calibri"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Calibri"/></font></fonts>'
            .'<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF0B2A35"/><bgColor indexed="64"/></patternFill></fill></fills>'
            .'<borders count="2"><border/><border><left style="thin"><color rgb="FFB8CCC8"/></left><right style="thin"><color rgb="FFB8CCC8"/></right><top style="thin"><color rgb="FFB8CCC8"/></top><bottom style="thin"><color rgb="FFB8CCC8"/></bottom></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="2"><xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"/><xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center" vertical="center"/></xf></cellXfs>'
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            .'</styleSheet>';
    }

    private function safeSheetName(string $name): string
    {
        $name = preg_replace('~[\\\\/?*\\[\\]:]~u', ' ', trim($name)) ?: 'Reporte';

        return mb_substr($name, 0, 31);
    }

    private function columnName(int $number): string
    {
        $name = '';
        while ($number > 0) {
            $number--;
            $name = chr(65 + ($number % 26)).$name;
            $number = intdiv($number, 26);
        }

        return $name;
    }

    private function xml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
