<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::all()->groupBy('status')->toArray();
        $listas = ['TODO', 'DONE', 'TODO EXTRA', 'NEXT-DAY'];

        return view('welcome2', compact('tasks', 'listas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'status' => 'required|string|in:TODO,DONE,TODO EXTRA,NEXT-DAY',
        ]);

        Task::create([
            'title' => $request->title,
            'status' => $request->status,
        ]);

        return redirect()->route('tasks.index');
    }
}
