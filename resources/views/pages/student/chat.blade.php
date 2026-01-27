@extends('layouts.app')
@section('title')
    Chat
@endsection
@php
    if (auth()->guard('student')->check()) {
        $menuItems = [
            ['label' => 'Dashboard', 'icon' => 'fi fi-rr-table-rows', 'route' => route('student.index')],
            ['label' => 'Curriculum', 'icon' => 'fi fi-rr-book-open', 'route' => route('student.curriculum')],
            ['label' => 'Assignment', 'icon' => 'fas fa-home', 'route' => route('student.assignment')],
            ['label' => 'Chat', 'icon' => 'fa-solid fa-message', 'route' => route('chat.all')],
        ];
    } elseif (auth()->guard('teacher')->check()) {
        $menuItems = [
            ['label' => 'Dashboard', 'icon' => 'fi fi-rr-table-rows', 'route' => route('teacher.dashboard')],
            ['label' => 'Resources', 'icon' => 'fi fi-rr-table-rows', 'route' => route('teacher.resources.index')],
            ['label' => 'Chat', 'icon' => 'fa-solid fa-message', 'route' => route('chat.all')],
        ];
    } else {
        $menuItems = [];
    }
@endphp
@section('page_css')
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
@endsection

@section('sidebar')
    @include('components.sidebar', ['menuItems' => $menuItems])
@endsection
@section('content')
    <div class="flex flex-col h-screen">
        <!-- Header -->
        <header class="bg-gray-800 text-white p-4">
            <h1 class="text-xl">Chat</h1>
        </header>

        <div class="flex flex-1">
            <!-- Left Sidebar -->
            <div class="w-1/3 bg-gray-200 p-4 overflow-y-auto" style="max-height: 95vh;">
                <h2 class="text-lg font-semibold mb-4">Contacts</h2>
                @if (auth()->guard('teacher')->check())
                    <!-- List students for the teacher -->
                    <ul>
                        @foreach ($students as $student)
                            <li class="mb-2">
                                <a href="{{ route('chat.form', ['receiverId' => $student->id, 'receiverType' => 'student']) }}"
                                    class="block bg-white p-2 rounded shadow hover:bg-gray-300"
                                    title="{{ $student->username }}">
                                    {{ \Illuminate\Support\Str::limit($student->username, 25, '...') }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @elseif (auth()->guard('student')->check())
                    <!-- List teachers for the student -->
                    <ul>
                        @foreach ($teachers as $teacher)
                            <li class="mb-2">
                                <a href="{{ route('chat.form', ['receiverId' => $teacher->id, 'receiverType' => 'teacher']) }}"
                                    class="block bg-white p-2 rounded shadow hover:bg-gray-300"
                                    title="{{ $teacher->username }}">>
                                    {{ \Illuminate\Support\Str::limit($teacher->username, 25, '...') }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <!-- Chat Area -->
            <div class="w-2/3">
                <div class="flex flex-col h-full">
                    <div class="bg-gray-300 text-gray-800 p-4">
                        <h2 class="text-xl">
                            @if ($receiverType === 'student')
                                {{ $receiver->username }}
                            @else
                                {{ $receiver->username }}
                            @endif
                        </h2>
                    </div>
                    <div id="chatArea" class="flex-1 overflow-y-auto p-4 bg-gray-100" style="max-height: 95vh;"
                        data-auth-id="{{ auth()->guard('student')->check() ? auth()->guard('student')->id() : auth()->guard('teacher')->id() }}"
                        data-auth-type="{{ auth()->guard('student')->check() ? 'student' : 'teacher' }}">
                        @foreach ($messages as $message)
                            @if (
                                $message->sender_id ==
                                    (auth()->guard('student')->check() ? auth()->guard('student')->id() : auth()->guard('teacher')->id()) &&
                                    $message->sender_type == (auth()->guard('student')->check() ? 'student' : 'teacher'))
                                <div class="text-right">
                                    <div class="bg-blue-500 text-white rounded p-2 mb-2 inline-block"
                                        style="background-color: #ff731a;     word-wrap: break-word;
        width: 50%;">
                                        {{ $message->message }}
                                    </div>
                                </div>
                            @else
                                <div class="text-left">
                                    <div class="bg-gray-200 rounded p-2 mb-2 inline-block"
                                        style="    word-wrap: break-word;
        width: 50%;">
                                        {{ $message->message }}
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <div class="p-4 bg-white border-t">
                        <form id="chatForm" class="flex">
                            <input type="text" id="messageInput" class="flex-1 border rounded p-2"
                                placeholder="Type your message...">
                            <button type="submit" style="background-color: #ff731a"
                                class="ml-2  bg-blue-500 text-white px-4 py-2 rounded">Send</button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('page_js')
    <script>
        const chatArea = document.getElementById('chatArea');
        const chatForm = document.getElementById('chatForm');
        const messageInput = document.getElementById('messageInput');

        const authId = parseInt(chatArea.getAttribute('data-auth-id'), 10);
        const authType = chatArea.getAttribute('data-auth-type');

        // Initialize lastMessageId with the value passed from the server
        let lastMessageId = {{ $lastMessageId }};


        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const message = messageInput.value.trim();

            if (!message) return;

            const sendButton = chatForm.querySelector('button[type="submit"]');
            sendButton.disabled = true;

            fetch(`/LMS/lms_pyramakerz/public/chat/{{ $receiver->id }}/{{ $receiverType }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({
                        message: message,
                    }),
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to send message');
                    }
                    return response.json();
                })
                .then(data => {
                    const newMessage = `
                <div class="text-right">
                    <div class="bg-blue-500 text-white rounded p-2 mb-2 inline-block">${message}</div>
                </div>`;
                    chatArea.innerHTML += newMessage;

                    lastMessageId = data.message.id;

                    messageInput.value = '';
                    chatArea.scrollTop = chatArea.scrollHeight;
                })
                .catch(error => console.error('Error:', error))
                .finally(() => {
                    sendButton.disabled = false;
                });
        });

        setInterval(function() {
            fetch(
                    `/LMS/lms_pyramakerz/public/chat/{{ $receiver->id }}/{{ $receiverType }}/messages?last_message_id=${lastMessageId}`
                )
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to fetch messages');
                    }
                    return response.json();
                })
                .then(data => {
                    data.messages.forEach(message => {
                        if (message.id > lastMessageId) {
                            const isAuthUser = message.sender_id == authId && message.sender_type ===
                                authType;

                            const messageHtml = `
                        <div class="${isAuthUser ? 'text-right' : 'text-left'}">
                            <div class="${isAuthUser ? 'bg-blue-500 text-white' : 'bg-gray-200'} rounded p-2 mb-2 inline-block">
                                ${message.message}
                            </div>
                        </div>`;

                            chatArea.innerHTML += messageHtml;

                            // Update the lastMessageId
                            lastMessageId = message.id;
                        }
                    });
                    // chatArea.scrollTop = chatArea.scrollHeight;
                })
                .catch(error => console.error('Error fetching messages:', error));
        }, 2000);
    </script>
@endsection
