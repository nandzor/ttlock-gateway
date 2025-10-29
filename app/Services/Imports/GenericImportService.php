<?php

namespace App\Services\Imports;

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\UploadedFile;

class GenericImportService
{
    /**
     * Import an uploaded Excel file using a given Import class.
     *
     * @param class-string $importClass A class implementing Maatwebsite Import
     * @param UploadedFile $file The uploaded .xlsx/.xls file
     */
    public function import(string $importClass, UploadedFile $file): void
    {
        Excel::import(new $importClass(), $file);
    }
}


