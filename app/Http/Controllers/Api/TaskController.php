<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use App\Events\TaskUpdated;
use App\Events\HighPriorityTaskCreated;
use App\Events\TaskCompleted;
use App\Http\Requests\StoreTaskRequest;

/**
 * @OA\Info(title="Task API", version="1.0.0")
 *
 * @OA\Schema(
 *     schema="Task",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Task Title"),
 *     @OA\Property(property="description", type="string", example="Task Description"),
 *     @OA\Property(property="due_date", type="string", format="date", example="2024-08-19"),
 *     @OA\Property(property="priority", type="string", enum={"high", "medium", "low"}, example="high"),
 *     @OA\Property(property="status", type="string", example="pending"),
 *     @OA\Property(property="user_id", type="integer", example=1)
 * )
 */
class TaskController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/tasks",
     *     summary="Get list of tasks",
     *     description="Retrieve a list of tasks for the authenticated user.",
     *     tags={"Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of tasks",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Task")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function index()
    {
        $tasks = Task::where('user_id', Auth::id())->get();
        return response()->json($tasks);
    }

    /**
     * @OA\Post(
     *     path="/api/tasks",
     *     summary="Create a new task",
     *     description="Create a new task for the authenticated user.",
     *     tags={"Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"title", "description", "due_date", "priority", "status"},
     *                 @OA\Property(property="title", type="string", example="Task Title"),
     *                 @OA\Property(property="description", type="string", example="Task Description"),
     *                 @OA\Property(property="due_date", type="string", format="date", example="2024-08-19"),
     *                 @OA\Property(property="priority", type="string", enum={"high", "medium", "low"}, example="high"),
     *                 @OA\Property(property="status", type="string", example="pending")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Task created successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
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
        return response()->json($task, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/tasks/{id}",
     *     summary="Get a single task",
     *     description="Retrieve a single task by ID for the authenticated user.",
     *     tags={"Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task details",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function show(Task $task)
    {
        $this->authorize('view', $task);
        return response()->json($task);
    }

    /**
     * @OA\Put(
     *     path="/api/tasks/{id}",
     *     summary="Update an existing task",
     *     description="Update an existing task for the authenticated user.",
     *     tags={"Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"title", "description", "due_date", "priority", "status"},
     *                 @OA\Property(property="title", type="string", example="Updated Task Title"),
     *                 @OA\Property(property="description", type="string", example="Updated Task Description"),
     *                 @OA\Property(property="due_date", type="string", format="date", example="2024-08-20"),
     *                 @OA\Property(property="priority", type="string", enum={"high", "medium", "low"}, example="medium"),
     *                 @OA\Property(property="status", type="string", example="completed")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Task updated successfully",
     *         @OA\JsonContent(ref="#/components/schemas/Task")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     )
     * )
     */
    public function update(Request $request, Task $task)
    {

        if ($task->status == 1) { // FOR EXAMPLE 1 == COMPLETED
            event(new TaskCompleted($task));
        }
        $task->update($request->all());
        event(new TaskUpdated($task));
        return response()->json($task);
    }

    /**
     * @OA\Delete(
     *     path="/api/tasks/{id}",
     *     summary="Delete a task",
     *     description="Delete a task by ID for the authenticated user.",
     *     tags={"Tasks"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Task deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Task not found"
     *     )
     * )
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);

        $task->delete();
        return response()->json(null, 204);
    }
}
