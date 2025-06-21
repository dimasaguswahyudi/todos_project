<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\TodoRequest;


use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TodoController extends Controller
{
    use AuthorizesRequests;

    protected $todo;
    public function __construct(Todo $todo)
    {
        $this->todo = $todo;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Todo::class);
        $todos = $this->todo->where('user_id', auth()->id())->orderByDesc('created_at')->get();
        return response()->json([
            'status' => 200,
            'message' => 'Data successfully',
            'data' => $todos,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TodoRequest $request): JsonResponse
    {
        $todo = $this->todo->create([
            'user_id' => auth()->id(),
            'title' => $request->title,
        ]);

        return response()->json([
            'status' => 201,
            'message' => 'Todo created successfully',
            'data' => $todo,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $todo = $this->todo->find($id);
        if (!$todo) {
            return response()->json([
                'status' => 404,
                'message' => 'Todo not found',
            ], 404);
        }
        $this->authorize('view', $todo);
        return response()->json([
            'status' => 200,
            'message' => 'Data successfully',
            'data' => $todo,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TodoRequest $request, string $id): JsonResponse
    {
        $todo = $this->todo->find($id);
        if (!$todo) {
            return response()->json([
                'status' => 404,
                'message' => 'Todo not found',
            ], 404);
        }

        $this->authorize('update', $todo);

        $todo->update([
            'title' => $request->title ?? $todo->title,
            'completed' => $request->completed,
        ]);

        return response()->json([
            'status' => 200,
            'message' => 'Todo updated successfully',
            'data' => $todo,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $todo = $this->todo->find($id);
        if (!$todo) {
            return response()->json([
                'status' => 404,
                'message' => 'Todo not found',
            ], 404);
        }
        
        $this->authorize('delete', $todo);

        $todo->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Todo deleted successfully',
        ]);
    }
}