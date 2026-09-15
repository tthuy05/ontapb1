<?php

namespace App\Http\Controllers;

use App\Http\Requests\VocabularyReviewRatingRequest;
use App\Models\Vocabulary;
use App\Services\VocabularyReviewScheduler;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VocabularyReviewController extends Controller
{
    public function index(VocabularyReviewScheduler $scheduler): View
    {
        return view('vocabulary.review', [
            'reviewItems' => $scheduler->queue(),
            'reviewSummary' => $scheduler->summary(),
        ]);
    }

    public function store(
        VocabularyReviewRatingRequest $request,
        Vocabulary $vocabulary,
        VocabularyReviewScheduler $scheduler,
    ): RedirectResponse {
        $vocabulary->load('topic');
        abort_unless($vocabulary->status === 'active' && $vocabulary->topic->status === 'active', 404);

        $scheduler->recordRating($vocabulary, $request->string('rating')->toString());

        return redirect()
            ->route('vocabulary.review.index')
            ->with('status', 'Vocabulary review saved and the next review was scheduled.');
    }
}
