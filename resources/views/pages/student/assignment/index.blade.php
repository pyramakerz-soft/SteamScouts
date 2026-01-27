@extends('layouts.app')

@section('title')
    Assignment
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
                    {{-- <img class="w-20 h-20 rounded-full" alt="avatar1" src="{{ Auth::guard('student')->user()->image }}" /> --}}
                    {{-- @if ($userAuth->image)
                    <img src="{{ asset($userAuth->image) }}" alt="Student Image"
                        class="w-20 h-20 rounded-full object-cover">
                @else
                    <img src="{{ asset('storage/students/profile-png.webp') }}" alt="Student Image"
                        class="w-30 h-20 rounded-full object-cover">
                @endif --}}

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
    </div>
    <div class="p-3 text-[#667085] my-8">
        <i class="fa-solid fa-house mx-2"></i>
        <span class="mx-2 text-[#D0D5DD]">/</span>
        <a href="#" class="mx-2 cursor-pointer">Assignment</a>
    </div>
    <div class="p-3">
        <div class="overflow-x-auto rounded-2xl border border-[#EAECF0]">
            <table class="w-full table-auto bg-[#FFFFFF] text-left text-[#475467] text-lg md:text-xl">
                <thead class="bg-[#F9FAFB]">
                    <tr>
                        <th class="py-4 px-6 min-w-[120px] whitespace-nowrap">
                            Title <i class="fa-solid fa-arrow-down mx-2"></i>
                        </th>
                        <th class="py-4 px-6 min-w-[120px] whitespace-nowrap">
                            Description <i class="fa-solid fa-arrow-down mx-2"></i>
                        </th>
                        <th class="py-4 px-6 min-w-[120px] whitespace-nowrap">
                            Start Date <i class="fa-solid fa-arrow-down mx-2"></i>
                        </th>
                        <th class="py-4 px-6 min-w-[120px] whitespace-nowrap">
                            Due Date <i class="fa-solid fa-arrow-down mx-2"></i>
                        </th>
                        <th class="py-4 px-6 min-w-[120px] whitespace-nowrap">
                            Marks <i class="fa-solid fa-arrow-down mx-2"></i>
                        </th>
                        <th class="py-4 px-6 min-w-[120px] whitespace-nowrap">
                            Actions <i class="fa-solid fa-arrow-down mx-2"></i>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @if (count($assignments) === 0)
                        <tr>
                            <td colspan="6" class="px-4 py-4 h-[72px] text-center border-t border-gray-300">
                                No Data Found
                            </td>
                        </tr>
                    @endif

                    @foreach ($assignments as $assignment)
                        <tr class="border-t border-gray-300 {{ $loop->index % 2 === 0 ? 'bg-[#F4F4F4]' : 'bg-white' }}">
                            <td class="py-5 px-6">{{ $assignment->title }}</td>
                            <td class="py-5 px-6">{{ $assignment->description }}</td>
                            <td class="py-5 px-6">{{ $assignment->start_date }}</td>
                            <td class="py-5 px-6">{{ $assignment->due_date }}</td>
                            {{-- <td class="py-5 px-6">{{ $assignment->marks }}</td> --}}
                            <td class="py-5 px-6">
                                {{ $assignment->student_marks ?? 'Not Graded' }} / {{ $assignment->marks }}
                            </td>
                            <td class="py-5 px-6">
                                @if ($assignment->student_marks)
                                    <p class="text-gray-500">
                                        View Assignment
                                    </p>
                                @else
                                    <a href="{{ route('student.assignment.show', $assignment->id) }}"
                                        class="text-[#FF7519] cursor-pointer">
                                        View Assignment
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
