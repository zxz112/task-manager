<?php

namespace App\Http\Controllers;

use App\Task;
use Illuminate\Http\Request;
use Auth;
use Spatie\QueryBuilder;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tasks = Task::paginate();
        // $task = QueryBuilder::for(Task::class)
        //     ->allowedFields(['status_id', 'creator', 'assigned_id'])
        //     ->get();
        return view('task.index', compact('tasks'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (\Auth::check()) {
            $task = new Task();
            $statusesAll = \App\TaskStatus::get();
            $statuses = $statusesAll->pluck('name', 'id');
            $labels = \App\Label::get();
            $labels = $labels->pluck('name', 'id');
            $usersAll = \App\User::get();
            $users = $usersAll->pluck('name', 'id');
            $users['empty'] = '';
            return view('task.create', compact('task', 'statuses', 'users', 'labels'));
        }

        return redirect()->route('tasks.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (\Auth::check()) {
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
            if ($labels) {
                foreach ($labels as $label) {
                    $taskLabel = new \App\LabelTask();
                    $taskLabel->task_id = $task->id;
                    $taskLabel->label_id = $label;
                    $taskLabel->save();
                }
            }
            flash('task has been added')->success();
                return redirect()
                    ->route('tasks.index');
        }
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
        if (Auth::check()) {
            $statusesAll = \App\TaskStatus::get();
            $statuses = $statusesAll->pluck('name', 'id');
            $usersAll = \App\User::get();
            $users = $usersAll->pluck('name', 'id');
            $users['empty'] = '';
            $labels = \App\Label::get();
            $labels = $labels->pluck('name', 'id');
            return view('task.edit', compact('task', 'users', 'statuses', 'labels'));
        }
        flash('failed delete')->error();
        return redirect()->route('tasks.index');
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
        if (Auth::check()) {
            $data = $this->validate($request, [
                'name' => 'required',
                'description' => '',
                'task_status_id' => '',
                'assigned_to_id' => '',
            ]);
            $creator = \Auth::user();
            $labels = $request['labels'];
            $data['created_by_id'] = $creator->id;
            $task->fill($data);
            $task->save();
            $labelsAll = $task->labels;
            if ($labelsAll) {
                foreach ($labelsAll as $label) {
                        \App\LabelTask::where('label_id', $label->id)
                        ->where('task_id', $task->id)
                        ->delete();
                }
            }
            if ($labels) {
                foreach ($labels as $label) {
                    $taskLabel = new \App\LabelTask();
                    $taskLabel->task_id = $task->id;
                    $taskLabel->label_id = $label;
                    $taskLabel->save();
                }
            }
            flash('success edit')->success();
            return redirect()->route('tasks.index');
        }
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
        if (Auth::check() && Auth::user()->id === $task->creator->id) {
            if ($task) {
                $task->delete();
            }
            flash('success delete')->success();
            return redirect()
                ->route('tasks.index');
        }
        flash('failed delete')->error();
        return redirect()->route('tasks.index');
    }
}