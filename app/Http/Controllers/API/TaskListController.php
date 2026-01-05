<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\TaskList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskListController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get user's own task lists and public lists from other users
        $taskLists = TaskList::where('user_id', $user->id)
            ->orWhere('is_public', true)
            ->with('user:id,name,username')
            ->withCount('tasks')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($taskLists);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_public' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $taskList = TaskList::create([
            'title' => $request->title,
            'description' => $request->description,
            'is_public' => $request->is_public ?? false,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'taskList' => $taskList,
            'message' => 'Task list created successfully'
        ], 201);
    }

    public function show(TaskList $taskList, Request $request)
    {
        // Check if user can view this task list
        if ($taskList->user_id !== $request->user()->id && !$taskList->is_public) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $taskList->load('user:id,name,username', 'tasks');

        return response()->json($taskList);
    }

    public function update(Request $request, TaskList $taskList)
    {
        // Check authorization
        if ($taskList->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'is_public' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $taskList->update($request->only(['title', 'description', 'is_public']));

        return response()->json([
            'taskList' => $taskList,
            'message' => 'Task list updated successfully'
        ]);
    }

    public function destroy(TaskList $taskList, Request $request)
    {
        // Check authorization
        if ($taskList->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $taskList->delete();

        return response()->json(['message' => 'Task list deleted successfully']);
    }
}