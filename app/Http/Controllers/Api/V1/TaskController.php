<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TaskController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $per_page = $request->query('per_page', 10);
        $search = $request->query('search');
        $status = $request->query('status');
        $order = $request->query('order', 'id');
        $dir = $request->query('dir', 'desc');

        $tasks = Task::query()
            ->when($search, fn (Builder $query, $search) => $query->search($search))
            ->when($status, fn (Builder $query) => $query->filter($status))
            ->where('user_id', $request->user()->id)
            ->orderBy($order, $dir)
            ->paginate($per_page);

        return $this->successResponse([
            'tasks' => TaskResource::collection($tasks),
            'meta' => [
                'current_page' => $tasks->currentPage(),
                'total' => $tasks->total(),
                'per_page' => $tasks->perPage(),
                'last_page' => $tasks->lastPage(),
                'from' => $tasks->firstItem(),
                'to' => $tasks->lastItem(),
                'next_page_url' => $tasks->nextPageUrl(),
                'prev_page_url' => $tasks->previousPageUrl(),
                'links' => $tasks->toArray()['links'],
                'has_pages' => $tasks->hasPages(), //این متد بررسی می‌کند که آیا پاجینیشن شامل چندین صفحه است یا خیر
                'has_more_pages' => $tasks->hasMorePages(), //آیا صفحات بیشتری بعد از صفحه جاری وجود دارد یا خیر
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TaskRequest $request)
    {
        try {
            $task = $request->user()->tasks()->create($request->validated());

            return $this->successResponse(new TaskResource($task), 'وظیفه مورد نظر با موفقیت ایجاد شد', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('خطا در ایجاد وظیفه: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        Gate::authorize('view', $task);

        return $this->successResponse(new TaskResource($task));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TaskRequest $request, Task $task)
    {
        Gate::authorize('update', $task);

        try {
            $task->update($request->validated());

            return $this->successResponse(new TaskResource($task), 'وظیفه مورد نظر با موفقیت ویرایش شد', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('خطا در ویرایش وظیفه: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task)
    {
        Gate::authorize('delete', $task);

        try {
            $task->delete();

            return $this->successResponse(null, 'وظیفه مورد نظر با موفقیت حذف شد', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('خطا در حذف وظیفه: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function toggleStatus(Task $task)
    {
        Gate::authorize('update', $task);

        try {
            $task->completed = !$task->completed;
            $task->save();

            return $this->successResponse(!!$task->completed, 'وظیفه مورد نظر با موفقیت ویرایش شد', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('خطا در ویرایش وظیفه: ' . $e->getMessage());
        }
    }
}
