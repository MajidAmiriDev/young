<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;


class TaskDashboard extends Component
{
    public $tasks;
    public $newTaskTitle;
    public $newTaskDescription;
    public $newTaskDueDate;
    public $newTaskPriority = 'low';
    public $newTaskStatus = 'pending';

    protected $rules = [
        'newTaskTitle' => 'required|string|max:255',
        'newTaskDescription' => 'required|string',
        'newTaskDueDate' => 'required|date',
        'newTaskPriority' => 'required|in:high,medium,low',
        'newTaskStatus' => 'required|string',
    ];

    public function mount()
    {
        $this->loadTasks();
    }

    public function loadTasks()
    {
        $this->tasks = Task::where('user_id', Auth::id())->get();
    }

    public function addTask()
    {
        $this->validate();

        Task::create([
            'title' => $this->newTaskTitle,
            'description' => $this->newTaskDescription,
            'due_date' => $this->newTaskDueDate,
            'priority' => $this->newTaskPriority,
            'status' => $this->newTaskStatus,
            'user_id' => Auth::id(),
        ]);

        $this->reset(['newTaskTitle', 'newTaskDescription', 'newTaskDueDate', 'newTaskPriority', 'newTaskStatus']);
        $this->loadTasks();
    }

    public function updateTask($taskId, $status)
    {
        $task = Task::find($taskId);
        if ($task) {
            $task->status = $status;
            $task->save();
            $this->loadTasks();
        }
    }

    public function render()
    {
        return view('livewire.task-dashboard');
    }
}
