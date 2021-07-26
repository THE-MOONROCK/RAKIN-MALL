<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeviceToken extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'device_token';

	protected $hidden = [
		'device_token'
	];

	protected $fillable = [
		'user_id',
		'app_version_number',
        'device_name',
		'device_token',
		'device_platform'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'usr_id', 'id');
	}
}
