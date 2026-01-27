@extends('layouts.app')

@section('title')
{{ $unit->title }} Resources
@endsection

@php
$menuItems = [
['label' => 'Dashboard', 'icon' => 'fi fi-rr-table-rows', 'route' => route('student.index')],
['label' => 'Curriculum', 'icon' => 'fi fi-rr-book-open', 'route' => route('student.curriculum')],
['label' => 'Assignment', 'icon' => 'fas fa-home', 'route' => route('student.assignment')],
['label' => 'Chat', 'icon' => 'fa-solid fa-message', 'route' => route('chat.all')],
];
@endphp

<style>
    #resourceModal {
        position: fixed !important;
        z-index: 99999 !important;
    }
</style>

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
                <div class="text-xl">{{ $userAuth->username }}</div>
                <div class="text-sm">{{ $userAuth->stage->name }}</div>
            </div>
        </div>
    </div>
</div>

<div class="p-3 text-[#667085] my-8">
    <i class="fa-solid fa-house mx-2"></i>
    <span class="mx-2 text-[#D0D5DD]">/</span>
    <a href="{{ route('student.index') }}" class="mx-2 cursor-pointer">Units</a>
    <span class="mx-2 text-[#D0D5DD]">/</span>
    <a href="#" class="mx-2 cursor-pointer">{{ $unit->title }}</a>
    <span class="mx-2 text-[#D0D5DD]">/</span>
    <a href="#" class="mx-2 cursor-pointer">Resources</a>
</div>

<div class="flex flex-wrap p-3 justify-center">

    {{-- EBOOK --}}
    @if ($unit->ebook_path)
    <div class="mb-7 w-full md:w-[45%] lg:w-[30%] p-2 mx-2 bg-white rounded-xl">
        <a onclick="openModal('E-Book', '{{ asset('ebooks/' . $unit->ebook_path . '/index.html') }}')" class="cursor-pointer block">
            <h3 class="px-4 py-2 bg-gray-200 text-lg font-bold truncate">E-Book</h3>
            <div class="p-4">
                <img src="{{ asset($unit->image) }}" alt="EBook" class="object-cover w-full h-[250px] rounded-xl">
            </div>
        </a>
    </div>
    @endif

    {{-- WORKSHOP --}}
    @if ($unit->workshop_path)
    <div class="mb-7 w-full md:w-[45%] lg:w-[30%] p-2 mx-2 bg-white rounded-xl">
        <a onclick="openModal('Workshop', '{{ asset('ebooks/' . $unit->workshop_path . '/index.html') }}')" class="cursor-pointer block">
            <h3 class="px-4 py-2 bg-gray-200 text-lg font-bold truncate">Workshop</h3>
            <div class="p-4">
                <img src="{{ asset($unit->image) }}" alt="Workshop" class="object-cover w-full h-[250px] rounded-xl">
            </div>
        </a>
    </div>
    @endif

    {{-- VIDEO --}}
    @if ($unit->video_path)
    <div class="mb-7 w-full md:w-[45%] lg:w-[30%] p-2 mx-2 bg-white rounded-xl">
        <a onclick="openModal('Video', '{{ $unit->video_path }}')" class="cursor-pointer block">
            <h3 class="px-4 py-2 bg-gray-200 text-lg font-bold truncate">Video</h3>
            <div class="p-4">
                <img src="{{ asset($unit->image) }}" alt="Video" class="object-cover w-full h-[250px] rounded-xl">
            </div>
        </a>
    </div>
    @endif

    @if (!$unit->ebook_path && !$unit->workshop_path && !$unit->video_path)
    <p class="m-auto text-gray-500">No learning materials available for this unit.</p>
    @endif

</div>
@endsection

{{-- ------------------------------------------------------------------------------------------------------------------ --}}
{{-- SINGLE UNIVERSAL MODAL --}}
<div id="resourceModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 flex items-center justify-center z-[9999] hidden">
    <div class="bg-white rounded-lg shadow-lg h-[95vh] overflow-y-scroll w-[90%]" style="width: 100% !important;">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 id="modal-title" class="text-lg font-semibold text-gray-900"></h3>
            <button onclick="closeModal()" class="bg-gray-500 hover:bg-gray-600 text-white font-bold py-2 px-4 rounded">
                Close
            </button>
        </div>

        <div id="modal-content" class="relative"></div>
    </div>
</div>

@section('page_js')
<script>
    function openModal(title, filePath) {

        document.getElementById("modal-title").innerText = title;

        let html = "";

        if (title === "Video") {
            html = `<iframe width="100%" height="90%" src="${filePath}" frameborder="0" allowfullscreen></iframe>`;
        } else {
            html = `<iframe src="${filePath}" width="100%" height="90%" style="border:none;"></iframe>`;
        }

        document.getElementById("modal-content").innerHTML = html;

        document.getElementById("resourceModal").classList.remove("hidden");
    }

    function closeModal() {
        document.getElementById("resourceModal").classList.add("hidden");
    }
</script>
@endsection
