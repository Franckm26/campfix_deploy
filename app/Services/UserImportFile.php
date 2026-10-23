<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Shuchkin\SimpleXLSX;
use ZipArchive;

class UserImportFile
{
    private const MAX_ROWS = 10000;
    private const MAX_COLUMNS = 100;
    private const MAX_EXPANDED_BYTES = 20 * 1024 * 1024;

    public static function rules(): array
    {
        return ['required', 'file', 'max:2048', 'extensions:csv,txt,xlsx',
            'mimetypes:text/plain,text/csv,text/tab-separated-values,application/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/zip'];
    }

    public function rows(UploadedFile $file, bool $masterlist, bool $allSheets): array
    {
        Validator::make(['file' => $file], ['file' => self::rules()])->validate();
        $rows = [];
        if (strtolower($file->getClientOriginalExtension()) === 'xlsx') {
            $this->checkWorkbook($file->getRealPath());
            $xlsx = SimpleXLSX::parse($file->getRealPath());
            if (!$xlsx) {
                $this->reject('The workbook is invalid. Please upload a valid XLSX file.');
            }
            $sheets = $allSheets ? array_keys($xlsx->sheetNames()) : [0];
            foreach ($sheets as $index) {
                $dimension = $xlsx->dimension($index);
                if ($dimension[0] > self::MAX_COLUMNS || $dimension[1] > self::MAX_ROWS) {
                    $this->reject('Imports may contain at most 10,000 rows and 100 columns.');
                }
                $sheetRows = [];
                foreach ($xlsx->readRows($index) as $row) {
                    $this->append($sheetRows, $row);
                }
                if ($allSheets) {
                    $sheetRows = StudentMasterlistRows::normalize($sheetRows);
                    if ($rows !== []) {
                        array_shift($sheetRows);
                    }
                }
                foreach ($sheetRows as $row) {
                    $this->append($rows, $row);
                }
            }
        } else {
            $content = file_get_contents($file->getRealPath());
            // Treat CSV/TXT as text data only; reject binary files and embedded code.
            if (preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F]|<\?(?:php|=)|<script\b/i', $content)) {
                $this->reject('The import must contain plain CSV or tab-separated text.');
            }
            $handle = fopen($file->getRealPath(), 'r');
            try {
                while (($row = fgetcsv($handle, 0, $masterlist ? "\t" : ',')) !== false) {
                    $this->append($rows, $row);
                }
            } finally {
                fclose($handle);
            }
            if ($allSheets) {
                $rows = StudentMasterlistRows::normalize($rows);
            }
        }
        if (count($rows) < 2) {
            $this->reject('The import must contain a header and at least one data row.');
        }
        return $rows;
    }

    private function append(array &$rows, array $row): void
    {
        if (count($rows) >= self::MAX_ROWS || count($row) > self::MAX_COLUMNS) {
            $this->reject('Imports may contain at most 10,000 rows and 100 columns.');
        }
        $rows[] = $row;
    }

    private function checkWorkbook(string $path): void
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            $this->reject('The workbook is invalid. Please upload a valid XLSX file.');
        }
        try {
            if ($zip->numFiles > 1000 || $zip->locateName('[Content_Types].xml') === false
                || $zip->locateName('xl/workbook.xml') === false) {
                $this->reject('The XLSX workbook structure is invalid or too large.');
            }
            $bytes = 0;
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $entry = $zip->statIndex($index);
                $bytes += $entry['size'];
                if ($bytes > self::MAX_EXPANDED_BYTES) {
                    $this->reject('The expanded workbook exceeds the 20 MB limit.');
                }
                if (preg_match('~(?:vbaProject\.bin$|(?:^|/)embeddings/|(?:^|/)externalLinks/)~i', $entry['name'])) {
                    $this->reject('Workbooks with macros, embedded objects or external links are not supported.');
                }
                if (preg_match('/\.(xml|rels)$/i', $entry['name'])
                    && preg_match('/<!DOCTYPE|<!ENTITY/i', $zip->getFromIndex($index))) {
                    $this->reject('Workbook XML declarations are not supported.');
                }
            }
        } finally {
            $zip->close();
        }
    }

    private function reject(string $message): never
    {
        throw ValidationException::withMessages(['file' => $message]);
    }
}
