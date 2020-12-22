<?php

namespace App\Http\Controllers;

use App\Excel;
use Illuminate\Http\Request;

class ExcelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $excel = Excel::paginate();
        return view('excel.index', compact('excel'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $excel = new \App\Excel();
        $people = \App\People::get()->pluck('name', 'id');
        return view('excel.create', compact('excel', 'people'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $excel = new \App\Excel();
        $people = $request['people'];
        $excel->save();
        $excel->people()->sync($people);
        flash(__('task has been added'))->success();
        return redirect()->route('tasks.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Excel  $excel
     * @return \Illuminate\Http\Response
     */
    public function show(Excel $excel)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Excel  $excel
     * @return \Illuminate\Http\Response
     */
    public function edit(Excel $excel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Excel  $excel
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Excel $excel)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Excel  $excel
     * @return \Illuminate\Http\Response
     */
    public function destroy(Excel $excel)
    {
        //
    }
}
