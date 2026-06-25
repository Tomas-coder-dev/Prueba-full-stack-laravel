<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveTaskRequest;
use App\Models\Task;
use App\TaskOptions\TaskStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = auth()->user()->tasks();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        return response()->json($query->get());
    }

    public function store(SaveTaskRequest $request): JsonResponse
    {
        $task = auth()->user()->tasks()->create($request->validated());

        return response()->json($task, 201);
    }

    public function update(SaveTaskRequest $request, Task $task): JsonResponse
    {
        if ($task->user_id !== auth()->id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        if ($task->status === TaskStatus::DONE) {
            return response()->json(['error' => 'Una tarea en estado "done" no puede ser editada'], Code: 422);
        }

        $task->update($request->validated());

        return response()->json($task);
    }

    public function destroy(Task $task): JsonResponse
    {
        if ($task->user_id !== auth()->id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $task->delete();

        return response()->json(['message' => 'Tarea eliminada exitosamente']);
    }

    public function changeStatus(Request $request, Task $task): JsonResponse
    {
        if ($task->user_id !== auth()->id()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $request->validate([
            'status' => ['required', \Illuminate\Validation\Rule::enum(TaskStatus::class)]
        ]);

        $task->update(['status' => $request->status]);

        return response()->json($task);
    }

    public function dashboard(): JsonResponse
    {
        $user = auth()->user();

        $metrics = [
            'total_tasks' => $user->tasks()->count(),
            'pending_tasks' => $user->tasks()->where('status', TaskStatus::PENDING->value)->count(),
            'in_progress_tasks' => $user->tasks()->where('status', TaskStatus::IN_PROGRESS->value)->count(),
            'completed_tasks' => $user->tasks()->where('status', TaskStatus::DONE->value)->count(),
        ];

        return response()->json($metrics);
    }
}