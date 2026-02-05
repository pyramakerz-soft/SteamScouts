@extends('layouts.app')

@section('title')
    Lessons for {{ $chapter->title }}
@endsection

@php
    $menuItems = [
        ['label' => 'Dashboard', 'icon' => 'fi fi-rr-table-rows', 'route' => route('teacher.dashboard')],
        ['label' => 'Resources', 'icon' => 'fi fi-rr-table-rows', 'route' => route('teacher.resources.index')],
        ['label' => 'Ticket', 'icon' => 'fa-solid fa-ticket', 'route' => route('teacher.tickets.index')],

        ['label' => 'Chat', 'icon' => 'fa-solid fa-message', 'route' => route('chat.all')],
    ];
@endphp

@section('sidebar')
    @include('components.sidebar', ['menuItems' => $menuItems])
@endsection

@section('content')
    @include('components.profile')

    <div class="p-3 text-[#667085] my-8">
        <i class="fa-solid fa-house mx-2"></i>
        <span class="mx-2 text-[#D0D5DD]">/</span>
        <a href="{{ route('teacher.dashboard') }}" class="mx-2 cursor-pointer">Grade</a>
        <span class="mx-2 text-[#D0D5DD]">/</span>
        <a href="{{ route('teacher.units', $chapter->material_id) }}" class="mx-2 cursor-pointer">Units</a>
        <span class="mx-2 text-[#D0D5DD]">/</span>
        <a href="#" class="mx-2 cursor-pointer">Lessons</a>
    </div>

    <!-- Display Lessons -->
    <div class="flex flex-wrap">
        @foreach ($chapter->lessons as $lesson)
            <div class="w-full sm:w-1/2 lg:w-1/4 p-3">
                <div class=" bg-white ">

                    <div class="p-4">
                        <button onclick="handleLessonClick(`{{ $lesson->file_path }}`,`{{ $lesson->teacher_file_path }}`)"
                        class="object-cover w-full">
                            <img src="{{ $lesson->image ? asset($lesson->image) : asset('images/defaultCard.webp') }}"
                                alt="{{ $lesson->title }}">
                        </button>
                        {{-- <button onclick="  openModal('ebook', `https://pyramakerz-artifacts.com/LMS/lms_pyramakerz/public/ebooks/Grade 3/G3 - Nature's Balance`);" class="object-cover w-full"> --}}

                    </div>
                    <h3 class="px-4 py-2 text-lg font-bold truncate">{{ $lesson->title }}</h3>
                </div>
            </div>
        @endforeach
        @if (count($chapter->lessons) == 0)
            <p class="m-auto text-gray-500">No Lessons yet</p>
        @endif
    </div>
@endsection

{{-- ------------------------------------------------------------------------------------- --}}

{{-- Ebook Modal --}}
<div id="ebook" class="inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center z-[999] hidden">
    <div class="bg-white rounded-lg shadow-lg h-[95vh] overflow-y-scroll w-[90%]"  style="height: 95% !important">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">
                EBook
            </h3>
            <div class="flex justify-end">
                <button onclick="closeModal('ebook')"
                    class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">Close</button>
            </div>
        </div>

        <!-- <div id="ebook-content" class="relative"> -->
        <iframe id="ebook-frame" style="display:none" class="relative" width="100%" height="90%"></iframe>

    </div>
</div>

<div id="ebookChooser" class="inset-0 bg-gray-800 bg-opacity-50 hidden z-[1000] flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 w-[300px] text-center">
        <h3 class="text-lg font-semibold mb-4">Choose Ebook</h3>

        <p id="noEbookMsg" class="text-gray-500 mb-4 hidden">
            No ebooks available
        </p>

        <button
            id="studentBtn"
            class="w-full mb-3 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded hidden"
        >
            Student Ebook
        </button>

        <button
            id="teacherBtn"
            class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded hidden"
        >
            Teacher Ebook
        </button>

        <button onclick="closeChooser()" class="mt-4 text-gray-500 text-sm">
            Cancel
        </button>
    </div>
</div>


{{-- <img src="{{ asset('assets/img/watermark 2.png') }}" 
class="absolute inset-0 w-full h-full opacity-50 z-10"
style="pointer-events: none;"> --}}
@section('page_js')
<script>
    function handleLessonClick(studentPath, teacherPath) {

        const hasStudent = studentPath && studentPath !== "";
        const hasTeacher = teacherPath && teacherPath !== "";

        // If both exist → show chooser
        if (hasStudent && hasTeacher) {
            openChooser(studentPath, teacherPath);
            return;
        }

        // Otherwise open the one that exists
        if (hasTeacher) {
            openEbook(teacherPath);
        } else if (hasStudent) {
            openEbook(studentPath);
        }
    }

    function openChooser(studentPath, teacherPath) {
    const chooser = document.getElementById('ebookChooser');
    const studentBtn = document.getElementById('studentBtn');
    const teacherBtn = document.getElementById('teacherBtn');
    const noMsg = document.getElementById('noEbookMsg');

    // reset state
    studentBtn.classList.add('hidden');
    teacherBtn.classList.add('hidden');
    noMsg.classList.add('hidden');

    const hasStudent = studentPath && studentPath !== "";
    const hasTeacher = teacherPath && teacherPath !== "";

    if (!hasStudent && !hasTeacher) {
        noMsg.classList.remove('hidden');
    }

    if (hasStudent) {
        studentBtn.classList.remove('hidden');
        studentBtn.onclick = () => {
            closeChooser();
            openEbook(studentPath);
        };
    }

    if (hasTeacher) {
        teacherBtn.classList.remove('hidden');
        teacherBtn.onclick = () => {
            closeChooser();
            openEbook(teacherPath);
        };
    }

    chooser.classList.remove('hidden');
    chooser.classList.add('fixed');
}


    function closeChooser() {
        const chooser = document.getElementById('ebookChooser');
        chooser.classList.add('hidden');
        chooser.classList.remove('fixed');
    }

    function openEbook(filePath) {
        const iframe = document.getElementById('ebook-frame');
        iframe.src = filePath;

        iframe.onload = () => {
            const doc = iframe.contentWindow.document;
            const bar = doc.querySelector('.buttonBar.right');
            if (bar) bar.style.display = 'none';
        };

        document.getElementById('ebook').classList.remove("hidden");
        document.getElementById('ebook').classList.add("fixed");
        iframe.style.display = 'block';
    }

    function closeModal(id) {
        document.getElementById(id).classList.add("hidden");
        document.getElementById('ebook').classList.remove("fixed");
        document.getElementById('ebook-frame').src = "";
    }
</script>
@endsection

