<?php

namespace App\Http\Resources;

class UserCollection extends AbstractCollection
{
    protected function handleData($request)
    {
        return UserResource::collection($this->collection);
    }
}
