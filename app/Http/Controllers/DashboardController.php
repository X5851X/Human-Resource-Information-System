<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $bidang = $request->input('bidang');
        
        // Start query with base employee query
        $employeeQuery = Employee::query();
        
        // Apply bidang filter if provided
        if ($bidang) {
            $employeeQuery->where('bidang', $bidang);
        }
        
        // Get total employees count
        $totalEmployees = $employeeQuery->count();
        
        // Start attendance query
        $attendanceQuery = Attendance::whereBetween('tanggal_kegiatan', [$startDate, $endDate]);
        
        // Apply bidang filter to activities if provided
        if ($bidang) {
            $attendanceQuery->whereHas('employee', function($query) use ($bidang) {
                $query->where('bidang', $bidang);
            });
        }
        
        // Count total activities in the date range
        $totalActivities = $attendanceQuery->distinct('nama_kegiatan')->count('nama_kegiatan');
        
        // Count total participants in the date range
        $totalParticipants = $attendanceQuery->count();
        
        // Calculate attendance rate (percentage of employees who participated in at least one activity)
        $participatingEmployees = $attendanceQuery->distinct('employee_id')->count('employee_id');
        $attendanceRate = $totalEmployees > 0 
            ? round(($participatingEmployees / $totalEmployees) * 100, 1) 
            : 0;
        
        return response()->json([
            'totalEmployees' => $totalEmployees,
            'attendanceRate' => $attendanceRate,
            'totalActivities' => $totalActivities,
            'totalParticipants' => $totalParticipants
        ]);
    }
    
    /**
     * Get chart data for attendance
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChartData(Request $request)
    {
        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date'));
        $viewMode = $request->input('view_mode', 'monthly');
        $bidang = $request->input('bidang');
        
        $result = [];
        
        // Generate appropriate periods based on view mode
        if ($viewMode === 'weekly') {
            // For weekly view, show each day
            $currentDate = clone $startDate;
            while ($currentDate <= $endDate) {
                $dayData = $this->getAttendanceDataForDate($currentDate, $bidang);
                $result[] = [
                    'label' => $currentDate->format('d'),
                    'present' => $dayData['present'],
                    'late' => $dayData['late'],
                    'absent' => $dayData['absent'],
                    'permit' => $dayData['permit']
                ];
                $currentDate->addDay();
            }
        } elseif ($viewMode === 'monthly') {
            // For monthly view, group by week
            $currentDate = clone $startDate;
            $weekNumber = 1;
            
            while ($currentDate <= $endDate) {
                $weekEndDate = (clone $currentDate)->addDays(6);
                if ($weekEndDate > $endDate) {
                    $weekEndDate = clone $endDate;
                }
                
                $weekData = $this->getAttendanceDataForDateRange($currentDate, $weekEndDate, $bidang);
                $result[] = [
                    'label' => "W{$weekNumber}",
                    'present' => $weekData['present'],
                    'late' => $weekData['late'],
                    'absent' => $weekData['absent'],
                    'permit' => $weekData['permit']
                ];
                
                $currentDate->addDays(7);
                $weekNumber++;
            }
        } elseif ($viewMode === 'quarterly') {
            // For quarterly view, group by month
            $currentDate = clone $startDate;
            $currentDate->day = 1; // Start from the first day of the month
            
            while ($currentDate <= $endDate) {
                $monthEndDate = (clone $currentDate)->endOfMonth();
                if ($monthEndDate > $endDate) {
                    $monthEndDate = clone $endDate;
                }
                
                $monthData = $this->getAttendanceDataForDateRange($currentDate, $monthEndDate, $bidang);
                $result[] = [
                    'label' => $currentDate->format('M'),
                    'present' => $monthData['present'],
                    'late' => $monthData['late'],
                    'absent' => $monthData['absent'],
                    'permit' => $monthData['permit']
                ];
                
                $currentDate->addMonth();
            }
        }
        
        return response()->json($result);
    }
    
    /**
     * Get recent activities data
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRecentActivities(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $bidang = $request->input('bidang');
        
        $query = Attendance::select(
                'tanggal_kegiatan',
                'nama_kegiatan',
                DB::raw('COUNT(DISTINCT employee_id) as jumlah_peserta')
            )
            ->whereBetween('tanggal_kegiatan', [$startDate, $endDate])
            ->groupBy('tanggal_kegiatan', 'nama_kegiatan')
            ->orderBy('tanggal_kegiatan', 'desc');
        
        // Apply bidang filter if provided
        if ($bidang) {
            $query->whereHas('employee', function($q) use ($bidang) {
                $q->where('bidang', $bidang);
            });
        }
        
        return response()->json($query->limit(5)->get());
    }
    
    /**
     * Get top employees data
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTopEmployees(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $bidang = $request->input('bidang');
        
        $query = DB::table('employees')
            ->select(
                'employees.id',
                'employees.nama',
                'employees.bidang',
                DB::raw('COUNT(DISTINCT attendances.nama_kegiatan) as kegiatan_count')
            )
            ->leftJoin('attendances', 'employees.id', '=', 'attendances.employee_id')
            ->whereBetween('attendances.tanggal_kegiatan', [$startDate, $endDate])
            ->groupBy('employees.id', 'employees.nama', 'employees.bidang')
            ->orderBy('kegiatan_count', 'desc');
        
        // Apply bidang filter if provided
        if ($bidang) {
            $query->where('employees.bidang', $bidang);
        }
        
        return response()->json($query->limit(5)->get());
    }
    
    /**
     * Get department performance data
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDepartmentPerformance(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $bidang = $request->input('bidang');
        
        $query = DB::table('employees')
            ->select(
                'employees.bidang as name',
                DB::raw('COUNT(DISTINCT attendances.nama_kegiatan) as count')
            )
            ->leftJoin('attendances', 'employees.id', '=', 'attendances.employee_id')
            ->whereBetween('attendances.tanggal_kegiatan', [$startDate, $endDate])
            ->groupBy('employees.bidang');
        
        // Apply bidang filter if provided
        if ($bidang) {
            $query->where('employees.bidang', $bidang);
        }
        
        $results = $query->get();
        
        // Calculate percentage for each department
        $totalActivities = $results->sum('count');
        
        $formattedResults = $results->map(function($dept) use ($totalActivities) {
            $percentage = $totalActivities > 0 ? ($dept->count / $totalActivities) * 100 : 0;
            return [
                'name' => $dept->name ?: 'Lainnya',
                'count' => $dept->count,
                'percentage' => round($percentage, 1)
            ];
        });
        
        return response()->json($formattedResults);
    }
    
    /**
     * Export report
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function exportReport(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $format = strtolower($request->input('format', 'pdf'));
        $bidang = $request->input('bidang');
        $includeCharts = $request->input('include_charts', 0);
        $includeEmployeeDetails = $request->input('include_employee_details', 0);
        $includeSummary = $request->input('include_summary', 0);
        
        // Get report data
        $stats = $this->getStats($request)->getOriginalContent();
        
        // Get activities data based on filters
        $activitiesQuery = Attendance::select(
                'tanggal_kegiatan',
                'nama_kegiatan',
                DB::raw('COUNT(DISTINCT employee_id) as jumlah_peserta')
            )
            ->whereBetween('tanggal_kegiatan', [$startDate, $endDate])
            ->groupBy('tanggal_kegiatan', 'nama_kegiatan')
            ->orderBy('tanggal_kegiatan', 'desc');
        
        // Apply bidang filter if provided
        if ($bidang) {
            $activitiesQuery->whereHas('employee', function($query) use ($bidang) {
                $query->where('bidang', $bidang);
            });
        }
        
        $activities = $activitiesQuery->get();
        
        // Get employee details if requested
        $employeeDetails = [];
        if ($includeEmployeeDetails) {
            $employeeDetailsQuery = DB::table('employees')
                ->select(
                    'employees.nama',
                    'employees.bidang',
                    'employees.email',
                    'employees.phone',
                    DB::raw('COUNT(DISTINCT attendances.nama_kegiatan) as kegiatan_count')
                )
                ->leftJoin('attendances', 'employees.id', '=', 'attendances.employee_id')
                ->whereBetween('attendances.tanggal_kegiatan', [$startDate, $endDate])
                ->groupBy('employees.nama', 'employees.bidang', 'employees.email', 'employees.phone')
                ->orderBy('employees.bidang')
                ->orderBy('employees.nama');
            
            if ($bidang) {
                $employeeDetailsQuery->where('employees.bidang', $bidang);
            }
            
            $employeeDetails = $employeeDetailsQuery->get();
        }
        
        // Generate report based on format
        if ($format === 'pdf') {
            return $this->generatePdfReport($stats, $activities, $employeeDetails, $startDate, $endDate, $bidang, $includeCharts, $includeSummary);
        } elseif ($format === 'excel') {
            return $this->generateExcelReport($stats, $activities, $employeeDetails, $startDate, $endDate, $bidang, $includeCharts, $includeSummary);
        } elseif ($format === 'csv') {
            return $this->generateCsvReport($stats, $activities, $employeeDetails, $startDate, $endDate, $bidang);
        }
        
        return response()->json(['error' => 'Format tidak didukung'], 400);
    }
    
    /**
     * Generate PDF report
     */
    private function generatePdfReport($stats, $activities, $employeeDetails, $startDate, $endDate, $bidang, $includeCharts, $includeSummary)
    {
        // Here you would implement PDF generation using a library like DomPDF
        // For example: return PDF::loadView('reports.attendance', compact('stats', 'activities', 'employeeDetails', 'startDate', 'endDate', 'bidang', 'includeCharts', 'includeSummary'))->download('laporan-kegiatan.pdf');
        
        // For demonstration, we'll just return a success response
        return response()->json(['message' => 'PDF report generated successfully'], 200);
    }
    
    /**
     * Generate Excel report
     */
    private function generateExcelReport($stats, $activities, $employeeDetails, $startDate, $endDate, $bidang, $includeCharts, $includeSummary)
    {
        // Here you would implement Excel generation using a library like Maatwebsite/Laravel-Excel
        // For example: return Excel::download(new AttendanceExport($stats, $activities, $employeeDetails, $startDate, $endDate, $bidang, $includeCharts, $includeSummary), 'laporan-kegiatan.xlsx');
        
        // For demonstration, we'll just return a success response
        return response()->json(['message' => 'Excel report generated successfully'], 200);
    }
    
    /**
     * Generate CSV report
     */
    private function generateCsvReport($stats, $activities, $employeeDetails, $startDate, $endDate, $bidang)
    {
        // Here you would implement CSV generation
        // For example: return (new AttendanceExport($stats, $activities, $employeeDetails, $startDate, $endDate, $bidang))->download('laporan-kegiatan.csv', \Maatwebsite\Excel\Excel::CSV);
        
        // For demonstration, we'll just return a success response
        return response()->json(['message' => 'CSV report generated successfully'], 200);
    }
    
    /**
     * Get attendance data for a specific date
     * 
     * @param Carbon $date
     * @param string|null $bidang
     * @return array
     */
    private function getAttendanceDataForDate(Carbon $date, $bidang = null)
    {
        $query = Attendance::where('tanggal_kegiatan', $date->format('Y-m-d'));
        
        // Apply bidang filter if provided
        if ($bidang) {
            $query->whereHas('employee', function($q) use ($bidang) {
                $q->where('bidang', $bidang);
            });
        }
        
        // Count different attendance statuses
        // For demonstration purposes, we'll assume some status categories
        // In a real application, you'd adapt this to your actual data model
        
        $totalEmployees = Employee::when($bidang, function($q) use ($bidang) {
            return $q->where('bidang', $bidang);
        })->count();
        
        $present = $query->where('status', 'hadir')->count();
        $late = $query->where('status', 'terlambat')->count();
        $permit = $query->where('status', 'izin')->count();
        
        // Calculate absent as the remainder
        $totalPresent = $present + $late + $permit;
        $absent = $totalEmployees - $totalPresent;
        if ($absent < 0) $absent = 0;
        
        // Convert to percentage for chart display (0-100 range)
        $total = $totalEmployees > 0 ? $totalEmployees : 1; // Avoid division by zero
        
        return [
            'present' => round(($present / $total) * 100),
            'late' => round(($late / $total) * 100),
            'absent' => round(($absent / $total) * 100),
            'permit' => round(($permit / $total) * 100)
        ];
    }
    
    /**
     * Get attendance data for a date range
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string|null $bidang
     * @return array
     */
    private function getAttendanceDataForDateRange(Carbon $startDate, Carbon $endDate, $bidang = null)
    {
        $query = Attendance::whereBetween('tanggal_kegiatan', [
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        ]);
        
        // Apply bidang filter if provided
        if ($bidang) {
            $query->whereHas('employee', function($q) use ($bidang) {
                $q->where('bidang', $bidang);
            });
        }
        
        // Count different attendance statuses
        // For demonstration purposes, we'll use status categories
        $totalEmployees = Employee::when($bidang, function($q) use ($bidang) {
            return $q->where('bidang', $bidang);
        })->count();
        
        $present = $query->where('status', 'hadir')->count();
        $late = $query->where('status', 'terlambat')->count();
        $permit = $query->where('status', 'izin')->count();
        
        // Calculate absent as the remainder
        $totalPresent = $present + $late + $permit;
        $absent = $totalEmployees - $totalPresent;
        if ($absent < 0) $absent = 0;
        
        // Convert to percentage for chart display (0-100 range)
        $total = $totalEmployees > 0 ? $totalEmployees : 1; // Avoid division by zero
        
        return [
            'present' => round(($present / $total) * 100),
            'late' => round(($late / $total) * 100),
            'absent' => round(($absent / $total) * 100),
            'permit' => round(($permit / $total) * 100)
        ];
    }
}