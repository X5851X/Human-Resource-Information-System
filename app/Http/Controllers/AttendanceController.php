<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    /**
     * Display a listing of attendances.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $attendances = Attendance::orderBy('tanggal_kegiatan', 'desc')->get();
        
        return response()->json($attendances);
    }

    /**
     * Store a newly created attendance in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tanggal_kegiatan' => 'required|date',
            'nama_kegiatan' => 'required|string|max:255',
            'nama' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Check if participant exists, if not create a new one
        $participant = Attendance::firstOrCreate(
            ['nama' => $request->nama]
        );

        $attendance = Attendance::create([
            'tanggal_kegiatan' => $request->tanggal_kegiatan,
            'nama_kegiatan' => $request->nama_kegiatan,
            'nama' => $request->nama,
            'keterangan' => $request->keterangan,
        ]);

        return response()->json($attendance, 201);
    }

    /**
     * Display the specified attendance.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $attendance = Attendance::findOrFail($id);
        
        return response()->json($attendance);
    }

    /**
     * Update the specified attendance in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'tanggal_kegiatan' => 'required|date',
            'nama_kegiatan' => 'required|string|max:255',
            'nama' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $attendance = Attendance::findOrFail($id);
        $attendance->update([
            'tanggal_kegiatan' => $request->tanggal_kegiatan,
            'nama_kegiatan' => $request->nama_kegiatan,
            'nama' => $request->nama,
            'keterangan' => $request->keterangan,
        ]);

        return response()->json($attendance);
    }

    /**
     * Remove the specified attendance from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->delete();

        return response()->json(null, 204);
    }

    /**
     * Get list of participants.
     *
     * @return \Illuminate\Http\Response
     */
    public function getParticipants()
    {
        $participants = Attendance::select('nama')->distinct()->orderBy('nama')->get();
        
        return response()->json($participants);
    }

    /**
     * Get unique kegiatan names from attendance records.
     *
     * @return \Illuminate\Http\Response
     */
    public function getKegiatan()
    {
        $kegiatan = Attendance::select('nama_kegiatan')
            ->distinct()
            ->orderBy('nama_kegiatan')
            ->get()
            ->pluck('nama_kegiatan');
        
        return response()->json($kegiatan);
    }
}