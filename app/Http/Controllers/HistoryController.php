<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use App\Models\Exercise;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $status = (string) $request->string('status');
        $skill = (string) $request->string('skill');

        $attempts = Attempt::query()
            ->with('exercise')
            ->whereIn('status', Attempt::STATUSES)
            ->when(in_array($status, Attempt::STATUSES, true), fn ($query) => $query->where('status', $status))
            ->when(in_array($skill, Exercise::SKILLS, true), fn ($query) => $query->where('configuration_snapshot->skill', $skill))
            ->when($search !== '', fn ($query) => $query->where('configuration_snapshot->title', 'like', '%'.$search.'%'))
            ->latest('started_at')
            ->paginate(20)
            ->withQueryString();

        return view('history.index', [
            'attempts' => $attempts,
            'search' => $search,
            'status' => $status,
            'skill' => $skill,
            'skills' => Exercise::SKILLS,
            'statuses' => Attempt::STATUSES,
        ]);
    }
}
