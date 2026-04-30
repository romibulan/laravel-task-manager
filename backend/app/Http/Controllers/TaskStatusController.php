<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskStatusController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Task $task, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::enum(TaskStatus::class)],
        ]);
        $task->update($validated);
        $task->refresh();
        $task->append('transitions');
        $task->load('owner:id,name,email');
        return response()->json(['success' => true, 'data' => new TaskResource($task), 'message' => 'Task updated successfully'], 200);
    }
}
