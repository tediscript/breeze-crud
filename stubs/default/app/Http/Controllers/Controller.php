<?php

namespace App\Http\Controllers;

use App\Models\__NAME__;
use Illuminate\Http\Request;

class __NAME__Controller extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $__CPNAME__ = __NAME__::paginate(10);
        return view('__LPNAME__.index', compact('__CPNAME__'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('__LPNAME__.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $__CNAME__ = __NAME__::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
        ]);

        return redirect()->route('__LPNAME__.show', $__CNAME__->id)
            ->with('flash.variant', 'success')
            ->with('flash.message', __('__NAME__ created!'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(__NAME__ $__CNAME__)
    {
        return view('__LPNAME__.show', compact('__CNAME__'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(__NAME__ $__CNAME__)
    {
        return view('__LPNAME__.edit', compact('__CNAME__'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, __NAME__ $__CNAME__)
    {
        $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $params = $request->all();
        $__CNAME__->update($params);

        return redirect()->route('__LPNAME__.edit', $__CNAME__->id)
            ->with('flash.variant', 'success')
            ->with('flash.message', __('__NAME__ updated!'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(__NAME__ $__CNAME__)
    {
        $__CNAME__->delete();
        return redirect()->route('__LPNAME__.index')
            ->with('flash.variant', 'success')
            ->with('flash.message', __('__NAME__ deleted!'));
    }
}