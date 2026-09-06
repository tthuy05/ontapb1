@php
    $editing = $grammarLesson->exists;
    $storedExamples = collect($grammarLesson->examples ?? [])->map(
        fn (array $example) => ($example['example'] ?? '').' | '.($example['explanation'] ?? '')
    )->implode(PHP_EOL);
@endphp
<form method="POST" action="{{ $editing ? route('manage.grammar.update', $grammarLesson) : route('manage.grammar.store') }}">
    @csrf
    @if ($editing) @method('PUT') @endif

    <div class="card mb-4"><div class="card-body p-4">
        <h2 class="h5 mb-3">Basics</h2><div class="row g-3">
            <div class="col-12 col-md-7"><label class="form-label" for="title">Title <span class="text-danger">required</span></label><input class="form-control @error('title') is-invalid @enderror" id="title" name="title" maxlength="180" value="{{ old('title', $grammarLesson->title) }}" required>@error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-12 col-md-5"><label class="form-label" for="topic_id">Topic <span class="text-danger">required</span></label><select class="form-select @error('topic_id') is-invalid @enderror" id="topic_id" name="topic_id" required><option value="">Choose a topic</option>@foreach ($topics as $topic)<option value="{{ $topic->id }}" @selected((string) old('topic_id', $grammarLesson->topic_id) === (string) $topic->id)>{{ $topic->name }} ({{ $topic->status }})</option>@endforeach</select>@error('topic_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-12 col-md-9"><label class="form-label" for="slug">Slug <span class="text-danger">required</span></label><input class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" maxlength="180" pattern="[A-Za-z0-9_-]+" value="{{ old('slug', $grammarLesson->slug) }}" required>@error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-12 col-md-3"><label class="form-label" for="position">Position</label><input class="form-control @error('position') is-invalid @enderror" id="position" name="position" type="number" min="0" max="65535" value="{{ old('position', $grammarLesson->position ?? 0) }}" required>@error('position')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        </div>
    </div></div>

    <div class="card mb-4"><div class="card-body p-4">
        <h2 class="h5 mb-3">Lesson content</h2><div class="vstack gap-3">
            <div><label class="form-label" for="objectives">Learning objectives <span class="text-danger">required</span></label><textarea class="form-control @error('objectives') is-invalid @enderror" id="objectives" name="objectives" rows="3" required>{{ old('objectives', $grammarLesson->objectives) }}</textarea>@error('objectives')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div><label class="form-label" for="prerequisites">Prerequisites</label><textarea class="form-control @error('prerequisites') is-invalid @enderror" id="prerequisites" name="prerequisites" rows="2">{{ old('prerequisites', $grammarLesson->prerequisites) }}</textarea>@error('prerequisites')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div><label class="form-label" for="body">Explanation <span class="text-danger">required</span></label><textarea class="form-control @error('body') is-invalid @enderror" id="body" name="body" rows="10" required>{{ old('body', $grammarLesson->body) }}</textarea><div class="form-text">Plain text is rendered with line breaks and safely escaped.</div>@error('body')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div><label class="form-label" for="examples_text">Examples</label><textarea class="form-control @error('examples_text') is-invalid @enderror @error('examples') is-invalid @enderror" id="examples_text" name="examples_text" rows="5" placeholder="Example sentence | Why this form is used">{{ old('examples_text', $storedExamples) }}</textarea><div class="form-text">Enter one example per line, with an explanation after a vertical bar (|). Maximum 20.</div>@error('examples_text')<div class="invalid-feedback">{{ $message }}</div>@enderror @error('examples')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div><label class="form-label" for="common_mistakes">Common mistakes</label><textarea class="form-control @error('common_mistakes') is-invalid @enderror" id="common_mistakes" name="common_mistakes" rows="4">{{ old('common_mistakes', $grammarLesson->common_mistakes) }}</textarea>@error('common_mistakes')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        </div>
    </div></div>

    @include('manage.grammar._source-fields')
    <div class="card mb-4"><div class="card-body p-4"><h2 class="h5">Publication</h2><label class="form-label" for="status">Status</label><select class="form-select @error('status') is-invalid @enderror" id="status" name="status">@foreach (\App\Models\GrammarLesson::STATUSES as $status)<option value="{{ $status }}" @selected(old('status', $grammarLesson->status ?: 'draft') === $status)>{{ ucfirst($status) }}</option>@endforeach</select>@error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
    <div class="d-flex flex-wrap gap-2"><button class="btn btn-primary" type="submit">{{ $editing ? 'Save lesson' : 'Create lesson' }}</button>@if ($editing)<a class="btn btn-outline-primary" href="{{ route('manage.grammar.show', $grammarLesson) }}">Preview</a>@endif<a class="btn btn-outline-secondary" href="{{ route('manage.grammar.index') }}">Cancel</a></div>
</form>
