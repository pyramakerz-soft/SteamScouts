@extends('layouts.app')

@section('title')
    Lessons for {{ $chapter->title }}
@endsection

@php
    $menuItems = [
        ['label' => 'Dashboard', 'icon' => 'fi fi-rr-table-rows', 'route' => route('student.index')],
        ['label' => 'Curriculum', 'icon' => 'fi fi-rr-book-open', 'route' => route('student.curriculum')],
        ['label' => 'Assignment', 'icon' => 'fas fa-home', 'route' => route('student.assignment')],
        ['label' => 'Chat', 'icon' => 'fa-solid fa-message', 'route' => route('chat.all')],
    ];
@endphp

@section('sidebar')
    @include('components.sidebar', ['menuItems' => $menuItems])
@endsection

@section('content')
    <div class="p-3">
        <div class="rounded-lg flex items-center justify-between py-3 px-6 bg-[#2E3646]">
            <div class="flex items-center space-x-4">
                <div>
                    <img class="w-20 h-20 rounded-full object-cover" alt="avatar"
                        src="{{ $userAuth->image ? asset($userAuth->image) : asset('images/default_user.jpg') }}" />
                </div>

                <div class="ml-3 font-semibold text-white flex flex-col space-y-2">
                    <div class="text-xl">
                        {{ $userAuth->username }}
                    </div>
                    <div class="text-sm">
                        {{ $userAuth->stage->name }}
                    </div>
                </div>
            </div>
        </div>
        @yield('insideContent')
    </div>

    <div class="p-3 text-[#667085] my-8">
        <i class="fa-solid fa-house mx-2"></i>
        <span class="mx-2 text-[#D0D5DD]">/</span>
        <a href="{{ route('student.theme') }}" class="mx-2 cursor-pointer">Theme</a>
        <span class="mx-2 text-[#D0D5DD]">/</span>
        <a href="{{ route('student_units.index', $chapter->material_id) }}" class="mx-2 cursor-pointer">Unit</a>
        <span class="mx-2 text-[#D0D5DD]">/</span>
        <a href="#" class="mx-2 cursor-pointer">lessons</a>
    </div>

    <div class="p-3">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-semibold text-[#17253E]">Lesson Preview</h2>
                <span id="lesson-title" class="text-sm text-gray-500">Select a lesson to preview.</span>
            </div>
            <div class="w-full h-[70vh] border border-gray-200 rounded-lg overflow-hidden bg-gray-50">
                <iframe id="lesson-iframe" title="Lesson preview" class="w-full h-full" src=""></iframe>
            </div>
        </div>
    </div>

    <div class="flex flex-wrap p-3">
        @if (!count($chapter->lessons) == 0)
            @foreach ($chapter->lessons as $lesson)
                <div class="mb-7 w-full md:w-[45%] lg:w-[30%] p-2 mx-2 bg-white  rounded-xl">
                    <div class="w-full">
                        <button type="button"
                            onclick="setLessonPreview(@json($lesson->file_path), @json($lesson->title))"
                            class="w-full text-left cursor-pointer h-full flex flex-col justify-between">
                            <!-- Updated title to handle long text -->
                            <h3 class="px-4 py-2 bg-gray-200 text-lg font-bold truncate"
                                style="overflow: hidden; white-space: nowrap; text-overflow: ellipsis;">
                                {{ $lesson->title }}
                            </h3>
                            <div class="p-4">
                                <img class="object-contain w-full h-[250px] rounded-xl"
                                    src="{{ $lesson->image ? asset($lesson->image) : asset('images/defaultCard.webp') }}"
                                    alt="{{ $lesson->title }}">

                            </div>
                        </button>
                    </div>
                </div>
            @endforeach
        @else
            <p class="m-auto text-gray-500">No Lessons yet</p>
        @endif
    </div>
@endsection


@section('page_js')
    <script>
        function setLessonPreview(filePath, title) {
            const iframe = document.getElementById('lesson-iframe');
            const lessonTitle = document.getElementById('lesson-title');
            iframe.src = filePath;
            lessonTitle.textContent = title;
        }
    </script>
@endsection
