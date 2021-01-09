<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Validator;
use App\Models\Task\Task;
use App\Http\Resources\TasksResource;

/**
 * Class TasksController.
 * Used to manage all the tasks created by user
 */
class TasksController extends APIController
{
    /**
     * Function to create task
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        try {
            $version = $request->header('Version-Code'); // to check app version
            $actual_version = config('constants.version_code');
            if($version >= $actual_version) {
                $status = array(Task::TYPE_PENDING, Task::TYPE_COMPLETED);
                $validation = Validator::make($request->all(), 
                                [
                                    'title'     => 'required|unique:tasks|max:100',
                                    'due_date' => 'required|date',
                                    'status' => 'sometimes|in:' . implode(',', $status),
                                ],
                                [
                                    'title.required' => 'Please provide task title.',
                                    'title.unique' => 'Title already exists. Please send different title.',
                                    'title.max' => 'Title should not be more than 100 characters',
                                    'due_date.required' => 'Please provide task due date.',
                                    'due_date.date' => 'Please send valid due date.',
                                    'status.in' => 'Please provide status either Pending or Completed.'
                                ]
                            );
                if ($validation->fails()) {
                    return $this->respond([
                        'status'   => false,
                        'code' => 200,
                        'message'   => trans($validation->messages()->first()),
                        'data' => (object)[]
                    ]);
                } else {
                    if(Task::create($request->all())) {
                        return $this->respond([
                            'status'   => true,
                            'code' => 200,
                            'message'   => "Task has been created successfully.",
                            'data' => (object)[]
                        ]);
                    }
                }
            } else {
                return $this->respond([
                    'status'   => false,
                    'code' => 503,
                    'message'   => "Please update app version",
                    'data' => (object)[]
                ]);
            }
        } catch(\Exception $e) {
            return $this->respond([
                'status'   => false,
                'code' => 500,
                'message'   => "Server Error",
                'data' => (object)[]
            ]);
        }
    }

    /**
     * Function to list all tasks
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        try {
            $version = $request->header('Version-Code'); // to check app version
            $actual_version = config('constants.version_code');
            if($version >= $actual_version) {
                $params = $request->all();
                $tasks = Task::with('subTasks')->pending();
                /**Block start to filter tasks */
                if(isset($params['title']) && $params['title'] != NULL) {
                    $tasks = $tasks->byTitle($params['title']);
                }

                if(isset($params['due_date']) && $params['due_date'] != NULL) {
                    $tasks = $tasks->byDueDate($params['due_date']);
                }

                if(isset($params['due_date_type']) && $params['due_date_type'] != NULL && in_array($params['due_date_type'] , ['today', 'this_week', 'next_week', 'overdue'])) {
                    $tasks = $tasks->byDueDateType($params['due_date_type']);
                }
                /**Block ends to filter tasks */
                $tasks = $tasks->parentTask()->ordered()->get();
                $allTasks = TasksResource::collection($tasks);
                return $this->respond([
                    'status'   => true,
                    'code' => 200,
                    'message'   => "All tasks found.",
                    'data' => $allTasks
                ]);
            } else {
                return $this->respond([
                    'status'   => false,
                    'code' => 503,
                    'message'   => "Please update app version",
                    'data' => []
                ]);
            }
        } catch(\Exception $e) {
            return $this->respond([
                'status'   => false,
                'code' => 500,
                'message'   => "Server Error",
                'data' => []
            ]);
        }
    }

    /**
     * Function to update task status
     * * @param Request $request
     */
    public function markComplete(Request $request) {
        try {
            $version = $request->header('Version-Code'); // to check app version
            $actual_version = config('constants.version_code');
            if($version >= $actual_version) {
                $params = $request->all();
                if(isset($params['task_id']) && $params['task_id'] != NULL) {
                    $task = Task::find($params['task_id']);
                    if($task != NULL) {
                        $task->status = Task::TYPE_COMPLETED;
                        $task->save();
                        return $this->respond([
                            'status'   => true,
                            'code' => 200,
                            'message'   => "Task Updated.",
                            'data' => []
                        ]);
                    } else {
                        return $this->respond([
                            'status'   => false,
                            'code' => 200,
                            'message'   => "Task not found.",
                            'data' => []
                        ]);
                    }
                } else {
                    return $this->respond([
                        'status'   => false,
                        'code' => 200,
                        'message'   => "Please provide task id.",
                        'data' => []
                    ]);
                }
            } else {
                return $this->respond([
                    'status'   => false,
                    'code' => 503,
                    'message'   => "Please update app version",
                    'data' => []
                ]);
            }
        } catch(\Exception $e) {
            return $this->respond([
                'status'   => false,
                'code' => 500,
                'message'   => "Server Error",
                'data' => []
            ]);
        }
    }

    /**
     * @param Task $task
     * @param Request $request
     *
     * @return mixed
     */
    public function destroy(Task $task, Request $request)
    {
        if($task->delete()) {
            return $this->respond([
                'status'   => true,
                'code' => 200,
                'message' => 'Task has been deleted successfully.',
            ]);
        } else {
            return $this->respond([
                'status'   => false,
                'code' => 200,
                'message'   => "There is some issue in deleting this task."
            ]);
        }
    }
}