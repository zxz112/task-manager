<?php

namespace App\Http\Controllers;

use App\Task;
use Illuminate\Http\Request;
use Auth;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $statuses = \App\TaskStatus::get()->pluck('name', 'id')->prepend('Status', '');
        $users = \App\User::get()->pluck('name', 'id')->prepend('User', '');
        $labels = \App\Label::get()->pluck('name', 'id')->prepend('Label', '');
        $tasks = QueryBuilder::for(Task::class)
            ->allowedFilters([
                AllowedFilter::exact('status.id'),
                AllowedFilter::exact('creator.id'),
                AllowedFilter::exact('assigner.id'),
                AllowedFilter::exact('labels.id')
            ])->paginate();
        return view('task.index', compact('tasks', 'statuses', 'users', 'labels'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create', Task::class);
        $task = new Task();
        $statuses = \App\TaskStatus::get()->pluck('name', 'id');
        $labels = \App\Label::get()->pluck('name', 'id');
        $users = \App\User::get()->pluck('name', 'id')->prepend('Assignee', '');
        return view('task.create', compact('task', 'statuses', 'users', 'labels'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('store', Task::class);
        $data = $this->validate($request, [
            'name' => 'required',
            'description' => '',
            'task_status_id' => '',
            'assigned_to_id' => '',
        ]);
        $creator = \Auth::user();
        $labels = $request['labels'];
        $data['created_by_id'] = $creator->id;
        $task = new \App\Task();
        $task->fill($data);
        $task->save();
        $task->labels()->sync($labels);
        flash(__('task has been added'))->success();
        return redirect()->route('tasks.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function show(Task $task)
    {
        return view('task.show', compact('task'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function edit(Task $task)
    {
        $this->authorize('edit', Task::class);
        $statuses = \App\TaskStatus::get()->pluck('name', 'id');
        $users = \App\User::get()->pluck('name', 'id')->prepend('Assignee', '');
        $labels = \App\Label::get()->pluck('name', 'id');
        return view('task.edit', compact('task', 'users', 'statuses', 'labels'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Task $task)
    {
        $this->authorize('update', Task::class);
        $data = $this->validate($request, [
            'name' => 'required',
            'description' => '',
            'task_status_id' => '',
            'assigned_to_id' => '',
        ]);
        $labels = $request['labels'];
        $task->fill($data);
        $task->save();
        $task->labels()->sync($labels);

        flash(__('success edit'))->success();
        return redirect()->route('tasks.index');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Task  $task
     * @return \Illuminate\Http\Response
     */
    public function destroy(Task $task)
    {
        $this->authorize('delete', $task);
        $task->delete();
        flash(__('success delete'))->success();
        return redirect()
            ->route('tasks.index');
    }
}
