<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tag;
use App\Models\Task;
use Carbon\Carbon;
use App\Constants\Lanes;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


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

        $dateStr = Carbon::parse($date)->format('Y-m-d');

        return view('home', compact('tasks', 'listas', 'tags', 'date', 'prev', 'next', 'dateStr'));
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

    /**
     * Pega atividades todo e WAITING do último dia e joga par ao dia escolhido (em geral é hoje)
     *
     * @param string $dateOld
     * @param string $dateToday
     * @return \Illuminate\Http\JsonResponse
     */
    public function copyTasksFromDate(string $dateOld, string $dateToday)
    {
        try {

            // PEGA TODO E NEXT
            $oldNextTasks = Task::with('tags')
                ->whereDate('date', $dateOld)
                ->whereIn('status', [Lanes::TODO, Lanes::WAITING])
                ->get();

            if ($oldNextTasks->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não há tarefas em Waiting ou Next para adicionar.'
                ], 400);
            }

            DB::transaction(function () use ($oldNextTasks, $dateToday) {
                foreach ($oldNextTasks as $task) {

                    // Pega a atividade da Lane TODO
                    $status = $task->status === Lanes::TODO
                        ? Lanes::TODO
                        : Lanes::WAITING;

                    $newTask = Task::create([
                        'title' => $task->title,
                        'notes' => $task->notes,
                        'status' => $status,
                        'date' => $dateToday,
                        'id_original' => $task->id_original ?? $task->id,
                    ]);

                    // Copia as tags
                    $newTask->tags()->sync($task->tags->pluck('id'));
                }
            });

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('Erro ao copiar tarefas', [
                'exception' => $e
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao copiar tarefas do dia anterior.'
            ], 500);
        }
    }

    // public function copyTasksFromDate(Request $request, $dateOld, $dateToday)
    // {
    //     try {
    //         // throw new \Exception("Erro ao copiar tarefas do dia anterior.");
    //         $oldNextTasks = Task::whereDate('date', '=', $dateOld)
    //             ->whereIn('status', [Lanes::NEXT, Lanes::WAITING])
    //             ->get();

    //         foreach ($oldNextTasks as $task) {
    //             $tomorrowStatus = $task->status == Lanes::NEXT ? Lanes::TODO : Lanes::WAITING;
    //             $newTask = Task::create([
    //                 'title' => $task->title,
    //                 'notes' => $task->notes,
    //                 'status' => $tomorrowStatus,
    //                 'date' => $dateToday,
    //             ]);
    //             $newTask->save();
    //         }

    //         return response()->json(['success' => true]);
    //     } catch (\Exception $e) {
    //         Log::error("Erro ao copiar tarefas: " . $e->getMessage());
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Erro ao copiar tarefas do dia anterior.'
    //         ], 500);
    //     }
    // }

    private function getPreviousDate(Carbon $d)
    {
        // Busca a data máxima que seja menor que $d
        $previousDate = Task::where('date', '<', $d->copy()->startOfDay())
            ->orderBy('date', 'desc')
            ->value('date'); // pega apenas o campo

        return $previousDate ? Carbon::parse($previousDate)->format('Y-m-d') : '';
    }

    public function show($id)
    {
        $task = Task::with('tags')->findOrFail($id);
        $originalId = $task->id_original ?? $task->id;

        $family = Task::with('tags')
            ->where('id', $originalId)
            ->orWhere('id_original', $originalId)
            ->orderBy('date', 'asc')
            ->get();

        $firstTask = $family->first();
        $lastTask = $family->last();

        $startDate = \Carbon\Carbon::parse($firstTask->date);
        $endDate = null;
        $duration = 0;

        if ($lastTask->status === Lanes::DONE) {
            $endDate = \Carbon\Carbon::parse($lastTask->date);
        }

        $endForDuration = $endDate ?: now();
        $duration = floor($startDate->diffInDays($endForDuration)) + 1;

        return view('tasks.show', compact('task', 'family', 'startDate', 'endDate', 'duration'));
    }

    private function getNextDate(Carbon $d)
    {
        // Busca a data mínima que seja maior que $d
        $nextDate = Task::where('date', '>', $d->copy()->startOfDay())
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
        $data = $request->validate([
            'title' => 'required|string',
            'notes' => 'nullable|string',
            'tag_ids' => 'nullable|array'
        ]);

        $task = Task::findOrFail($id);
        $task->title = $data['title'];
        $task->notes = $data['notes'];
        $task->save();

        if (isset($data['tag_ids'])) {
            $task->tags()->sync($data['tag_ids']);
        } else {
            $task->tags()->detach();
        }

        return response()->json(['success' => true]);
    }

    public function delete(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return response()->json(['success' => true]);
    }

    public function previousDayTasks(Request $request)
    {

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
