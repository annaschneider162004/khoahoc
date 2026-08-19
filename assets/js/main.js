document.addEventListener('DOMContentLoaded', () => {
    const badge = document.getElementById('notificationBadge');
    const list = document.getElementById('notificationList');

    const fetchNotifications = async () => {
        if (!badge && !list) {
            return;
        }

        try {
            const response = await fetch('/notifications.php?ajax=1', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (!response.ok) {
                return;
            }
            const data = await response.json();
            if (badge) {
                badge.textContent = data.unread_count;
            }
            if (list) {
                list.innerHTML = data.items.map((item) => `
                    <li class="rounded-2xl border border-slate-100 p-4">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-semibold text-slate-800">${item.title}</p>
                                <p class="mt-1 text-sm text-slate-500">${item.message}</p>
                                <p class="mt-2 text-xs text-slate-400">${item.created_at}</p>
                            </div>
                            ${item.is_read ? '' : '<span class="h-3 w-3 rounded-full bg-primary"></span>'}
                        </div>
                    </li>
                `).join('');
            }
        } catch (error) {
            console.error(error);
        }
    };

    fetchNotifications();
    setInterval(fetchNotifications, 5000);

    document.querySelectorAll('[data-chat-poll]').forEach((element) => {
        const receiverId = element.getAttribute('data-chat-receiver');
        const container = document.getElementById('chatMessages');
        if (!receiverId || !container) {
            return;
        }

        const pollChat = async () => {
            try {
                const response = await fetch(`/student/chat.php?ajax=1&receiver_id=${receiverId}`);
                if (!response.ok) {
                    return;
                }
                const data = await response.json();
                container.innerHTML = data.messages.map((message) => {
                    const mine = Number(message.sender_id) === Number(data.current_user_id);
                    return `
                        <div class="flex ${mine ? 'justify-end' : 'justify-start'}">
                            <div class="max-w-xl rounded-2xl px-4 py-3 text-sm shadow ${mine ? 'bg-primary text-white' : 'bg-white text-slate-700'}">
                                <p>${message.message}</p>
                                <p class="mt-2 text-[11px] ${mine ? 'text-violet-100' : 'text-slate-400'}">${message.created_at}</p>
                            </div>
                        </div>
                    `;
                }).join('');
                container.scrollTop = container.scrollHeight;
            } catch (error) {
                console.error(error);
            }
        };

        pollChat();
        setInterval(pollChat, 3000);
    });
});
