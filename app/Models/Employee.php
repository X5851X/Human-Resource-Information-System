<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nama',
        'jabatan',
        'nomor_induk_pegawai',
        'keterangan',
        'bidang'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the attendance records for the employee.
     */
    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'nama', 'nama');
    }
    
    /**
     * Apply uppercase to NIP
     *
     * @param string $value
     * @return void
     */
    public function setNomorIndukPegawaiAttribute($value)
    {
        $this->attributes['nomor_induk_pegawai'] = strtoupper(trim($value));
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
}