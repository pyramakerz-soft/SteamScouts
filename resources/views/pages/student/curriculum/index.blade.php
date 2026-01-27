@extends('layouts.app')

@section('title')
    Curriculum
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
            <div>
                <button onclick="openEditModal('editPassword')">
                    <i class="fas fa-edit text-white text-xl"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="p-3 text-[#667085] my-8">
        <i class="fa-solid fa-house mx-2"></i>
        <span class="mx-2 text-[#D0D5DD]">/</span>
        <a href="#" class="mx-2 cursor-pointer">Curriculum</a>
    </div>

    <div class="p-3 space-y-6">
        @forelse ($materials as $material)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <details class="group" open>
                    <summary
                        class="flex items-center justify-between px-6 py-4 cursor-pointer text-[#17253E] font-semibold text-lg">
                        <div>
                            <div>{{ $material->title }}</div>
                            <div class="text-sm text-gray-500 font-normal">{{ $material->units->count() }} Units</div>
                        </div>
                        <span class="text-[#FF7519] transition-transform group-open:rotate-180">
                            <i class="fa-solid fa-chevron-down"></i>
                        </span>
                    </summary>

                    <div class="px-6 pb-6 space-y-4">
                        @forelse ($material->units as $unit)
                            <details class="group rounded-lg border border-gray-100">
                                <summary
                                    class="flex items-center justify-between px-4 py-3 cursor-pointer text-[#17253E] font-semibold">
                                    <div>
                                        <a href="{{ route('student_units.unitContent', $unit->id) }}"
                                            class="hover:underline">
                                            {{ $unit->title }}
                                        </a>
                                        <div class="text-xs text-gray-500 font-normal">
                                            {{ $unit->chapters->count() }} Chapters
                                        </div>
                                    </div>
                                    <span class="text-[#FF7519] transition-transform group-open:rotate-180">
                                        <i class="fa-solid fa-chevron-down"></i>
                                    </span>
                                </summary>

                                <div class="px-4 pb-4 space-y-3">
                                    @forelse ($unit->chapters as $chapter)
                                        <details class="group rounded-lg border border-gray-100 bg-gray-50">
                                            <summary
                                                class="flex items-center justify-between px-4 py-3 cursor-pointer text-[#17253E]">
                                                <div>
                                                    <a href="{{ route('student_lessons.index', $chapter->id) }}"
                                                        class="font-semibold hover:underline">
                                                        {{ $chapter->title }}
                                                    </a>
                                                    <div class="text-xs text-gray-500">
                                                        {{ $chapter->lessons->count() }} Lessons
                                                    </div>
                                                </div>
                                                <span class="text-[#FF7519] transition-transform group-open:rotate-180">
                                                    <i class="fa-solid fa-chevron-down"></i>
                                                </span>
                                            </summary>

                                            <div class="px-4 pb-4">
                                                <ul class="ml-4 list-disc space-y-2 text-sm text-gray-600">
                                                    @forelse ($chapter->lessons as $lesson)
                                                        <li class="flex items-center justify-between">
                                                            <a href="{{ route('student_lessons.ebooks', $lesson->id) }}"
                                                                class="text-[#17253E] hover:underline">
                                                                {{ $lesson->title }}
                                                            </a>
                                                            <span class="text-xs text-gray-400">Lesson</span>
                                                        </li>
                                                    @empty
                                                        <li class="text-gray-500">No lessons available.</li>
                                                    @endforelse
                                                </ul>
                                            </div>
                                        </details>
                                    @empty
                                        <p class="text-sm text-gray-500">No chapters available.</p>
                                    @endforelse
                                </div>
                            </details>
                        @empty
                            <p class="text-sm text-gray-500">No units available.</p>
                        @endforelse
                    </div>
                </details>
            </div>
        @empty
            <p class="text-center text-gray-500">No curriculum assigned yet.</p>
        @endforelse
    </div>
@endsection

<form action="{{ route('changeStudentPassword') }}" method="POST" id="editPassword"
    class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center z-10 hidden">
    @csrf
    <div class="bg-white rounded-lg shadow-lg  w-[50%]">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900">
                Edit password
            </h3>
            <div class="flex justify-end">
                <button onclick="closeModal('editPassword')" type="button"
                    class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">Close</button>
            </div>
        </div>

        <div class="px-3 mb-3">
            <div class="rounded-2xl bg-[#F6F6F6] text-start px-4 md:px-6 py-3 md:py-4 my-4 md:my-5">
                <p class="font-semibold text-base md:text-lg text-[#1C1C1E]">Password</p>
                <input placeholder="Change Your Password" name="password" required
                    class="w-full rounded-2xl p-2 md:p-4 mt-5 text-sm md:text-base" type="password"
                    value="">
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="bg-[#17253E] text-white font-semibold py-2 px-6 rounded-xl">Save</button>
            </div>
        </div>
    </div>
</form>

<script>
    function openEditModal(id) {
        document.getElementById(id).classList.remove("hidden");
    }

    function closeModal(id) {
        document.getElementById(id).classList.add("hidden");
    }
</script>
