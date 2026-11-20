@extends('layouts.app')

@section('content')
<style>
    .chatbot-shell {
        max-width: 1100px;
    }

    .chatbot-messages {
        height: min(65vh, 600px);
        min-height: 300px;
        overflow-y: auto;
        background: #f8f9fa;
    }

    .chat-message {
        display: flex;
        margin-bottom: 1rem;
    }

    .chat-message-user {
        justify-content: flex-end;
    }

    .chat-bubble {
        max-width: min(80%, 620px);
        padding: .75rem 1rem;
        border-radius: 1rem;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
    }

    .chat-message-assistant .chat-bubble {
        background: #fff;
        border: 1px solid #dee2e6;
    }

    .chat-message-user .chat-bubble {
        color: #fff;
        background: #0d6efd;
    }

    .chat-message-error .chat-bubble {
        color: #842029;
        background: #f8d7da;
    }
</style>

<div class="container chatbot-shell">
    <div class="row g-3">
        <aside class="col-12 col-md-4 col-lg-3">
            <div class="card h-100 shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h2 class="h6 mb-0">My Chats</h2>
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('chatbot.index', ['new' => 1]) }}">
                        New Chat
                    </a>
                </div>
                <div class="list-group list-group-flush" id="conversation-list">
                    @forelse ($conversations as $item)
                        <a
                            class="list-group-item list-group-item-action {{ $conversation?->id === $item->id ? 'active' : '' }}"
                            href="{{ route('chatbot.index', ['conversation_id' => $item->id]) }}"
                            data-conversation-id="{{ $item->id }}"
                        >
                            {{ $item->title }}
                        </a>
                    @empty
                        <span class="list-group-item text-muted" id="no-conversations">
                            No previous conversations.
                        </span>
                    @endforelse
                </div>
            </div>
        </aside>

        <section class="col-12 col-md-8 col-lg-9">
            <div class="card shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h1 class="h5 mb-1">AI Assistant</h1>
                        <p class="small text-muted mb-0">Ask your question and I'll do my best to help.</p>
                    </div>
                    <span class="badge text-bg-success">Online</span></span>
                </div>

                <div id="chat-messages" class="card-body chatbot-messages p-3" role="log" aria-live="polite">
                    @forelse ($messages as $chatMessage)
                        <div class="chat-message chat-message-{{ $chatMessage->role }}">
                            <div class="chat-bubble">{{ $chatMessage->content }}</div>
                        </div>
                    @empty
                        <div class="chat-message chat-message-assistant">
                            <div class="chat-bubble">Hello! How can I help you?</div>
                        </div>
                    @endforelse
                </div>

                <div class="card-footer">
                    <form
                        id="chat-form"
                        action="{{ route('chatbot.message') }}"
                        method="POST"
                        data-conversation-id="{{ $conversation?->id ?? '' }}"
                    >
                        @csrf
                        <div class="input-group">
                            <label class="visually-hidden" for="chat-message">Type your message</label>
                            <input
                                id="chat-message"
                                name="message"
                                class="form-control"
                                type="text"
                                maxlength="2000"
                                autocomplete="off"
                                placeholder="Type your message here..."
                                required
                            >
                            <button id="chat-send" class="btn btn-primary" type="submit">Send</button>
                        </div>
                        <div id="chat-status" class="small text-muted mt-2" aria-live="polite"></div>
                    </form>
                </div>
            </div>
        </section>
    </div>
</div>

<script>
    (() => {
        const form = document.getElementById('chat-form');
        const input = document.getElementById('chat-message');
        const messages = document.getElementById('chat-messages');
        const sendButton = document.getElementById('chat-send');
        const status = document.getElementById('chat-status');
        const conversationList = document.getElementById('conversation-list');
        const csrfToken = form.querySelector('input[name="_token"]').value;
        let conversationId = form.dataset.conversationId || '';

        function addMessage(content, role, isError = false) {
            const row = document.createElement('div');
            row.className = `chat-message chat-message-${role}${isError ? ' chat-message-error' : ''}`;

            const bubble = document.createElement('div');
            bubble.className = 'chat-bubble';
            bubble.textContent = content;
            row.appendChild(bubble);
            messages.appendChild(row);
            messages.scrollTop = messages.scrollHeight;

            return row;
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const message = input.value.trim();
            if (!message) {
                return;
            }

            addMessage(message, 'user');
            input.value = '';
            input.disabled = true;
            sendButton.disabled = true;
            status.textContent = 'Generating response...';
            const waitingMessage = addMessage('...', 'assistant');

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({
                        message,
                        conversation_id: conversationId || null,
                    }),
                });
                const data = await response.json();

                if (!response.ok || !data.success) {
                    const validationMessage = data.errors?.message?.[0];
                    throw new Error(validationMessage || data.message || 'Unable to get a response.');
                }

                waitingMessage.remove();
                addMessage(data.reply, 'assistant');
                conversationId = String(data.conversation_id);
                form.dataset.conversationId = conversationId;

                const url = new URL(window.location.href);
                url.searchParams.set('conversation_id', conversationId);
                url.searchParams.delete('new');
                window.history.replaceState({}, '', url);

                conversationList.querySelector('#no-conversations')?.remove();
                conversationList.querySelectorAll('[data-conversation-id]').forEach((link) => {
                    link.classList.toggle('active', link.dataset.conversationId === conversationId);
                });

                if (!conversationList.querySelector(`[data-conversation-id="${conversationId}"]`)) {
                    const link = document.createElement('a');
                    link.className = 'list-group-item list-group-item-action active';
                    link.href = url.toString();
                    link.dataset.conversationId = conversationId;
                    link.textContent = data.title;
                    conversationList.prepend(link);
                }
                status.textContent = '';
            } catch (error) {
                waitingMessage.remove();
                addMessage(error.message || 'An error occurred while connecting to the assistant.', 'assistant', true);
                status.textContent = '';
            } finally {
                input.disabled = false;
                sendButton.disabled = false;
                input.focus();
            }
        });
    })();
</script>
@endsection