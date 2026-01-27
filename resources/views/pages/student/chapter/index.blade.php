@extends('layouts.app')

@section('title')
    @yield('title')
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
                            class="w-20 h-20 rounded-full object-cover">
                    @else
                        <img src="{{ asset('storage/students/profile-png.webp') }}" alt="Student Image"
                            class="w-30 h-20 rounded-full object-cover">
                    @endif --}}
                    <img class="w-20 h-20 rounded-full object-cover" alt="avatar"
                        src="{{ $userAuth->image ? asset('storage/' . $userAuth->image) : asset('images/default_user.jpg') }}" />
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
        <a href="#" class="mx-2 cursor-pointer">Chapters</a>
    </div>

    <div class="flex flex-wrap">
        @foreach ($unit->chapters as $chapter)
            <div class="w-full sm:w-1/2 lg:w-1/4 p-2">
                <div class="h-[350px] bg-white border border-slate-200 rounded-md">
                    <a class="cursor-pointer" href="{{ route('student_lessons.index', $chapter->id) }}">
                        <img class="card-img-top"
                            src="{{ $chapter->image ? asset('storage/' . $chapter->image) : asset('images/defaultCard.webp') }}"
                            alt="{{ $chapter->title }}">
                        <p class="py-5 px-2 text-slate-800 text-2xl font-semibold">
                            {{ $chapter->title }}
                        </p>
                    </a>
                </div>
            </div>
        @endforeach
    </div>
@endsection
