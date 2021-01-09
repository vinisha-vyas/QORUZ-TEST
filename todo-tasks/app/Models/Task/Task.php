<?php

/**
 * Task model to manage all tasks and sub tasks created by user
 */
namespace App\Models\Task;

use Illuminate\Database\Eloquent\Model;
use App\Models\Task\Traits\TaskRelationship;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Task extends Model
{

    const TYPE_PENDING = 'Pending';
    const TYPE_COMPLETED = 'Completed';

    use SoftDeletes,
        TaskRelationship {
            // AffiliateAttribute::getEditButtonAttribute insteadof ModelTrait;
        }

    /**
     * The database table used by the model.
     * @var string
     */
    protected $table = 'tasks';

    /**
     * Mass Assignable fields of model
     * @var array
     */
    protected $fillable = [
        'title', 'due_date', 'status', 'task_id'
    ];


    /**
     * Dates
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * Guarded fields of model
     * @var array
     */
    protected $guarded = [
        'id'
    ];

    /**
     * Constructor of Model
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function($task){
            $task->subTasks()->delete();
        });
    }

    /**
     * Scope to check status pending
     */
    public function scopePending($query)
    {
        return $query->whereStatus(Self::TYPE_PENDING);
    }

    /**
     * Scope to check status pending
     */
    public function scopeParentTask($query)
    {
        return $query->whereNull('task_id');
    }

    /**
     * Scope to get ordered due date tasks
     */
    public function scopeOrdered($query)
    {
        return $query->OrderBy('due_date', 'ASC');
    }

    /**
     * Scope to get task based on title
     */
    public function scopeByTitle($query, $title)
    {
        return $query->where('title', 'LIKE' ,'%'.$title.'%');
    }

    /**
     * Scope to get task based on due date
     */
    public function scopeByDueDate($query, $due_date)
    {
        return $query->whereDate('due_date',$due_date);
    }

    /**
     * Scope to get task based on due date type - today, this_week, next_week, overdue
     */
    public function scopeByDueDateType($query, $due_date_type)
    {
        if($due_date_type == 'today') {
            return $query->whereDate('due_date', date('Y-m-d'));
        } elseif($due_date_type == 'this_week') {
            return $query->whereBetween('due_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif($due_date_type == 'overdue') {
            return $query->whereDate('due_date','<',date('Y-m-d'));
        }
    }
}
