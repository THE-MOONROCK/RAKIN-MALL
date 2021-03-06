<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Actor extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'act';
    protected $fillable = [];
    protected $hidden = [];
    protected $primaryKey = 'id';
    protected $appends = [];
    protected $with = [];
}
