<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Lang;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Upload extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $table = 'uploads';
    protected $fillable = [];
    protected $hidden = [
        'is_temp_delete','status','module_id'
    ];
    protected $primaryKey = 'id';
    protected $appends = ['translated_module', 'attachments_link', 'attachments', 'attachments_url'];
    protected $with = ['media'];

    // public function user()
    // {
    //     return $this->belongsTo('App\User');
    // }

    public function getTranslatedModuleAttribute() {
        $label = 'upload.' . $this->module;
        return Lang::has($label) ? trans($label) : ucfirst($this->module);
    }

    public function getUploadedAttachmentsAttribute()
    {
        return $this->getMedia('file')->keyBy('id');
    }

    /**
     * @return string
     */
    public function getAttachmentsLinkAttribute()
    {
        $attachments = $this->getMedia('file');
        if (! count($attachments)) {
            return null;
        }
        $html = [];
        foreach ($attachments as $file) {
            $html[] = '<a href="' . $file->getUrl() . '" target="_blank">' . $file->file_name . '</a>';
        }

        return implode('<br/>', $html);
    }

    public function getAttachmentsAttribute()
    {
        $attachments = $this->getMedia('file');
        if (! count($attachments)) {
            return null;
        }
        $files = [];
        foreach ($attachments as $file) {
            $files[] = $file;
        }
        return $files;
    }

    public function getAttachmentsUrlAttribute()
    {
        $attachments = $this->getMedia('file');
        if (! count($attachments)) {
            return null;
        }
        $files = [];
        foreach ($attachments as $file) {
            $files[] = $file->getUrl();
        }
        return $files;
    }

}
