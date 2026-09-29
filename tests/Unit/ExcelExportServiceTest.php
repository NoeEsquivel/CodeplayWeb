<?php

namespace Tests\Unit;

use App\Services\ExcelExportService;
use Tests\TestCase;
use ZipArchive;

class ExcelExportServiceTest extends TestCase
{
    public function test_it_builds_a_valid_excel_workbook_with_headers_and_values(): void
    {
        $contents = app(ExcelExportService::class)->build(
            ['Alumno', 'Calificación'],
            [['Ana López', 95.5], ['Mario Pérez', 82]],
            'Calificaciones'
        );

        $this->assertStringStartsWith('PK', $contents);
        $temporary = tempnam(sys_get_temp_dir(), 'xlsx-test-');
        file_put_contents($temporary, $contents);
        $zip = new ZipArchive();
        $this->assertTrue($zip->open($temporary) === true);
        $worksheet = $zip->getFromName('xl/worksheets/sheet1.xml');
        $workbook = $zip->getFromName('xl/workbook.xml');
        $zip->close();
        @unlink($temporary);

        $this->assertIsString($worksheet);
        $this->assertStringContainsString('Alumno', $worksheet);
        $this->assertStringContainsString('Ana López', $worksheet);
        $this->assertStringContainsString('<v>95.5</v>', $worksheet);
        $this->assertStringContainsString('name="Calificaciones"', (string) $workbook);
    }
}
