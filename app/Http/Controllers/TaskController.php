<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tag;
use App\Models\Task;
use Carbon\Carbon;
use App\Constants\Lanes;

class TaskController extends Controller
{
    public function index($date = null)
    {
        $date = $date ? Carbon::parse($date)->startOfDay() : now()->startOfDay();

        $prev = $this->getPreviousDate($date->copy());
        $next = $this->getNextDate($date->copy());

        $tasks = Task::with('tags')
            ->whereDate('date', $date->toDateString())
            ->orderBy('ordering')
            ->get()
            ->groupBy('status')->toArray();

        $listas = Lanes::getAllAsArray();
        $tags = Tag::all();

        return view('home', compact('tasks', 'listas', 'tags', 'date', 'prev', 'next'));
    }

    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'title' => 'required|string',
                'notes' => 'nullable|string',
                'date' => 'required|date',
                'status' => 'required|in:' . Lanes::getAllAsString(),
                'tag_ids' => 'nullable|array'
            ]);

            $task = Task::create([
                'title' => $data['title'],
                'notes' => $data['notes'] ?? null,
                'date' => $data['date'],
                'status' => $data['status'] ?? Lanes::TODO,
            ]);
            if (!empty($data['tag_ids'])) $task->tags()->sync($data['tag_ids']);
            return redirect()->route('home');
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }
    }

    public function getOldDate(Request $request, $dateOld, $dateToday)
    {
        $oldNextTasks = Task::whereDate('date', '=', $dateOld)->whereIn('status', '=', [Lanes::NEXT, Lanes::WAITING])->get();
        foreach ($oldNextTasks as $task) {
            $tomorrowStatus = $task->status == Lanes::NEXT ? Lanes::TODO : Lanes::WAITING;
            $newTask = Task::create([
                'title' => $task->title,
                'notes' => $task->notes,
                'status' => $tomorrowStatus,
                'date' => $dateToday,
            ]);
            $newTask->save();
        }
        return response()->json(['success' => true]);
    }

    private function getPreviousDate(Carbon $d)
    {
        // Busca a data máxima que seja menor que $d
        $previousDate = Task::whereDate('date', '<', $d)
            ->orderBy('date', 'desc')
            ->value('date'); // pega apenas o campo

        return $previousDate ? Carbon::parse($previousDate)->format('Y-m-d') : '';
    }

    private function getNextDate(Carbon $d)
    {
        // Busca a data mínima que seja maior que $d
        $nextDate = Task::whereDate('date', '>', $d)
            ->orderBy('date', 'asc')
            ->value('date'); // Pega apenas o campo 'date' da primeira tarefa encontrada

        return $nextDate ? Carbon::parse($nextDate)->format('Y-m-d') : '';
    }

    public function updateLane(Request $request, Task $task)
    {
        $request->validate([
            'status' => 'required|string'
        ]);

        $task->status = $request->status;
        $task->save();

        return response()->json(['success' => true, 'task' => $task]);
    }

    public function update(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $task->title = $request->title;
        $task->notes = $request->notes;
        $task->save();

        return response()->json(['success' => true]);
    }

    public function delete(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return response()->json(['success' => true]);
    }

    public function previousDayTasks(Request $request){

        $request->validate([
            'oldDate' => 'required|string'
        ]);
        $oldDate = $request->oldDate;

        $tasks = Task::with('tags')
            ->whereDate('date', $oldDate)
            ->orderBy('ordering')
            ->get()
            ->groupBy('status')->toArray();

        $listas = Lanes::getAllAsArray();
        $tags = Tag::all();
        return response()->json([
            'listas' => $listas,
            'tasks' => $tasks,
            'tags' => $tags,
        ]);

    }
}
