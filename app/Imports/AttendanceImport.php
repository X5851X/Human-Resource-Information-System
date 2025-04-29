<?php

namespace App\Imports;

use App\Models\Attendance;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class AttendanceImport implements 
    ToCollection, 
    WithHeadingRow, 
    WithValidation, 
    WithMapping, 
    WithBatchInserts, 
    WithChunkReading,
    SkipsOnError
{
    use Importable, SkipsErrors;

    protected $warnings = [];
    protected $rowCount = 0;
    protected $invalidDates = 0;

    /**
     * Process collection of rows
     *
     * @param Collection $rows
     * @return void
     */
    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            $this->warnings[] = 'File tidak berisi data atau format tidak sesuai.';
            return;
        }

        $this->rowCount = 0;
        $this->invalidDates = 0;

        foreach ($rows as $row) {
            // Skip empty rows
            if (empty($row['nama_kegiatan']) && empty($row['nama'])) {
                continue;
            }

            try {
                // Parse date with error handling
                $date = $this->parseDate($row['tanggal_kegiatan']);
                
                // Create attendance record
                Attendance::create([
                    'tanggal_kegiatan' => $date,
                    'nama_kegiatan' => $row['nama_kegiatan'],
                    'nama' => $row['nama'],
                    'keterangan' => $row['keterangan'] ?? null,
                ]);
                
                $this->rowCount++;
            } catch (\Exception $e) {
                $this->warnings[] = "Data dengan kegiatan '{$row['nama_kegiatan']}' dilewati: " . $e->getMessage();
                $this->invalidDates++;
            }
        }
        
        if ($this->invalidDates > 0) {
            $this->warnings[] = "{$this->invalidDates} data dilewati karena format tanggal tidak valid.";
        }
    }

    /**
     * Parse various date formats
     *
     * @param mixed $value
     * @return string
     */
    protected function parseDate($value)
    {
        if (empty($value)) {
            throw new \Exception("Tanggal kegiatan tidak boleh kosong");
        }
        
        // Handle Excel date numbers
        if (is_numeric($value)) {
            return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value))->format('Y-m-d');
        }
        
        // Try to parse as string date
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            throw new \Exception("Format tanggal '{$value}' tidak valid");
        }
    }

    /**
     * Map Excel columns to model attributes
     *
     * @param array $row
     * @return array
     */
    public function map($row): array
    {
        // Map Excel columns to attributes
        // This handles different column name variations
        return [
            'tanggal_kegiatan' => $row['tanggal_kegiatan'] ?? null,
            'nama_kegiatan' => $row['nama_kegiatan'] ?? null,
            'nama' => $row['nama'] ?? null,
            'keterangan' => $row['keterangan'] ?? null,
        ];
    }

    /**
     * Validation rules
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'nama_kegiatan' => 'required|string|max:255',
            'tanggal_kegiatan' => 'required',
            'nama' => 'required|string|max:255',
            'keterangan' => 'nullable|string|max:255',
        ];
    }

    /**
     * Custom validation messages
     *
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'nama_kegiatan.required' => 'Kolom nama kegiatan wajib diisi',
            'tanggal_kegiatan.required' => 'Kolom tanggal kegiatan wajib diisi',
            'nama.required' => 'Kolom nama wajib diisi',
        ];
    }

    /**
     * Configure the chunk size for processing large files
     *
     * @return int
     */
    public function chunkSize(): int
    {
        return 1000;
    }

    /**
     * Configure batch size for better performance
     *
     * @return int
     */
    public function batchSize(): int
    {
        return 500;
    }

    /**
     * Get import warnings
     *
     * @return array
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * Get row count
     *
     * @return int
     */
    public function getRowCount(): int
    {
        return $this->rowCount;
    }
}