@extends('admin.layouts.layout')

@section('content')
    <div class="wrapper">
        @include('admin.layouts.sidebar')

        <div class="main">
            @include('admin.layouts.navbar')

            <main class="content">
                <div class="container-fluid p-0">

                    <h1>Lessons</h1>
                    @can('create lesson')
                        <a href="{{ route('lessons.create') }}" class="btn btn-primary mb-3">Add Lesson</a>
                    @endcan
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    {{-- Filter Form --}}
                    <form method="GET" action="{{ route('lessons.index') }}" class="row mb-4" id="filterForm">
                        <div class="col-md-4">
                            <label for="theme_id" class="form-label">Filter by Theme</label>
                            <select name="theme_id" id="theme_id" class="form-control">
                                <option value="">All Themes</option>
                                @foreach ($themes as $theme)
                                    <option value="{{ $theme->id }}"
                                        {{ request('theme_id') == $theme->id ? 'selected' : '' }}>
                                        {{ $theme->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="unit_id" class="form-label">Filter by Unit</label>
                            <select name="unit_id" id="unit_id" class="form-control">
                                <option value="">All Units</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}"
                                        {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </form>


                    <div class="table-responsive" style="overflow-x: auto;">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>File</th>
                                    <th>Theme</th>
                                    <th>Unit</th>
                                    <th>Chapter</th>
                                    <th>Image</th>
                                    <th>Active</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lessons as $lesson)
                                    <tr>
                                        <td>{{ $lesson->title }}</td>
                                        <td>
                                            @if ($lesson->file_path)
                                                <button class="btn btn-success" data-bs-toggle="modal"
                                                    data-bs-target="#ebookModal" data-file="{{ asset($lesson->file_path) }}">
                                                    View Student Ebook
                                                </button>    
                                            @endif
                                            

                                            @if($lesson->teacher_file_path)
                                                <button class="btn btn-success" data-bs-toggle="modal"
                                                data-bs-target="#ebookModal" data-file="{{ asset($lesson->teacher_file_path) }}">
                                                View Teacher Ebook
                                            </button>
                                            @endif
                                        </td>
                                        <td>{{ $lesson->chapter->material->title ?? '-' }}</td>
                                        <td>{{ $lesson->chapter->unit->title ?? '-' }}</td>
                                        <td>{{ $lesson->chapter->title }}</td>
                                        <td>
                                            @if ($lesson->image)
                                                <img src="{{ asset($lesson->image) }}" alt="{{ $lesson->title }}"
                                                    width="100">
                                            @else
                                                No Image
                                            @endif
                                        </td>
                                        <td>{{ $lesson->is_active ? 'Active' : 'Inactive' }}</td>
                                        <td class="d-flex align-items-center gap-2">
                                            @can('update lesson')
                                                <a href="{{ route('lessons.edit', $lesson->id) }}"
                                                    class="btn btn-info">Edit</a>
                                            @endcan
                                            @can('delete lesson')
                                                <form action="{{ route('lessons.destroy', $lesson->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger"
                                                        onclick="return confirm('Are you sure you want to delete this lesson?');">Delete</button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $lessons->appends(request()->input())->links('pagination::bootstrap-5') }}
                </div>
            </main>
        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="ebookModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg" style="max-width: 90%; max-height: 90%; margin: auto;">
            <div class="modal-content" style="height: 90vh;">
                <div class="modal-header">
                    <h5 class="modal-title">Ebook</h5>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
                <div class="position-relative modal-body" style="height: calc(100% - 60px);">
                    <embed src="" id="ebookEmbed" width="100%" height="100%" style="border: none;"></embed>
                    {{-- <img src="{{ asset('assets/img/watermark 2.png') }}"
                        class="position-absolute top-0 start-0 w-100 h-100" style="pointer-events: none; opacity: 0.5;"> --}}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page_js')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#ebookModal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var file = button.data('file');
                var modal = $(this);
                var embed = modal.find('#ebookEmbed');
                embed.attr('src', file);
            });

            $('#ebookModal').on('hide.bs.modal', function() {
                var embed = $(this).find('#ebookEmbed');
                embed.attr('src', '');
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('#theme_id, #unit_id').change(function() {
                $('#filterForm').submit();
            });
        });

        $('#clearFilters').click(function(e) {
            e.preventDefault();
            $('#school').val('').prop('selected', true);
            $('#class').val('').prop('selected', true);
            $('#filterForm').submit();
        });
    </script>
@endsection
