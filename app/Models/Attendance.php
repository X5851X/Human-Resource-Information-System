<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tanggal_kegiatan',
        'nama_kegiatan',
        'nama',
        'keterangan'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'tanggal_kegiatan' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Set the date attribute with proper format
     *
     * @param string $value
     * @return void
     */
    public function setTanggalKegiatanAttribute($value)
    {
        if ($value instanceof Carbon) {
            $this->attributes['tanggal_kegiatan'] = $value->format('Y-m-d');
        } else {
            $this->attributes['tanggal_kegiatan'] = Carbon::parse($value)->format('Y-m-d');
        }
    }
    
    /**
     * Trim whitespace from name
     *
     * @param string $value
     * @return void
     */
    public function setNamaAttribute($value)
    {
        $this->attributes['nama'] = trim($value);
    }
    
    /**
     * Trim whitespace from event name
     *
     * @param string $value
     * @return void
     */
    public function setNamaKegiatanAttribute($value)
    {
        $this->attributes['nama_kegiatan'] = trim($value);
    }
    
    /**
     * Get the employee associated with this attendance.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'nama', 'nama');
    }
}