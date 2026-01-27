@extends('layouts.app')
@section('title')
Units
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

@section('page_css')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC"
    crossorigin="anonymous">

<style>
    body {
        background-color: #f8fafc;
    }

    .header-card {
        border-radius: 18px;
        padding: 20px;
        background: linear-gradient(to right, #2E3646, #1F2533);
        color: white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
    }

    .section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #17253e;
        border-bottom: 3px solid #525d6f;
        display: inline-block;
        margin-bottom: 1rem;
        padding-bottom: 0.3rem;
    }

    .resource-card {
        background-color: #ffffff;
        border: none;
        border-radius: 12px;
        padding: 0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        cursor: pointer;
        overflow: hidden;
    }

    .resource-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }

    .unit-img {
        width: 100%;
        height: 160px;
        object-fit: cover;
    }

    .unit-title {
        font-size: 1.2rem;
        font-weight: 700;
        color: #17253e;
        margin-bottom: 6px;
    }

    .unit-link {
        text-decoration: none;
        font-weight: 600;
        color: #17253e;
    }

    .unit-link:hover {
        text-decoration: underline;
        color: #0f1827;
    }
</style>
@endsection


@section('sidebar')
@include('components.sidebar', ['menuItems' => $menuItems])
@endsection


@section('content')
<div class="container py-4">

    <!-- Header -->
    <div class="header-card d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <img src="{{ $student->image ? asset($student->image) : asset('images/default_user.jpg') }}"
                class="rounded-circle me-3" width="70" height="70" style="object-fit: cover; border:3px solid white;">
            <div>
                <h4 class="mb-1">{{ $student->username }}</h4>
                <small class="text-light">{{ $student->stage->name }}</small>
            </div>
        </div>

        <button onclick="openEditModal('editPassword')" class="btn btn-light">
            <i class="fas fa-edit"></i>
        </button>
    </div>

    <!-- Breadcrumb -->
    <div class="text-secondary small mb-4">
        <i class="fa-solid fa-house"></i>
        <span class="mx-1">/</span>
        <span>Units</span>
    </div>

    <!-- Materials -->
    @foreach ($materials as $material)
    <h3 class="section-title">{{ $material->title }}</h3>

    <div class="row g-4 mb-5">

        @forelse($material->units as $unit)
        <div class="col-12 col-md-6 col-lg-4">
            <div class="resource-card">

                @if ($unit->image)
                <img src="{{ $unit->image }}" class="unit-img">
                @else
                <div class="d-flex align-items-center justify-content-center unit-img bg-light text-muted">
                    No Image
                </div>
                @endif

                <div class="p-3">
                    <div class="unit-title">{{ $unit->title }}</div>

                    <a href="{{ route('student_units.unitContent', $unit->id) }}" class="unit-link">
                        View Details →
                    </a>
                </div>

            </div>
        </div>
        @empty
        <p class="text-muted fst-italic">No units available.</p>
        @endforelse

    </div>

    @endforeach

</div>
@endsection

@section('page_js')
<script>
    function openEditModal(id) {
        document.getElementById(id).classList.remove("d-none");
    }

    function closeModal(id) {
        document.getElementById(id).classList.add("d-none");
    }
</script>
@endsection


<!-- Password Modal -->
<form action="{{ route('changeStudentPassword') }}" method="POST"
    id="editPassword"
    class="position-fixed top-0 start-0 w-100 h-100 d-none"
    style="background: rgba(0,0,0,0.6); z-index:1000;">
    @csrf

    <div class="bg-white rounded-3 shadow-lg p-4 mx-auto mt-5" style="max-width: 500px;">
        <div class="d-flex justify-content-between mb-3">
            <h5>Edit Password</h5>
            <button type="button" class="btn btn-secondary btn-sm" onclick="closeModal('editPassword')">Close</button>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Password</label>
            <input type="password" name="password" required class="form-control" placeholder="Enter new password">
        </div>

        <button class="btn btn-primary w-100">Save</button>
    </div>
</form>
