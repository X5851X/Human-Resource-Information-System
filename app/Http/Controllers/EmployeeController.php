<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class EmployeeController extends Controller
{
    /**
     * Display a listing of employees.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = Employee::query();

            // Handle search parameters
            if ($request->has('search')) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('nama', 'like', "%{$searchTerm}%")
                      ->orWhere('nomor_induk_pegawai', 'like', "%{$searchTerm}%")
                      ->orWhere('jabatan', 'like', "%{$searchTerm}%")
                      ->orWhere('bidang', 'like', "%{$searchTerm}%");
                });
            }

            // Handle filters
            if ($request->has('bidang') && $request->bidang) {
                $query->where('bidang', $request->bidang);
            }

            if ($request->has('jabatan') && $request->jabatan) {
                $query->where('jabatan', $request->jabatan);
            }

            // Handle sorting
            $sortField = $request->sort_by ?? 'nama';
            $sortDirection = $request->sort_direction ?? 'asc';
            $allowedSortFields = ['nama', 'nomor_induk_pegawai', 'jabatan', 'bidang', 'created_at'];
            
            if (in_array($sortField, $allowedSortFields)) {
                $query->orderBy($sortField, $sortDirection);
            } else {
                $query->orderBy('nama', 'asc');
            }

            // Get pagination parameters
            $perPage = $request->per_page ?? 15;
            
            $employees = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $employees,
                'message' => 'Data pegawai berhasil dimuat'
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching employees: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data pegawai: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created employee.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nama' => 'required|string|max:255',
                'jabatan' => 'required|string|max:255',
                'nomor_induk_pegawai' => 'required|string|max:50|unique:employees',
                'keterangan' => 'nullable|string',
                'bidang' => 'required|string|max:255',
            ], [
                'nama.required' => 'Nama harus diisi',
                'jabatan.required' => 'Jabatan harus diisi',
                'nomor_induk_pegawai.required' => 'NIP harus diisi',
                'nomor_induk_pegawai.unique' => 'NIP sudah terdaftar',
                'bidang.required' => 'Bidang harus diisi',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $employee = Employee::create([
                'nama' => $request->nama,
                'jabatan' => $request->jabatan,
                'nomor_induk_pegawai' => $request->nomor_induk_pegawai,
                'keterangan' => $request->keterangan,
                'bidang' => $request->bidang,
            ]);

            return response()->json([
                'success' => true,
                'data' => $employee,
                'message' => 'Data pegawai berhasil disimpan'
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            Log::error('Error creating employee: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data pegawai: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified employee.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $employee = Employee::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $employee,
                'message' => 'Data pegawai berhasil dimuat'
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching employee details: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data pegawai: ' . $e->getMessage()
            ], Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * Update the specified employee.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $employee = Employee::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'nama' => 'required|string|max:255',
                'jabatan' => 'required|string|max:255',
                'nomor_induk_pegawai' => 'required|string|max:50|unique:employees,nomor_induk_pegawai,' . $id,
                'keterangan' => 'nullable|string',
                'bidang' => 'required|string|max:255',
            ], [
                'nama.required' => 'Nama harus diisi',
                'jabatan.required' => 'Jabatan harus diisi',
                'nomor_induk_pegawai.required' => 'NIP harus diisi',
                'nomor_induk_pegawai.unique' => 'NIP sudah terdaftar',
                'bidang.required' => 'Bidang harus diisi',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal',
                    'errors' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $employee->update([
                'nama' => $request->nama,
                'jabatan' => $request->jabatan,
                'nomor_induk_pegawai' => $request->nomor_induk_pegawai,
                'keterangan' => $request->keterangan,
                'bidang' => $request->bidang,
            ]);

            return response()->json([
                'success' => true,
                'data' => $employee,
                'message' => 'Data pegawai berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating employee: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data pegawai: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified employee.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $employee = Employee::findOrFail($id);
            $employee->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Data pegawai berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting employee: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data pegawai: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get a list of all departments/bidang
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBidangList()
    {
        try {
            $bidangList = Employee::distinct()->orderBy('bidang')->pluck('bidang');
            
            return response()->json([
                'success' => true,
                'data' => $bidangList,
                'message' => 'Daftar bidang berhasil dimuat'
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching bidang list: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat daftar bidang: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get a list of all positions/jabatan
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getJabatanList()
    {
        try {
            $jabatanList = Employee::distinct()->orderBy('jabatan')->pluck('jabatan');
            
            return response()->json([
                'success' => true,
                'data' => $jabatanList,
                'message' => 'Daftar jabatan berhasil dimuat'
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching jabatan list: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat daftar jabatan: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Restore a soft-deleted employee
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function restore($id)
    {
        try {
            $employee = Employee::withTrashed()->findOrFail($id);
            $employee->restore();
            
            return response()->json([
                'success' => true,
                'data' => $employee,
                'message' => 'Data pegawai berhasil dipulihkan'
            ]);
        } catch (\Exception $e) {
            Log::error('Error restoring employee: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memulihkan data pegawai: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get all soft-deleted employees
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function trash(Request $request)
    {
        try {
            $perPage = $request->per_page ?? 15;
            $deletedEmployees = Employee::onlyTrashed()->paginate($perPage);
            
            return response()->json([
                'success' => true,
                'data' => $deletedEmployees,
                'message' => 'Data pegawai yang dihapus berhasil dimuat'
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching deleted employees: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat data pegawai yang dihapus: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Force delete an employee
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function forceDelete($id)
    {
        try {
            $employee = Employee::withTrashed()->findOrFail($id);
            $employee->forceDelete();
            
            return response()->json([
                'success' => true,
                'message' => 'Data pegawai berhasil dihapus secara permanen'
            ]);
        } catch (\Exception $e) {
            Log::error('Error permanently deleting employee: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data pegawai secara permanen: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get an employee by NIP
     *
     * @param string $nip
     * @return \Illuminate\Http\JsonResponse
     */
    public function findByNip($nip)
    {
        try {
            $employee = Employee::where('nomor_induk_pegawai', $nip)->first();
            
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pegawai dengan NIP tersebut tidak ditemukan'
                ], Response::HTTP_NOT_FOUND);
            }
            
            return response()->json([
                'success' => true,
                'data' => $employee,
                'message' => 'Data pegawai berhasil ditemukan'
            ]);
        } catch (\Exception $e) {
            Log::error('Error finding employee by NIP: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencari data pegawai: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}