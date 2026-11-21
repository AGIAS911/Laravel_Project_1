(() => {
    const widget = document.querySelector('[data-chatbot-widget]');
    if (!widget) return;

    const panel = widget.querySelector('[data-chatbot-panel]');
    const openButton = widget.querySelector('[data-chatbot-open]');
    const closeButton = widget.querySelector('[data-chatbot-close]');
    const newChatButton = widget.querySelector('[data-chatbot-new]');
    const messages = widget.querySelector('[data-chatbot-messages]');
    const form = widget.querySelector('[data-chatbot-form]');
    const input = form.elements.message;
    const sendButton = widget.querySelector('[data-chatbot-send]');
    const status = widget.querySelector('[data-chatbot-status]');
    const storageKey = `moveit-chat-${widget.dataset.userKey}`;
    const query = new URLSearchParams(location.search);
    if (query.get('new') === '1') localStorage.removeItem(storageKey);
    let conversationId = query.get('conversation_id') || (query.get('new') === '1' ? '' : localStorage.getItem(storageKey)) || '';
    let loadedConversationId = '';

    if (conversationId) localStorage.setItem(storageKey, conversationId);

    function showMessage(content, role, error = false) {
        const row = document.createElement('div');
        row.className = `chatbot-message chatbot-message-${role}${error ? ' chatbot-message-error' : ''}`;
        const bubble = document.createElement('p');
        bubble.textContent = content;
        row.appendChild(bubble);
        messages.appendChild(row);
        messages.scrollTop = messages.scrollHeight;
        return row;
    }

    function showGreeting() {
        messages.replaceChildren();
        showMessage('Hello! How can I help you?', 'assistant');
    }

    async function loadHistory() {
        if (!conversationId || loadedConversationId === conversationId) return;
        const id = conversationId;
        status.textContent = 'Loading conversation...';
        try {
            const response = await fetch(`${widget.dataset.historyUrl}/${encodeURIComponent(id)}`, {
                headers: { Accept: 'application/json' },
            });
            if (conversationId !== id) return;
            if (response.status === 404) {
                conversationId = '';
                localStorage.removeItem(storageKey);
                showGreeting();
                return;
            }
            const data = await response.json();
            if (!response.ok) throw new Error(data.message || 'Could not load this conversation.');
            if (conversationId !== id) return;
            messages.replaceChildren();
            data.messages.forEach((message) => showMessage(message.content, message.role));
            if (!data.messages.length) showGreeting();
            loadedConversationId = id;
        } catch (error) {
            if (conversationId === id) status.textContent = error.message || 'Could not load this conversation.';
        } finally {
            if (status.textContent === 'Loading conversation...') status.textContent = '';
        }
    }

    function setOpen(open) {
        panel.hidden = !open;
        openButton.setAttribute('aria-expanded', String(open));
        if (open) {
            loadHistory();
            input.focus();
        } else {
            openButton.focus();
        }
    }

    openButton.addEventListener('click', () => setOpen(panel.hidden));
    closeButton.addEventListener('click', () => setOpen(false));
    newChatButton.addEventListener('click', () => {
        conversationId = '';
        loadedConversationId = '';
        localStorage.removeItem(storageKey);
        showGreeting();
        status.textContent = '';
        input.focus();
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const message = input.value.trim();
        if (!message) return;

        showMessage(message, 'user');
        input.value = '';
        input.disabled = true;
        sendButton.disabled = newChatButton.disabled = true;
        status.textContent = 'Generating response...';
        const waiting = showMessage('...', 'assistant');

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                },
                body: JSON.stringify({ message, conversation_id: conversationId || null }),
            });
            const data = await response.json();
            if (!response.ok || !data.success) {
                throw new Error(data.errors?.message?.[0] || data.message || 'Unable to get a response.');
            }
            waiting.remove();
            showMessage(data.reply, 'assistant');
            conversationId = String(data.conversation_id);
            loadedConversationId = conversationId;
            localStorage.setItem(storageKey, conversationId);
            status.textContent = '';
        } catch (error) {
            waiting.remove();
            showMessage(error.message || 'Unable to connect to the assistant.', 'assistant', true);
            status.textContent = '';
        } finally {
            input.disabled = false;
            sendButton.disabled = newChatButton.disabled = false;
            input.focus();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !panel.hidden) setOpen(false);
    });

    if (query.get('chat') === 'open') {
        setOpen(true);
        query.delete('chat');
        query.delete('conversation_id');
        query.delete('new');
        const remainingQuery = query.toString();
        history.replaceState({}, '', `${location.pathname}${remainingQuery ? `?${remainingQuery}` : ''}${location.hash}`);
    }
})();
