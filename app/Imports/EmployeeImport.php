<?php

namespace App\Imports;

use App\Models\Employee;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Facades\Log;

class EmployeeImport implements 
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
    protected $skippedCount = 0;
    protected $updateExisting = false;

    /**
     * Constructor to allow configurable behavior
     * 
     * @param bool $updateExisting Whether to update existing employees or skip them
     */
    public function __construct($updateExisting = false)
    {
        $this->updateExisting = $updateExisting;
    }

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

        // Log the first few rows for debugging
        $sampleRows = $rows->take(3);
        Log::info('Sample rows for debugging:', $sampleRows->toArray());

        $uniqueNIPs = [];
        $this->rowCount = 0;
        $this->skippedCount = 0;

        foreach ($rows as $rowIndex => $row) {
            // Skip rows where all required fields are empty (likely blank row)
            if ((!isset($row['nama']) || empty($row['nama'])) && 
                (!isset($row['nomor_induk_pegawai']) || empty($row['nomor_induk_pegawai']))) {
                continue;
            }

            // Check if row has required data
            if (!isset($row['nama']) || !isset($row['nomor_induk_pegawai']) || !isset($row['jabatan']) || !isset($row['bidang'])) {
                Log::warning('Row missing required data: ' . json_encode($row));
                
                // Build error message showing what columns are missing
                $missingColumns = [];
                if (!isset($row['nama'])) $missingColumns[] = 'nama';
                if (!isset($row['nomor_induk_pegawai'])) $missingColumns[] = 'nomor_induk_pegawai';
                if (!isset($row['jabatan'])) $missingColumns[] = 'jabatan';
                if (!isset($row['bidang'])) $missingColumns[] = 'bidang';
                
                $this->warnings[] = "Baris " . ($rowIndex + 2) . " tidak memiliki data yang diperlukan. Kolom yang hilang: " . implode(', ', $missingColumns);
                $this->skippedCount++;
                continue;
            }
            
            $nip = $this->cleanNIP($row['nomor_induk_pegawai']);
            
            // Skip if NIP is empty
            if (empty($nip)) {
                $this->warnings[] = "Baris " . ($rowIndex + 2) . " memiliki NIP kosong (baris diabaikan)";
                $this->skippedCount++;
                continue;
            }

            // Check for duplicate NIP within the file
            if (in_array($nip, $uniqueNIPs)) {
                $this->warnings[] = "Duplikasi NIP '{$nip}' dalam file (baris " . ($rowIndex + 2) . " diabaikan)";
                $this->skippedCount++;
                continue;
            }
            
            // Check for existing NIP in database
            $existingEmployee = Employee::where('nomor_induk_pegawai', $nip)->first();
            if ($existingEmployee) {
                if ($this->updateExisting) {
                    // Update existing employee instead of skipping
                    try {
                        $existingEmployee->update([
                            'nama' => $row['nama'],
                            'jabatan' => $row['jabatan'],
                            'keterangan' => $row['keterangan'] ?? null,
                            'bidang' => $row['bidang'],
                        ]);
                        
                        $this->rowCount++;
                        $uniqueNIPs[] = $nip;
                        $this->warnings[] = "NIP '{$nip}' telah diperbarui (baris " . ($rowIndex + 2) . ")";
                    } catch (\Exception $e) {
                        Log::error('Error updating employee: ' . $e->getMessage());
                        Log::error('Row data: ' . json_encode($row));
                        $this->warnings[] = "Gagal memperbarui data karyawan pada baris " . ($rowIndex + 2) . ": " . $e->getMessage();
                        $this->skippedCount++;
                    }
                } else {
                    // Skip existing employee
                    $this->warnings[] = "NIP '{$nip}' sudah terdaftar untuk '{$existingEmployee->nama}' (baris " . ($rowIndex + 2) . " diabaikan)";
                    $this->skippedCount++;
                }
                continue;
            }
            
            $uniqueNIPs[] = $nip;
            
            try {
                // Create new employee
                Employee::create([
                    'nama' => $row['nama'],
                    'jabatan' => $row['jabatan'],
                    'nomor_induk_pegawai' => $nip,
                    'keterangan' => $row['keterangan'] ?? null,
                    'bidang' => $row['bidang'],
                ]);
                
                $this->rowCount++;
            } catch (\Exception $e) {
                Log::error('Error creating employee: ' . $e->getMessage());
                Log::error('Row data: ' . json_encode($row));
                $this->warnings[] = "Gagal membuat data karyawan pada baris " . ($rowIndex + 2) . ": " . $e->getMessage();
                $this->skippedCount++;
            }
        }
        
        if ($this->skippedCount > 0) {
            $this->warnings[] = "{$this->skippedCount} data dilewati karena duplikasi atau sudah ada.";
        }
    }

    /**
     * Clean and standardize NIP format
     *
     * @param string $nip
     * @return string
     */
    protected function cleanNIP($nip)
    {
        if (empty($nip)) {
            return '';
        }
        
        // Handle different NIP formats and ensure consistency
        $nip = is_string($nip) ? trim($nip) : (string)$nip;
        
        // Remove any non-alphanumeric characters if needed
        // Uncomment the line below if you want to strip spaces, dots, etc.
        // $nip = preg_replace('/[^a-zA-Z0-9]/', '', $nip);
        
        return $nip;
    }

    /**
     * Map Excel columns to model attributes with flexible column name recognition
     *
     * @param array $row
     * @return array
     */
    public function map($row): array
    {
        // Check if any row needs debugging
        if (isset($row['nomor_induk_pegawai']) && strpos($row['nomor_induk_pegawai'], '3216066') !== false) {
            Log::info('Mapping special NIP row:', $row);
        }
        
        // Handle possible variations in column names
        $nama = $row['nama'] ?? $row['name'] ?? $row['employee_name'] ?? null;
        $jabatan = $row['jabatan'] ?? $row['position'] ?? $row['job_title'] ?? null;
        $nip = $row['nomor_induk_pegawai'] ?? $row['nip'] ?? $row['employee_id'] ?? null;
        $keterangan = $row['keterangan'] ?? $row['notes'] ?? $row['description'] ?? null;
        $bidang = $row['bidang'] ?? $row['department'] ?? null;
        
        return [
            'nama' => $nama,
            'jabatan' => $jabatan,
            'nomor_induk_pegawai' => $this->cleanNIP($nip),
            'keterangan' => $keterangan,
            'bidang' => $bidang,
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
            'nama' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'nomor_induk_pegawai' => 'required|string|max:50',
            'keterangan' => 'nullable|string',
            'bidang' => 'required|string|max:255',
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
            'nama.required' => 'Kolom nama harus diisi',
            'nama.string' => 'Kolom nama harus berupa teks',
            'nama.max' => 'Kolom nama maksimal 255 karakter',
            'jabatan.required' => 'Kolom jabatan harus diisi',
            'jabatan.string' => 'Kolom jabatan harus berupa teks',
            'jabatan.max' => 'Kolom jabatan maksimal 255 karakter',
            'nomor_induk_pegawai.required' => 'Kolom NIP harus diisi',
            'nomor_induk_pegawai.string' => 'Kolom NIP harus berupa teks',
            'nomor_induk_pegawai.max' => 'Kolom NIP maksimal 50 karakter',
            'bidang.required' => 'Kolom bidang harus diisi',
            'bidang.string' => 'Kolom bidang harus berupa teks',
            'bidang.max' => 'Kolom bidang maksimal 255 karakter',
        ];
    }

    /**
     * Configure batch size for bulk inserts
     *
     * @return int
     */
    public function batchSize(): int
    {
        return 500;
    }

    /**
     * Configure chunk size for reading large Excel files
     *
     * @return int
     */
    public function chunkSize(): int
    {
        return 1000;
    }

    /**
     * Get warnings during import
     *
     * @return array
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * Get count of imported rows
     *
     * @return int
     */
    public function getRowCount(): int
    {
        return $this->rowCount;
    }

    /**
     * Get count of skipped rows
     *
     * @return int
     */
    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }

    /**
     * Handle error during import
     *
     * @param \Throwable $e
     */
    public function onError(\Throwable $e)
    {
        Log::error('Error during employee import: ' . $e->getMessage());
        Log::error($e->getTraceAsString());
        $this->warnings[] = 'Error saat import: ' . $e->getMessage();
    }

    /**
     * Define starting row (after header)
     *
     * @return int
     */
    public function headingRow(): int
    {
        return 1;
    }
}