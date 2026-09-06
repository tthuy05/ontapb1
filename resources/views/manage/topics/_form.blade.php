@php($editing = $topic->exists)
<form method="POST" action="{{ $editing ? route('manage.topics.update', $topic) : route('manage.topics.store') }}">
    @csrf
    @if ($editing) @method('PUT') @endif

    <div class="card mb-4"><div class="card-body p-4">
        <h2 class="h5 mb-3">Topic details</h2>
        <div class="row g-3">
            <div class="col-12 col-md-8"><label class="form-label" for="name">Name <span class="text-danger">required</span></label><input class="form-control @error('name') is-invalid @enderror" id="name" name="name" maxlength="120" value="{{ old('name', $topic->name) }}" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-12 col-md-4"><label class="form-label" for="area">Area <span class="text-danger">required</span></label><select class="form-select @error('area') is-invalid @enderror" id="area" name="area" required>@foreach (\App\Models\Topic::AREAS as $area)<option value="{{ $area }}" @selected(old('area', $topic->area ?: 'vocabulary') === $area)>{{ ucfirst($area) }}</option>@endforeach</select>@error('area')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-12"><label class="form-label" for="slug">Slug <span class="text-danger">required</span></label><input class="form-control @error('slug') is-invalid @enderror" id="slug" name="slug" maxlength="140" value="{{ old('slug', $topic->slug) }}" pattern="[A-Za-z0-9_-]+" required>@error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-12"><label class="form-label" for="description">Description</label><textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $topic->description) }}</textarea>@error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-6 col-md-4"><label class="form-label" for="position">Position</label><input class="form-control @error('position') is-invalid @enderror" id="position" name="position" type="number" min="0" max="65535" value="{{ old('position', $topic->position ?? 0) }}" required>@error('position')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-6 col-md-4"><label class="form-label" for="priority">Priority</label><select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority">@foreach ([1 => 'High', 2 => 'Medium', 3 => 'Low'] as $value => $label)<option value="{{ $value }}" @selected((int) old('priority', $topic->priority ?? 2) === $value)>{{ $label }}</option>@endforeach</select>@error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-12 col-md-4"><label class="form-label" for="status">Status</label><select class="form-select @error('status') is-invalid @enderror" id="status" name="status">@foreach (\App\Models\Topic::STATUSES as $status)<option value="{{ $status }}" @selected(old('status', $topic->status ?: 'draft') === $status)>{{ ucfirst($status) }}</option>@endforeach</select>@error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        </div>
    </div></div>
    <div class="d-flex gap-2"><button class="btn btn-primary" type="submit">{{ $editing ? 'Save topic' : 'Create topic' }}</button><a class="btn btn-outline-secondary" href="{{ route('manage.topics.index') }}">Cancel</a></div>
</form>
