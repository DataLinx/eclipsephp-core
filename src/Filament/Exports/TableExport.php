<?php

namespace Eclipse\Core\Filament\Exports;

use Maatwebsite\Excel\Excel;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class TableExport extends ExcelExport
{
    public function setUp()
    {
        $this
            ->fromTable()
            ->askForWriterType(Excel::XLSX, $this->getWriterTypeOptions());
    }

    protected function getWriterTypeOptions(): array
    {
        return [
            Excel::XLSX => 'XLSX',
            Excel::CSV => 'CSV',
            Excel::ODS => 'ODS',
        ];
    }
}
