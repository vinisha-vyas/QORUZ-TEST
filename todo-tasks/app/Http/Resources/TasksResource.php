<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class TasksResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     *
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'due_date' => $this->due_date,
            'subTasks' => Self::collection($this->subTasks)
        ];
    }
}
