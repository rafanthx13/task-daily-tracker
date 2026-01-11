<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Reminder;
use Carbon\Carbon;

class ReminderController extends Controller
{
    public function index()
    {
        return view('reminders.index');
    }

    public function sporadicIndex()
    {
        $reminders = Reminder::where('type', 'sporadic')->whereNull('last_completed_at')->get();
        return view('reminders.sporadic', compact('reminders'));
    }

    public function recurringIndex()
    {
        $reminders = Reminder::where('type', 'recurring')->get();
        return view('reminders.recurring', compact('reminders'));
    }

    public function finishedIndex()
    {
        $reminders = Reminder::where('type', 'sporadic')
            ->whereNotNull('last_completed_at')
            ->orderBy('last_completed_at', 'desc')
            ->get();

        return view('reminders.finished', compact('reminders'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'type' => 'required|in:sporadic,recurring',
        ]);

        Reminder::create($data);

        return back()->with('success', 'Lembrete criado com sucesso!');
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'title' => 'required|string',
        ]);

        $reminder = Reminder::findOrFail($id);
        $reminder->update($data);

        return back()->with('success', 'Lembrete atualizado!');
    }

    public function destroy($id)
    {
        $reminder = Reminder::findOrFail($id);
        $reminder->delete();

        return back()->with('success', 'Lembrete removido!');
    }

    public function complete($id)
    {
        $reminder = Reminder::where('id', $id)->where('type', 'recurring')->firstOrFail();
        $reminder->update(['last_completed_at' => now()]);

        return response()->json(['success' => true]);
    }

    public function finishSporadic($id)
    {
        $reminder = Reminder::where('id', $id)->where('type', 'sporadic')->firstOrFail();
        $reminder->update(['last_completed_at' => now()]);

        return response()->json(['success' => true]);
    }
}
