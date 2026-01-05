<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\TaskList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth:sanctum');
    }

    public function store(Request $request, TaskList $taskList)
    {
        // Check authorization
        if ($taskList->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'task_list_id' => $taskList->id,
        ]);

        return response()->json([
            'task' => $task,
            'message' => 'Task created successfully'
        ], 201);
    }

    public function update(Request $request, Task $task)
    {
        // Check authorization
        if ($task->taskList->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'is_completed' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $task->update($request->only(['title', 'description', 'is_completed']));

        return response()->json([
            'task' => $task,
            'message' => 'Task updated successfully'
        ]);
    }

    public function destroy(Task $task, Request $request)
    {
        // Check authorization
        if ($task->taskList->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }
}