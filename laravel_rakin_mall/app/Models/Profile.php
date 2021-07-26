<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profile extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [];
    protected $primaryKey = 'id';
    protected $table = 'prof';
    protected $appends = ['full_name', 'department_id'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function designation()
    {
        return $this->belongsTo('App\Designation');
    }

    public function position()
    {
        return $this->belongsTo('App\Position');
    }

    public function getFullNameAttribute()
    {
        return ($this->first_name ? $this->first_name : '').' '.($this->last_name ? $this->last_name : '');
    }

    public function getDepartmentIdAttribute()
    {
        return $this->dest ? $this->dest->dept_id : 0;
    }
}
