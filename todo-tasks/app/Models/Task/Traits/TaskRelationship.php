<?php

/**
 * Class TaskRelationship
 * Trait to manage relationship related to task model
 */
namespace App\Models\Task\Traits;

use App\Models\Task\Task;

trait TaskRelationship
{
    /*
    * Relation to task
    */
    public function task() {
        return $this->belongsTo(Task::class);
    }

    /**
     * Relation to get all sub tasks
     */
    public function subTasks()
    {
       return $this->hasMany(Task::class,'task_id', 'id');
    }
}
