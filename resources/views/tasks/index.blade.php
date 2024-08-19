                                                                       <!-- resources/views/tasks/index.blade.php -->

                                                                       @extends('layouts.app')

                                                                       @section('content')
                                                                       <div class="container">
                                                                           <h1>Tasks</h1>
                                                                           <a href="{{ route('tasks.create') }}" class="btn btn-primary">Create New Task</a>
                                                                           <table class="table mt-3">
                                                                               <thead>
                                                                                   <tr>
                                                                                       <th>Title</th>
                                                                                       <th>Description</th>
                                                                                       <th>Due Date</th>
                                                                                       <th>Priority</th>
                                                                                       <th>Status</th>
                                                                                       <th>Actions</th>
                                                                                   </tr>
                                                                               </thead>
                                                                               <tbody>
                                                                                   @foreach ($tasks as $task)
                                                                                       <tr>
                                                                                           <td>{{ $task->title }}</td>
                                                                                           <td>{{ $task->description }}</td>
                                                                                           <td>{{ $task->due_date }}</td>
                                                                                           <td>{{ $task->priority }}</td>
                                                                                           <td>{{ $task->status }}</td>
                                                                                           <td>
                                                                                               <a href="{{ route('tasks.show', $task) }}" class="btn btn-info btn-sm">View</a>
                                                                                               <a href="{{ route('tasks.edit', $task) }}" class="btn btn-warning btn-sm">Edit</a>
                                                                                               <form action="{{ route('tasks.destroy', $task) }}" method="POST" style="display:inline;">
                                                                                                   @csrf
                                                                                                   @method('DELETE')
                                                                                                   <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                                                                               </form>
                                                                                           </td>
                                                                                       </tr>
                                                                                   @endforeach
                                                                               </tbody>
                                                                           </table>
                                                                       </div>
                                                                       @endsection
