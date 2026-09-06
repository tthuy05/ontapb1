@php($editing = $vocabulary->exists)
<form method="POST" action="{{ $editing ? route('manage.vocabulary.update', $vocabulary) : route('manage.vocabulary.store') }}">
    @csrf
    @if ($editing) @method('PUT') @endif

    <div class="card mb-4"><div class="card-body p-4">
        <h2 class="h5 mb-3">Basics</h2>
        <div class="row g-3">
            <div class="col-12 col-md-6"><label class="form-label" for="term">Term or phrase <span class="text-danger">required</span></label><input class="form-control @error('term') is-invalid @enderror" id="term" name="term" maxlength="160" value="{{ old('term', $vocabulary->term) }}" required>@error('term')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-12 col-md-6"><label class="form-label" for="topic_id">Topic <span class="text-danger">required</span></label><select class="form-select @error('topic_id') is-invalid @enderror" id="topic_id" name="topic_id" required><option value="">Choose a topic</option>@foreach ($topics as $topic)<option value="{{ $topic->id }}" @selected((string) old('topic_id', $vocabulary->topic_id) === (string) $topic->id)>{{ $topic->name }} ({{ $topic->status }})</option>@endforeach</select>@error('topic_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-12 col-md-6"><label class="form-label" for="part_of_speech">Part of speech</label><input class="form-control @error('part_of_speech') is-invalid @enderror" id="part_of_speech" name="part_of_speech" maxlength="40" value="{{ old('part_of_speech', $vocabulary->part_of_speech) }}">@error('part_of_speech')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-12 col-md-6"><label class="form-label" for="phonetic">Phonetic text</label><input class="form-control @error('phonetic') is-invalid @enderror" id="phonetic" name="phonetic" maxlength="120" value="{{ old('phonetic', $vocabulary->phonetic) }}">@error('phonetic')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        </div>
    </div></div>

    <div class="card mb-4"><div class="card-body p-4">
        <h2 class="h5 mb-3">Study content</h2>
        <div class="vstack gap-3">
            <div><label class="form-label" for="definition">English meaning <span class="text-danger">required</span></label><textarea class="form-control @error('definition') is-invalid @enderror" id="definition" name="definition" rows="3" required>{{ old('definition', $vocabulary->definition) }}</textarea>@error('definition')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div><label class="form-label" for="translation">Vietnamese note or translation</label><textarea class="form-control @error('translation') is-invalid @enderror" id="translation" name="translation" rows="2">{{ old('translation', $vocabulary->translation) }}</textarea>@error('translation')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div><label class="form-label" for="example_sentence">Original or licensed example</label><textarea class="form-control @error('example_sentence') is-invalid @enderror" id="example_sentence" name="example_sentence" rows="2">{{ old('example_sentence', $vocabulary->example_sentence) }}</textarea>@error('example_sentence')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div><label class="form-label" for="notes">Study notes</label><textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="3">{{ old('notes', $vocabulary->notes) }}</textarea>@error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div><label class="form-label" for="pronunciation_audio_path">Pronunciation audio path</label><input class="form-control @error('pronunciation_audio_path') is-invalid @enderror" id="pronunciation_audio_path" name="pronunciation_audio_path" maxlength="500" value="{{ old('pronunciation_audio_path', $vocabulary->pronunciation_audio_path) }}" placeholder="audio/pronunciation/example.mp3"><div class="form-text">Only reviewed files under public/audio/pronunciation are accepted.</div>@error('pronunciation_audio_path')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        </div>
    </div></div>

    @include('manage.vocabulary._source-fields')

    <div class="card mb-4"><div class="card-body p-4"><h2 class="h5">Publication</h2><label class="form-label" for="status">Status</label><select class="form-select @error('status') is-invalid @enderror" id="status" name="status">@foreach (\App\Models\Vocabulary::STATUSES as $status)<option value="{{ $status }}" @selected(old('status', $vocabulary->status ?: 'draft') === $status)>{{ ucfirst($status) }}</option>@endforeach</select>@error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
    <div class="d-flex flex-wrap gap-2"><button class="btn btn-primary" type="submit">{{ $editing ? 'Save entry' : 'Create entry' }}</button>@if ($editing)<a class="btn btn-outline-primary" href="{{ route('manage.vocabulary.show', $vocabulary) }}">Preview</a>@endif<a class="btn btn-outline-secondary" href="{{ route('manage.vocabulary.index') }}">Cancel</a></div>
</form>
