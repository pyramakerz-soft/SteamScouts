@extends('layouts.app')

@section('title')
    eBooks for {{ $lesson->title }}
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
                    {{-- @if ($userAuth->image)
                        <img src="{{ asset('storage/' . $userAuth->image) }}" alt="Student Image"
                            class="w-20 h-20 rounded-full">
                    @else
                        <img src="{{ asset('storage/students/profile-png.webp') }}" alt="Student Image"
                            class="w-30 h-20 rounded-full">
                    @endif --}}
                    <img  class="w-20 h-20 rounded-full " alt="avatar" src="{{ $userAuth->image ? asset('storage/' . $userAuth->image)  : asset('images/default_user.jpg') }}" />

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
        <a href="{{ route("student.theme") }}" class="mx-2 cursor-pointer">Theme</a>
        <span class="mx-2 text-[#D0D5DD]">/</span>
        <a href="{{ route("student_units.index", $lesson->chapter->unit_id) }}" class="mx-2 cursor-pointer">Unit</a>
        <span class="mx-2 text-[#D0D5DD]">/</span>
        <a href="{{ route('student_lessons.index', $lesson->id) }}" class="mx-2 cursor-pointer">Lessons</a>
        <span class="mx-2 text-[#D0D5DD]">/</span>
        <a href="#" class="mx-2 cursor-pointer">Ebook</a>
    </div>

    <!-- Display eBooks -->
    <div class="flex flex-wrap p-3">
        @foreach ($lesson->ebooks as $ebook)
            <div class="mb-7 w-full md:w-[45%] lg:w-[30%] p-5 mx-2 bg-white shadow-md rounded-xl">
                <div class="h-full">
                    <div class="cursor-pointer h-full flex flex-col justify-center">
                        <h3 class="text-lg font-bold text-gray-700">{{ $ebook->title }}</h3>
                        <p class="text-gray-600">{{ $ebook->description }}</p>

                        <!-- Lesson Name -->
                        <p class="text-gray-500">Lesson: {{ $lesson->title }}</p>

                        <!-- eBook Download Link -->
                        <a href="{{ asset('storage/' . $ebook->file_path) }}"
                            class="inline-block mt-4 text-blue-500 hover:text-blue-700 font-semibold" target="_blank">View
                            eBook</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endsection
