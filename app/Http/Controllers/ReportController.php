<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    /**
     * Generate attendance report based on filter criteria
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function generateReport(Request $request): JsonResponse
    {
        try {
            $reportType = $request->input('reportType');
            $data = [];
            $summary = [
                'present' => 0,
                'late' => 0,
                'absent' => 0,
                'permit' => 0,
            ];

            switch ($reportType) {
                case 'daily':
                    $data = $this->getDailyReport($request);
                    break;
                case 'monthly':
                    $data = $this->getMonthlyReport($request);
                    break;
                case 'department':
                    $data = $this->getDepartmentReport($request);
                    break;
                case 'employee':
                    $data = $this->getEmployeeReport($request);
                    break;
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid report type'
                    ], 400);
            }

            // Calculate summary
            foreach ($data as $record) {
                switch ($record->status) {
                    case 'Hadir':
                        $summary['present']++;
                        break;
                    case 'Terlambat':
                        $summary['late']++;
                        break;
                    case 'Tidak Hadir':
                        $summary['absent']++;
                        break;
                    case 'Izin':
                        $summary['permit']++;
                        break;
                }
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'summary' => $summary
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get daily attendance report
     * 
     * @param Request $request
     * @return array
     */
    private function getDailyReport(Request $request): array
    {
        $date = $request->input('date');
        
        return Attendance::join('employees', 'attendances.employee_id', '=', 'employees.id')
            ->whereDate('attendances.date', $date)
            ->select(
                'employees.id as employee_id',
                'employees.name as nama',
                'attendances.date as tanggal',
                'attendances.time_in as jamMasuk',
                'attendances.time_out as jamKeluar',
                'attendances.status',
                'attendances.notes as keterangan',
                'employees.department_id'
            )
            ->get()
            ->toArray();
    }

    /**
     * Get monthly attendance report
     * 
     * @param Request $request
     * @return array
     */
    private function getMonthlyReport(Request $request): array
    {
        $month = $request->input('month');
        $year = $request->input('year');
        
        return Attendance::join('employees', 'attendances.employee_id', '=', 'employees.id')
            ->whereMonth('attendances.date', $month)
            ->whereYear('attendances.date', $year)
            ->select(
                'employees.id as employee_id',
                'employees.name as nama',
                'attendances.date as tanggal',
                'attendances.time_in as jamMasuk',
                'attendances.time_out as jamKeluar',
                'attendances.status',
                'attendances.notes as keterangan',
                'employees.department_id'
            )
            ->get()
            ->toArray();
    }

    /**
     * Get department attendance report
     * 
     * @param Request $request
     * @return array
     */
    private function getDepartmentReport(Request $request): array
    {
        $month = $request->input('month');
        $year = $request->input('year');
        $departmentId = $request->input('department_id');
        
        $query = Attendance::join('employees', 'attendances.employee_id', '=', 'employees.id')
            ->whereMonth('attendances.date', $month)
            ->whereYear('attendances.date', $year);
        
        if ($departmentId) {
            $query->where('employees.department_id', $departmentId);
        }
        
        return $query->select(
            'employees.id as employee_id',
            'employees.name as nama',
            'attendances.date as tanggal',
            'attendances.time_in as jamMasuk',
            'attendances.time_out as jamKeluar',
            'attendances.status',
            'attendances.notes as keterangan',
            'employees.department_id'
        )
        ->get()
        ->toArray();
    }

    /**
     * Get employee attendance report
     * 
     * @param Request $request
     * @return array
     */
    private function getEmployeeReport(Request $request): array
    {
        $month = $request->input('month');
        $year = $request->input('year');
        $employeeId = $request->input('employee_id');
        
        $query = Attendance::join('employees', 'attendances.employee_id', '=', 'employees.id')
            ->whereMonth('attendances.date', $month)
            ->whereYear('attendances.date', $year);
        
        if ($employeeId) {
            $query->where('employees.id', $employeeId);
        }
        
        return $query->select(
            'employees.id as employee_id',
            'employees.name as nama',
            'attendances.date as tanggal',
            'attendances.time_in as jamMasuk',
            'attendances.time_out as jamKeluar',
            'attendances.status',
            'attendances.notes as keterangan',
            'employees.department_id'
        )
        ->get()
        ->toArray();
    }

    /**
     * Export attendance report to Excel
     * 
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportToExcel(Request $request)
    {
        try {
            $reportType = $request->input('reportType');
            $data = [];
            
            switch ($reportType) {
                case 'daily':
                    $data = $this->getDailyReport($request);
                    $title = 'Laporan Harian - ' . Carbon::parse($request->input('date'))->format('d/m/Y');
                    break;
                case 'monthly':
                    $data = $this->getMonthlyReport($request);
                    $title = 'Laporan Bulanan - ' . $this->getMonthName($request->input('month')) . ' ' . $request->input('year');
                    break;
                case 'department':
                    $data = $this->getDepartmentReport($request);
                    $departmentName = 'Semua Departemen';
                    if ($request->input('department_id')) {
                        $department = DB::table('departments')->where('id', $request->input('department_id'))->first();
                        $departmentName = $department ? $department->name : 'Departemen Tidak Ditemukan';
                    }
                    $title = 'Laporan Departemen - ' . $departmentName . ' - ' . $this->getMonthName($request->input('month')) . ' ' . $request->input('year');
                    break;
                case 'employee':
                    $data = $this->getEmployeeReport($request);
                    $employeeName = 'Semua Pegawai';
                    if ($request->input('employee_id')) {
                        $employee = Employee::find($request->input('employee_id'));
                        $employeeName = $employee ? $employee->name : 'Pegawai Tidak Ditemukan';
                    }
                    $title = 'Laporan Pegawai - ' . $employeeName . ' - ' . $this->getMonthName($request->input('month')) . ' ' . $request->input('year');
                    break;
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid report type'
                    ], 400);
            }
            
            // Create Excel file
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set title
            $sheet->setCellValue('A1', 'LAPORAN KEHADIRAN');
            $sheet->setCellValue('A2', $title);
            
            // Set headers
            $sheet->setCellValue('A4', 'No');
            $sheet->setCellValue('B4', 'Nama');
            $sheet->setCellValue('C4', 'Tanggal');
            $sheet->setCellValue('D4', 'Jam Masuk');
            $sheet->setCellValue('E4', 'Jam Keluar');
            $sheet->setCellValue('F4', 'Status');
            $sheet->setCellValue('G4', 'Keterangan');
            
            // Fill data
            $row = 5;
            foreach ($data as $index => $record) {
                $sheet->setCellValue('A' . $row, $index + 1);
                $sheet->setCellValue('B' . $row, $record['nama']);
                $sheet->setCellValue('C' . $row, Carbon::parse($record['tanggal'])->format('d/m/Y'));
                $sheet->setCellValue('D' . $row, $record['jamMasuk'] ?? '-');
                $sheet->setCellValue('E' . $row, $record['jamKeluar'] ?? '-');
                $sheet->setCellValue('F' . $row, $record['status']);
                $sheet->setCellValue('G' . $row, $record['keterangan'] ?? '-');
                $row++;
            }
            
            // Create summary
            $present = count(array_filter($data, function($item) {
                return $item['status'] === 'Hadir';
            }));
            
            $late = count(array_filter($data, function($item) {
                return $item['status'] === 'Terlambat';
            }));
            
            $absent = count(array_filter($data, function($item) {
                return $item['status'] === 'Tidak Hadir';
            }));
            
            $permit = count(array_filter($data, function($item) {
                return $item['status'] === 'Izin';
            }));
            
            $row += 2;
            $sheet->setCellValue('A' . $row, 'RINGKASAN');
            $row++;
            $sheet->setCellValue('A' . $row, 'Hadir:');
            $sheet->setCellValue('B' . $row, $present);
            $row++;
            $sheet->setCellValue('A' . $row, 'Terlambat:');
            $sheet->setCellValue('B' . $row, $late);
            $row++;
            $sheet->setCellValue('A' . $row, 'Tidak Hadir:');
            $sheet->setCellValue('B' . $row, $absent);
            $row++;
            $sheet->setCellValue('A' . $row, 'Izin:');
            $sheet->setCellValue('B' . $row, $permit);
            
            // Auto width columns
            foreach (range('A', 'G') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
            
            // Format styling
            $sheet->getStyle('A1:G1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A4:G4')->getFont()->setBold(true);
            $sheet->getStyle('A' . ($row - 4) . ':A' . $row)->getFont()->setBold(true);
            
            // Create file
            $filename = 'attendance_report_' . date('YmdHis') . '.xlsx';
            $writer = new Xlsx($spreadsheet);
            $path = storage_path('app/public/reports/' . $filename);
            
            // Ensure directory exists
            if (!file_exists(storage_path('app/public/reports/'))) {
                mkdir(storage_path('app/public/reports/'), 0755, true);
            }
            
            $writer->save($path);
            
            return response()->download($path, $filename)->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to export report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get month name in Indonesian
     * 
     * @param int $month
     * @return string
     */
    private function getMonthName(int $month): string
    {
        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];
        
        return $months[$month] ?? '';
    }

    /**
     * Generate PDF report for printing
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function generatePDF(Request $request)
    {
        try {
            $reportType = $request->input('reportType');
            $data = [];
            
            switch ($reportType) {
                case 'daily':
                    $data = $this->getDailyReport($request);
                    $title = 'Laporan Harian - ' . Carbon::parse($request->input('date'))->format('d/m/Y');
                    break;
                case 'monthly':
                    $data = $this->getMonthlyReport($request);
                    $title = 'Laporan Bulanan - ' . $this->getMonthName($request->input('month')) . ' ' . $request->input('year');
                    break;
                case 'department':
                    $data = $this->getDepartmentReport($request);
                    $departmentName = 'Semua Departemen';
                    if ($request->input('department_id')) {
                        $department = DB::table('departments')->where('id', $request->input('department_id'))->first();
                        $departmentName = $department ? $department->name : 'Departemen Tidak Ditemukan';
                    }
                    $title = 'Laporan Departemen - ' . $departmentName . ' - ' . $this->getMonthName($request->input('month')) . ' ' . $request->input('year');
                    break;
                case 'employee':
                    $data = $this->getEmployeeReport($request);
                    $employeeName = 'Semua Pegawai';
                    if ($request->input('employee_id')) {
                        $employee = Employee::find($request->input('employee_id'));
                        $employeeName = $employee ? $employee->name : 'Pegawai Tidak Ditemukan';
                    }
                    $title = 'Laporan Pegawai - ' . $employeeName . ' - ' . $this->getMonthName($request->input('month')) . ' ' . $request->input('year');
                    break;
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid report type'
                    ], 400);
            }
            
            // Calculate summary
            $summary = [
                'present' => count(array_filter($data, function($item) {
                    return $item['status'] === 'Hadir';
                })),
                'late' => count(array_filter($data, function($item) {
                    return $item['status'] === 'Terlambat';
                })),
                'absent' => count(array_filter($data, function($item) {
                    return $item['status'] === 'Tidak Hadir';
                })),
                'permit' => count(array_filter($data, function($item) {
                    return $item['status'] === 'Izin';
                }))
            ];
            
            // Generate PDF using view
            $pdf = app('dompdf.wrapper');
            $pdf->loadView('reports.attendance_pdf', [
                'title' => $title,
                'data' => $data,
                'summary' => $summary,
                'date' => Carbon::now()->format('d/m/Y H:i:s')
            ]);
            
            return $pdf->stream('attendance_report.pdf');
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF: ' . $e->getMessage()
            ], 500);
        }
    }
}