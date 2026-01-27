@extends('layouts.app')

@section('title')
    Unit
@endsection

@php
    $menuItems = [
        ['label' => 'Dashboard', 'icon' => 'fi fi-rr-table-rows', 'route' => route('student.theme')],
        ['label' => 'Curriculum', 'icon' => 'fi fi-rr-book-open', 'route' => route('student.curriculum')],
        ['label' => 'Assignment', 'icon' => 'fas fa-home', 'route' => route('student.assignment')],
        ['label' => 'Chat', 'icon' => 'fa-solid fa-message', 'route' => route('chat.all')],
    ];
@endphp

@section('sidebar')
    @include('components.sidebar', ['menuItems' => $menuItems])
@endsection

@section('content')
    <style>
        .header-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 24px;
            background-color: #2E3646;
            border-radius: 12px;
        }

        .header-profile {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
        }

        .user-info {
            color: white;
            font-weight: 600;
        }

        .breadcrumb {
            padding: 16px;
            color: #667085;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .breadcrumb span {
            color: #D0D5DD;
        }

        .breadcrumb a {
            color: inherit;
            text-decoration: none;
            cursor: pointer;
        }

        .accordion {
            margin: 16px 0;
            width: 100%;
        }

        .accordion-button {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 12px;
            background-color: #F0F0F0;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
            border: none;
            outline: none;
        }

        .accordion-button:hover {
            background-color: #2E3646;
            color: white;
        }

        .accordion-body {
            display: none;
            padding: 16px;
            width: 100%;
        }

        .accordion-body.open {
            display: block;
        }

        .unit-header {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .unit-image {
            width: 50px;
            height: 44px;
            border-radius: 4px;
        }

        .unit-title {
            font-size: 1.5rem;
            margin-top: 4px;
        }

        .chapters {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
        }

        .chapter {
            width: calc(33.33% - 32px);
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 16px;
        }

        .chapter img {
            width: 100%;
            height: 250px;
            object-fit: contain;
            border-radius: 12px;
        }

        .chapter-title {
            margin-top: 8px;
            font-size: 1.5rem;
            color: #2D3748;
            text-align: center;
        }
    </style>

    <div class="p-3">
        <div class="header-container">
            <div class="header-profile">
                <img class="avatar" src="{{ $userAuth->image ? asset($userAuth->image) : asset('images/default_user.jpg') }}"
                    alt="avatar">
                <div class="user-info">
                    <div class="text-xl">{{ $userAuth->username }}</div>
                    <div class="text-sm">{{ $userAuth->stage->name }}</div>
                </div>
            </div>
        </div>

        @yield('insideContent')
    </div>

    <div class="breadcrumb">
        <i class="fa-solid fa-house"></i>
        <span>/</span>
        <a href="{{ route('student.theme') }}">Theme</a>
        <span>/</span>
        <a href="#">Unit</a>
    </div>

    <div class="accordion">
        @foreach ($material->units as $unit)
            <div class="m-3">
                <h2>
                    <button type="button" class="accordion-button" data-accordion-target="#accordion-{{ $unit->id }}">
                        <div class="unit-header">
                            <img src="{{ asset('images/unit' . min($loop->iteration, 3) . '.png') }}" class="unit-image">
                            <span class="unit-title">{{ $unit->title }}</span>
                        </div>
                        <svg class="w-3 h-3 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 6" fill="none">
                            <path d="M9 5L5 1 1 5" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                    </button>
                </h2>
                <div id="accordion-{{ $unit->id }}" class="accordion-body">
                    <a href="{{ route('student_units.unitContent', $unit->id) }}" class="unit-link">
                        Unit Resources →
                    </a>

                    <div class="chapters">
                        @forelse ($unit->chapters as $chapter)
                            <div class="chapter">
                                <a href="{{ route('student_lessons.index', $chapter->id) }}">
                                    <img src="{{ $chapter->image ? asset($chapter->image) : asset('images/defaultCard.webp') }}"
                                        alt="{{ $chapter->title }}">
                                    <p class="chapter-title">{{ $chapter->title }}</p>
                                </a>
                            </div>
                        @empty
                            <p>No chapters available </p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <script>
        document.querySelectorAll('.accordion-button').forEach(button => {
            button.addEventListener('click', () => {
                const target = button.getAttribute('data-accordion-target');
                const body = document.querySelector(target);
                body.classList.toggle('open');
            });
        });
    </script>
@endsection
