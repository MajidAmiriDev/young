<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Events\TaskUpdated;
use App\Events\HighPriorityTaskCreated;
use App\Events\TaskCompleted;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\StoreTaskRequest;

class TaskController extends Controller
{

    public function __construct()
    {

    }

    public function create()
    {
        return view('tasks.create');
    }

    public function store(StoreTaskRequest $request)
    {
        $task = Task::createTask($request->validated());
        if ($task->priority === '2') { // 2 == High
            // ارسال وظیفه با اولویت بالا به صف
            ProcessCriticalTask::dispatch($task)->onQueue('high-priority');
            event(new HighPriorityTaskCreated($task));
        }

        event(new TaskUpdated($task));
        Log::info('A new task has been created.', ['task_id' => $task->id]);
        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
    }

    public function index()
    {
        $tasks = Task::where('user_id', auth()->id())->get();
        return view('tasks.index', compact('tasks'));
    }

    public function show(Task $task)
    {
        $this->authorize('view', $task);
        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $this->authorize('update', $task);
        event(new TaskUpdated($task));
        return view('tasks.edit', compact('task'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'due_date' => 'required|date',
            'priority' => 'required|in:high,medium,low',
            'status' => 'required|string',
        ]);
        if ($task->status == 1) { // FOR EXAMPLE 1 == COMPLETED
            event(new TaskCompleted($task));
        }
        $task->update([
            'title' => $request->title,
            'description' => $request->description,
            'due_date' => $request->due_date,
            'priority' => $request->priority,
            'status' => $request->status,
        ]);
        event(new TaskUpdated($task));
        return redirect()->route('tasks.index')->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        $task->delete();
        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }
}
