<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;
use App\Models\Task;
use App\Http\Resources\UserResource;
use App\Http\Resources\TaskCollection;
use Illuminate\Validation\Rule;
use App\Enums\TaskStatus;
use App\Http\Resources\TaskResource;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //return;
        $tasks = Task::with('owner:id,name,email')
            ->when($request->filled('status'), function ($q) use ($request) {
                return $q->whereIn('status', $request->status);
            })
            ->when($request->filled('due_date'), function ($q) use ($request) {
                if ($request->due_date === 'past_due') {
                    return  $q->whereDate('due_date', '<', now());
                } else if ($request->due_date === 'due_today') {
                    return  $q->whereDate('due_date', '=',  now());
                } else {
                    return $q;
                }
            })
            ->when($request->filled('q'), function ($q) use ($request) {
                return $q->where('title', 'like', "%" . $request->q . "%");
            })
            // ->orderByRaw("CASE WHEN status = ? THEN 0 WHEN status = ? THEN 1 WHEN status = ? THEN 2 END", [TaskStatus::Pending->value, TaskStatus::InProgress->value, TaskStatus::Completed->value])
            ->orderBy('due_date', 'asc')
            ->paginate(10)
            ->getCollection()
            ->transform(function ($task) {
                $task->append('transitions');
                return $task;
            });

        $stats = Task::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return new TaskCollection($tasks, 200, ['stats' => $stats]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['required', 'date', 'after:today'],
        ]);

        $task = Task::create([...$validated, 'created_by' => auth()->id()]);
        $task->refresh()
            ->load('owner:id,name,email')
            ->append('transitions');
        return response()->json(['success' => true, 'data' => new TaskResource($task), 'message' => 'Task created successfully'], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task): JsonResponse
    {

        $task->append('transitions');
        $task->load('owner:id,name,email');
        return response()->json(['success' => true, 'data' => new TaskResource($task)], 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['required', 'date', 'after:today'],
            'status' => ['nullable', Rule::enum(TaskStatus::class)],
        ]);
        $task->update($validated);
        $task->refresh();
        $task->append('transitions');
        $task->load('owner:id,name,email');
        return response()->json(['success' => true, 'data' => new TaskResource($task), 'message' => 'Task updated successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task): JsonResponse
    {
        $task->delete();
        return response()->json(['success' => true, 'id' => $task->id, 'message' => 'Task deleted successfully'], 200);
    }
}
