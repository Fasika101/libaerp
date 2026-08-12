<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HospitalDepartment extends Model
{
    use BelongsToTenant;

    use SoftDeletes;

    protected $table = 'hospital_departments';

    protected $fillable = [
        'tenant_id','name', 'code', 'description', 'location', 'phone', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function doctors()
    {
        return $this->hasMany(Doctor::class, 'department_id', 'id');
    }

    public function wards()
    {
        return $this->hasMany(HospitalWard::class, 'department_id', 'id');
    }
}
