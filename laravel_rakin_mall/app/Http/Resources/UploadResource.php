<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UploadResource extends JsonResource
{
    var $showDetail = false;
    public function __construct($resource, $showDetail = false)
    {
        $this->showDetail = $showDetail;
        parent::__construct($resource);
    }
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'user_id' => $this->user_id,
            'module' => $this->module,
            'upload_token' => $this->upload_token,
            'user_filename' => $this->user_filename,
            'filename' => $this->filename,
            'created_at' => formatToDateTime($this->created_at),
            'updated_at' => formatToDateTime($this->updated_at),
            'translated_module' => $this->translated_module,
            'file_url' => $this->attachments_url,
            'attachments' => $this->attachments,
            'user' => $this->showDetail && $this->user ? new UserInfoResource($this->user) : null
        ];
    }
}
