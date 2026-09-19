/**
 * NextGen AI Assistant & Recommender Drawer Handler
 */

document.addEventListener('DOMContentLoaded', () => {

    const drawer = document.getElementById('aiDrawer');
    const overlay = document.getElementById('aiDrawerOverlay');
    const trigger = document.getElementById('aiDrawerTrigger');
    const closeBtn = document.getElementById('closeAiDrawerBtn');

    const form = document.getElementById('aiDrawerForm');
    const input = document.getElementById('aiDrawerInput');
    const messages = document.getElementById('aiChatMessages');

    function openDrawer() {
        if (drawer && overlay) {
            drawer.classList.add('open');
            overlay.style.display = 'block';
            if (input) input.focus();
        }
    }

    function closeDrawer() {
        if (drawer && overlay) {
            drawer.classList.remove('open');
            overlay.style.display = 'none';
        }
    }

    if (trigger) trigger.addEventListener('click', openDrawer);
    if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
    if (overlay) overlay.addEventListener('click', closeDrawer);

    // Prompt Chips Handler
    document.querySelectorAll('.prompt-chip').forEach(chip => {
        chip.addEventListener('click', () => {
            const promptText = chip.getAttribute('data-prompt');
            if (input) {
                input.value = promptText;
                sendAiQuery(promptText);
            }
        });
    });

    if (form) {
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const text = input.value.trim();
            if (text) {
                sendAiQuery(text);
                input.value = '';
            }
        });
    }

    function sendAiQuery(userPrompt) {
        openDrawer();

        // Append User Message
        const userDiv = document.createElement('div');
        userDiv.className = 'ai-msg user-msg';
        userDiv.innerHTML = `<div class="msg-bubble">${escapeHtml(userPrompt)}</div>`;
        messages.appendChild(userDiv);

        // Append Bot Thinking Message
        const botDiv = document.createElement('div');
        botDiv.className = 'ai-msg bot-msg';
        botDiv.innerHTML = `
            <div class="msg-avatar"><i class="fa-solid fa-robot"></i></div>
            <div class="msg-bubble">
                <i class="fa-solid fa-spinner fa-spin"></i> Analyzing your requirements & scanning catalog...
            </div>
        `;
        messages.appendChild(botDiv);
        scrollToBottom();

        // Fetch AI Endpoint
        fetch('api/ai_chat.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ prompt: userPrompt })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success' && data.recommendations.length > 0) {
                let html = `
                    <div style="margin-bottom: 0.5rem;">
                        🎯 <strong>Analyzed Requirements:</strong><br>
                        ${data.requirement.budget ? `• Budget: <strong>${data.requirement.budget ? '₹' + data.requirement.budget.toLocaleString() : 'Any'}</strong><br>` : ''}
                        ${data.requirement.purposes.length ? `• Purpose: ${data.requirement.purposes.join(', ')}<br>` : ''}
                        ${data.requirement.features.length ? `• Priority: ${data.requirement.features.join(', ')}` : ''}
                    </div>
                    <div style="font-weight: 700; color: #6366f1; margin: 0.75rem 0 0.5rem 0;">Top AI Matches:</div>
                `;

                data.recommendations.forEach(item => {
                    const reasonsList = item.ai_reasons.slice(0, 3).map(r => `<div style="font-size: 0.75rem; color: #34d399;">${r}</div>`).join('');
                    html += `
                        <div style="background: rgba(9, 13, 22, 0.8); border: 1px solid rgba(99, 102, 241, 0.3); border-radius: 10px; padding: 0.75rem; margin-bottom: 0.75rem; display: flex; gap: 0.75rem; align-items: center;">
                            <img src="${item.image_url}" style="width: 55px; height: 55px; object-fit: cover; border-radius: 8px;">
                            <div style="flex: 1;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <a href="${item.url}" style="font-weight: 600; font-size: 0.85rem; color: #fff;">${item.name}</a>
                                    <span style="background: #10b981; color: #fff; font-size: 0.7rem; padding: 0.15rem 0.5rem; border-radius: 99px; font-weight: 700;">${item.ai_score}% Match</span>
                                </div>
                                <div style="font-weight: 700; color: #c7d2fe; font-size: 0.85rem; margin: 0.2rem 0;">${item.price}</div>
                                ${reasonsList}
                            </div>
                        </div>
                    `;
                });

                botDiv.querySelector('.msg-bubble').innerHTML = html;
            } else {
                botDiv.querySelector('.msg-bubble').innerHTML = `
                    No exact products found matching your prompt. Try searching for "laptops", "headphones", "smartphones" or adjusting your budget filter!
                `;
            }
            scrollToBottom();
        })
        .catch(() => {
            botDiv.querySelector('.msg-bubble').innerHTML = 'Sorry, there was an issue processing your requirement. Please try again.';
            scrollToBottom();
        });
    }

    function scrollToBottom() {
        if (messages) messages.scrollTop = messages.scrollHeight;
    }

    function escapeHtml(text) {
        return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
    }

});
