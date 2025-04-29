<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\EmployeeImport;
use App\Imports\AttendanceImport;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;
use Symfony\Component\HttpFoundation\Response;

class UploadController extends Controller
{
    /**
     * Handle file upload and import based on type
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        // Validate the uploaded file
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:5120',
            'type' => 'required|in:kepegawaian,daftar_hadir',
            'update_existing' => 'nullable|boolean', // New parameter for handling existing records
        ], [
            'file.required' => 'File tidak boleh kosong',
            'file.file' => 'Upload harus berupa file',
            'file.max' => 'Ukuran file maksimal 5MB',
            'type.required' => 'Tipe data harus dipilih',
            'type.in' => 'Tipe data tidak valid',
        ]);

        if ($validator->fails()) {
            Log::warning('Upload validation failed: ' . json_encode($validator->errors()->toArray()));
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengunggah file. Silakan periksa kembali.',
                'errors' => $validator->errors()
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $file = $request->file('file');
        $rowCount = 0;
        $updateExisting = $request->boolean('update_existing', false);

        try {
            // Log upload attempt
            Log::info('File upload attempt', [
                'type' => $request->type,
                'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
                'update_existing' => $updateExisting
            ]);
            
            // Wrap import in transaction for better data integrity
            DB::beginTransaction();
            
            if ($request->type == 'kepegawaian') {
                $import = new EmployeeImport($updateExisting);
                Excel::import($import, $file);
                $rowCount = $import->getRowCount();
                $warnings = $import->getWarnings();
            } elseif ($request->type == 'daftar_hadir') {
                $import = new AttendanceImport();
                Excel::import($import, $file);
                $rowCount = $import->getRowCount();
                $warnings = $import->getWarnings();
            }
            
            DB::commit();

            // Build success message
            $message = "File berhasil diunggah. {$rowCount} data berhasil diproses.";
            
            // Add warnings if any
            if (!empty($warnings)) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'warnings' => $warnings,
                    'imported' => $rowCount
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'imported' => $rowCount
            ]);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();
            
            // Handle validation errors from Excel import
            $failures = $e->failures();
            $errorMessages = [];
            
            foreach ($failures as $failure) {
                $errorMessages[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            }
            
            Log::error('Excel validation error during import: ', $errorMessages);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengimpor data',
                'errorMessages' => $errorMessages
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
                
        } catch (Exception $e) {
            DB::rollBack();
            
            // Handle general exceptions
            Log::error('Import error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Show the upload form - for SPA, just redirect to dashboard
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function showUploadForm()
    {
        return redirect()->route('dashboard');
    }
}