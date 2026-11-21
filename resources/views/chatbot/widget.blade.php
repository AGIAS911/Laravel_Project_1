<div class="chatbot-widget" data-chatbot-widget
     data-user-key="{{ auth()->id() ? 'user-'.auth()->id() : 'guest' }}"
     data-history-url="{{ url('/chatbot/conversations') }}">
    <section class="chatbot-panel" data-chatbot-panel role="dialog" aria-label="MoveIt AI assistant" hidden>
        <header class="chatbot-panel-header">
            <div>
                <h2>MoveIt Assistant</h2>
                <p>Ask us about moving services</p>
            </div>
            <div class="chatbot-panel-actions">
                <button type="button" class="chatbot-new-chat" data-chatbot-new>New chat</button>
                <button type="button" class="chatbot-close" data-chatbot-close aria-label="Close chat">×</button>
            </div>
        </header>
        <div class="chatbot-panel-messages" data-chatbot-messages role="log" aria-live="polite">
            <div class="chatbot-message chatbot-message-assistant">
                <p>Hello! How can I help you?</p>
            </div>
        </div>
        <form class="chatbot-panel-form" data-chatbot-form action="{{ route('chatbot.message') }}" method="POST">
            @csrf
            <label class="visually-hidden" for="chatbot-widget-input">Type your message</label>
            <input id="chatbot-widget-input" name="message" type="text" maxlength="2000"
                   autocomplete="off" placeholder="Type your message..." required>
            <button type="submit" data-chatbot-send aria-label="Send message">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="m22 2-7 20-4-9-9-4Z"/>
                    <path d="M22 2 11 13"/>
                </svg>
            </button>
        </form>
        <p class="chatbot-panel-status" data-chatbot-status aria-live="polite"></p>
    </section>

    <button type="button" class="chatbot-launcher" data-chatbot-open
            aria-label="Open MoveIt chatbot" aria-expanded="false" title="Chat with MoveIt">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
             stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8z"/>
            <path d="M8 12h.01M12 12h.01M16 12h.01"/>
        </svg>
    </button>
</div>
<script src="{{ asset('js/chatbot-widget.js') }}" defer></script>
