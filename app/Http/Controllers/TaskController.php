<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tag;
use App\Models\Task;
use Carbon\Carbon;

class TaskController extends Controller
{
    public function index($date = null)
    {
        $date = $date ? Carbon::parse($date)->startOfDay() : now()->startOfDay();

        // previous working day (for display)
        $prev = $this->previousWorkingDay($date->copy());
        $next = $this->nextWorkingDay($date->copy());

        // $tasks = Task::all()->groupBy('status')->toArray();

        $tasks = Task::with('tags')
                        ->whereDate('date', $date->toDateString())
                        ->orderBy('ordering')
                        ->get()
                        ->groupBy('status')->toArray();
        // dd($tasks);

        $listas = ['todo', 'done', 'extra', 'next'];
        // dd($tasks);
        $tags = Tag::all();

        return view('home', compact('tasks', 'listas', 'tags', 'date'));
    }

    /**
     * Salva Task
     *
     */
    public function store(Request $request)
    {
        try {
            // dd($request);
            $data = $request->validate([
                'title' => 'required|string',
                'notes' => 'nullable|string',
                'date'=>'required|date',
                'status' => 'required|in:todo,next,extra,done',
                'tag_ids'=>'nullable|array'
            ]);
            // dd($data);

            $task = Task::create([
                'title'=>$data['title'],
                'notes'=>$data['notes'] ?? null ,
                'date'=>$data['date'],
                'status'=>$data['status'] ?? 'todo',
            ]);
            if (!empty($data['tag_ids'])) $task->tags()->sync($data['tag_ids']);
            return redirect()->route('home');
            //code...
        } catch (\Throwable $th) {
            //throw $th;
            dump($request);
            dd($th->getMessage());
        }
    }

     // IMPORT logic: copia as tasks do último dia útil que tiver tasks (pulando sáb/dom)
    public function importFromLastWorkingDay(Request $request)
    {
        $targetDate = Carbon::parse($request->input('date'));
        // find last day < targetDate that has tasks (skip weekends)
        $search = $targetDate->copy()->subDay();
        while ($search->isWeekend()) $search->subDay();

        // keep looking back while no tasks
        while (Task::whereDate('date', $search->toDateString())->count() == 0) {
            $search->subDay();
            // skip weekends
            while ($search->isWeekend()) $search->subDay();
            // safety: avoid infinite loop (stop after 60 days)
            if ($search->diffInDays($targetDate) > 365) {
                return response()->json(['message'=>'Nenhum dia anterior com tasks encontrado'], 404);
            }
        }

        $fromTasks = Task::with('tags')->whereDate('date', $search->toDateString())->get();
        $copied = [];
        foreach ($fromTasks as $t) {
            // regra: não importar tasks marcadas 'done' por padrão (configurável)
            if ($t->status === 'done') continue;

            $newRepeat = null;
            if (!is_null($t->repeat_days_left) && $t->repeat_days_left > 0) {
                $newRepeat = $t->repeat_days_left - 1;
            }

            $new = Task::create([
                'title' => $t->title,
                'notes' => $t->notes,
                'status' => $t->status,
                'date' => $targetDate->toDateString(),
                'ordering' => $t->ordering,
                'repeat_days_left' => $newRepeat
            ]);
            if ($t->tags->isNotEmpty()) {
                $new->tags()->sync($t->tags->pluck('id')->toArray());
            }
            $copied[] = $new;
        }

        return response()->json([
            'copied_count' => count($copied),
            'from_date' => $search->toDateString(),
            'target_date' => $targetDate->toDateString(),
        ]);
    }

    private function previousWorkingDay(Carbon $d)
    {
        $d->subDay();
        while ($d->isWeekend()) $d->subDay();
        return $d;
    }

    private function nextWorkingDay(Carbon $d)
    {
        $d->addDay();
        while ($d->isWeekend()) $d->addDay();
        return $d;
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
}
