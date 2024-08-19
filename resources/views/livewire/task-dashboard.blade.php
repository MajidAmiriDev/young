<div>
    <h2>Task Dashboard</h2>

    <form wire:submit.prevent="addTask">
        <input type="text" wire:model="newTaskTitle" placeholder="Title">
        <textarea wire:model="newTaskDescription" placeholder="Description"></textarea>
        <input type="date" wire:model="newTaskDueDate">
        <select wire:model="newTaskPriority">
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
        </select>
        <select wire:model="newTaskStatus">
            <option value="pending">Pending</option>
            <option value="completed">Completed</option>
        </select>
        <button type="submit">Add Task</button>
    </form>

    <ul>
        @foreach($tasks as $task)
            <li>
                <strong>{{ $task->title }}</strong> - {{ $task->status }}
                <button wire:click="updateTask({{ $task->id }}, 'completed')">Mark as Completed</button>
            </li>
        @endforeach
    </ul>
</div>
