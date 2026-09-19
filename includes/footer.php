<?php
/**
 * NextGen E-Commerce Footer Component with Floating AI Drawer & Vision Modal
 */
?>
    <!-- AI Vision Image Search Upload Modal -->
    <div id="imageSearchModal" class="ai-drawer-overlay" style="z-index: 2000;">
        <div style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background: var(--bg-surface); border: 1px solid var(--border-highlight); border-radius: var(--radius-lg); padding: 2rem; width: 90%; max-width: 500px; box-shadow: var(--shadow-card);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                <h3 style="font-family: var(--font-heading); color: #fff;"><i class="fa-solid fa-camera text-neon"></i> AI Vision Image Search</h3>
                <button type="button" onclick="closeImageModal()" style="background: none; border: none; color: var(--text-muted); font-size: 1.2rem; cursor: pointer;">&times;</button>
            </div>

            <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 1.5rem;">Upload a photo of a laptop, smartphone, or gadget to find matching products in our catalog.</p>

            <form id="imageVisionForm" enctype="multipart/form-data">
                <div class="form-group mb-4">
                    <input type="file" id="visionFileInput" name="product_image" accept="image/*" class="form-control" required style="padding: 0.5rem;">
                </div>
                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center;"><i class="fa-solid fa-wand-magic-sparkles"></i> Analyze Image with AI</button>
            </form>

            <div id="visionResultsBox" style="display: none; margin-top: 1.5rem;"></div>
        </div>
    </div>

    <!-- Floating AI Shopping Assistant Launcher Button -->
    <button class="ai-floating-trigger" id="aiDrawerTrigger" title="Ask NextGen AI Assistant">
        <div class="ai-trigger-icon"><i class="fa-solid fa-robot"></i></div>
        <span class="ai-trigger-label">AI Assistant</span>
        <span class="ai-ping"></span>
    </button>

    <!-- Floating AI Shopping Assistant Drawer -->
    <div class="ai-drawer-overlay" id="aiDrawerOverlay"></div>
    <div class="ai-drawer" id="aiDrawer">
        <div class="ai-drawer-header">
            <div class="ai-title-wrap">
                <div class="ai-avatar"><i class="fa-solid fa-sparkles"></i></div>
                <div>
                    <h3>NextGen AI Assistant</h3>
                    <p>Describe your needs & get instant match scores</p>
                </div>
            </div>
            <button class="close-drawer-btn" id="closeAiDrawerBtn"><i class="fa-solid fa-xmark"></i></button>
        </div>

        <div class="ai-drawer-body">
            <!-- Sample Chips -->
            <div class="quick-chips">
                <span class="chip-label">Quick Prompts:</span>
                <button class="prompt-chip" data-prompt="I need a laptop for coding under ₹60,000 with 16GB RAM">💻 Coding Laptop</button>
                <button class="prompt-chip" data-prompt="Best noise cancelling headphones under ₹5,000 for study">🎧 ANC Headphones</button>
                <button class="prompt-chip" data-prompt="Flagship camera phone under ₹70,000">📱 Camera Phone</button>
            </div>

            <!-- Chat Output Stream -->
            <div class="ai-chat-messages" id="aiChatMessages">
                <div class="ai-msg bot-msg">
                    <div class="msg-avatar"><i class="fa-solid fa-robot"></i></div>
                    <div class="msg-bubble">
                        👋 Hi! I'm your AI Shopping Assistant. Tell me what product you're looking for, your budget, or speak using Voice Shopping!
                    </div>
                </div>
            </div>
        </div>

        <div class="ai-drawer-footer">
            <form id="aiDrawerForm" class="ai-input-form">
                <input type="text" id="aiDrawerInput" placeholder="Type e.g., Laptop for coding under 60k with battery..." autocomplete="off" required>
                <button type="submit" class="ai-send-btn"><i class="fa-solid fa-paper-plane"></i></button>
            </form>
        </div>
    </div>

    <!-- Main Footer Container -->
    <footer class="main-footer">
        <div class="container footer-grid">
            <div class="footer-col brand-col">
                <div class="brand-logo mb-3">
                    <div class="logo-icon"><i class="fa-solid fa-bolt"></i></div>
                    <div class="logo-text">
                        <span class="brand-name">NEXTGEN</span>
                        <span class="brand-tag">AI STORE</span>
                    </div>
                </div>
                <p class="footer-desc">Experience 2026 NextGen E-Commerce with smart natural language requirement analysis, real-time AI product matching, and fast UPI checkout.</p>
                <div class="social-links">
                    <a href="#"><i class="fa-brands fa-github"></i></a>
                    <a href="#"><i class="fa-brands fa-twitter"></i></a>
                    <a href="#"><i class="fa-brands fa-linkedin"></i></a>
                    <a href="#"><i class="fa-brands fa-instagram"></i></a>
                </div>
            </div>

            <div class="footer-col">
                <h4>Shop Categories</h4>
                <ul>
                    <li><a href="products.php?cat=laptops-computers">Laptops & PCs</a></li>
                    <li><a href="products.php?cat=smartphones-mobile">Smartphones & 5G</a></li>
                    <li><a href="products.php?cat=audio-headphones">Headphones & TWS</a></li>
                    <li><a href="products.php?cat=gaming-consoles">Gaming & Gear</a></li>
                    <li><a href="products.php?cat=smart-wearables">Smartwatches</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>NextGen AI Features</h4>
                <ul>
                    <li><a href="ai-assistant.php">AI Shopping Assistant</a></li>
                    <li><a href="compare.php">Comparison Studio</a></li>
                    <li><a href="ai-assistant.php">Natural Language Match</a></li>
                    <li><a href="admin/ai-analytics.php">AI Customer Analytics</a></li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Customer Support</h4>
                <ul>
                    <li><a href="orders.php">Order Tracking</a></li>
                    <li><a href="profile.php">My Account</a></li>
                    <li><a href="wishlist.php">Saved Wishlist</a></li>
                    <li><a href="cart.php">Shopping Cart</a></li>
                    <li><a href="admin/login.php" class="admin-portal-link"><i class="fa-solid fa-shield-halved"></i> Admin Portal</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom container">
            <p>&copy; <?= date('Y') ?> NEXTGEN AI E-Commerce Inc. All Rights Reserved.</p>
            <div class="payment-badges">
                <span class="pay-badge"><i class="fa-solid fa-qrcode"></i> UPI & QR</span>
                <span class="pay-badge"><i class="fa-solid fa-credit-card"></i> Credit Card</span>
                <span class="pay-badge"><i class="fa-solid fa-building-columns"></i> NetBanking</span>
                <span class="pay-badge"><i class="fa-solid fa-money-bill-wave"></i> Cash on Delivery</span>
            </div>
        </div>
    </footer>

    <!-- Global JS Assets -->
    <script src="assets/js/script.js"></script>
    <script src="assets/js/ai-assistant.js"></script>
    <script>
    // 🗣️ Voice Shopping Web Speech API Listener
    const voiceBtn = document.getElementById('voiceSearchBtn');
    if (voiceBtn) {
        voiceBtn.addEventListener('click', () => {
            const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
            if (!SpeechRecognition) {
                showToast('Voice Search is supported in Google Chrome & Edge!', 'error');
                return;
            }
            const recognition = new SpeechRecognition();
            recognition.lang = 'en-US';
            voiceBtn.style.color = '#ef4444';
            showToast('Listening... Speak your requirement!', 'info');

            recognition.onresult = (e) => {
                const transcript = e.results[0][0].transcript;
                document.getElementById('searchInput').value = transcript;
                voiceBtn.style.color = 'var(--primary)';
                showToast(`Recognized: "${transcript}"`, 'success');
                window.location.href = `products.php?q=${encodeURIComponent(transcript)}`;
            };

            recognition.onerror = () => {
                voiceBtn.style.color = 'var(--primary)';
                showToast('Voice recognition canceled or unavailable.', 'error');
            };

            recognition.start();
        });
    }

    // 📸 AI Image Vision Search Modal Logic
    const imageModal = document.getElementById('imageSearchModal');
    const imageTrigger = document.getElementById('imageSearchTrigger');

    function openImageModal() { if (imageModal) imageModal.style.display = 'block'; }
    function closeImageModal() { if (imageModal) imageModal.style.display = 'none'; }
    if (imageTrigger) imageTrigger.addEventListener('click', openImageModal);

    document.getElementById('imageVisionForm')?.addEventListener('submit', (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        const resBox = document.getElementById('visionResultsBox');
        resBox.style.display = 'block';
        resBox.innerHTML = '<div style="color:#c7d2fe; text-align:center;"><i class="fa-solid fa-spinner fa-spin"></i> AI Vision processing image attributes...</div>';

        fetch('api/ai_image_search.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            let html = `
                <div style="background: rgba(99, 102, 241, 0.15); padding: 0.75rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.85rem;">
                    🎯 <strong>Detected:</strong> ${data.vision_analysis.detected_object} (Confidence: ${data.vision_analysis.confidence_score}%)
                </div>
                <div style="font-weight: 700; color: #fff; margin-bottom: 0.5rem;">Visually Similar Products:</div>
            `;
            data.matched_products.forEach(p => {
                html += `
                    <div style="display:flex; justify-content:space-between; align-items:center; background: var(--bg-card); padding: 0.5rem; border-radius: 6px; margin-bottom: 0.5rem;">
                        <a href="${p.url}" style="font-size: 0.85rem; color: #fff; font-weight:600;">${p.name}</a>
                        <span style="color: var(--emerald); font-weight:700;">${p.price}</span>
                    </div>
                `;
            });
            resBox.innerHTML = html;
        });
    });
    </script>
</body>
</html>
