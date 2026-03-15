/**
 * Chatbot AI Engine - Frontend Script
 */

(function() {
	'use strict';

	const ChatbotAIEngine = {
		config: {
			containerId: 'chatbot-ai-engine-container',
			bubbleId: 'chatbot-ai-engine-bubble',
			windowId: 'chatbot-ai-engine-window',
			messagesId: 'chatbot-ai-engine-messages',
			inputId: 'chatbot-ai-engine-input',
			sendBtnId: 'chatbot-ai-engine-send-btn',
			closeBtnId: 'chatbot-ai-engine-close-btn',
		},

		state: { isOpen: false, isLoading: false, messages: [] },

		init: function() {
			if (typeof chatbotAIEngine === 'undefined') return;
			
			// Initialize Session ID if missing
			if (!sessionStorage.getItem('chatbot-ai-engine-session-id')) {
				sessionStorage.setItem('chatbot-ai-engine-session-id', Date.now().toString());
			}

			this.createDOM();
			this.bindEvents();
		},

		createDOM: function() {
			const container = document.createElement('div');
			container.id = this.config.containerId;
			container.className = `chatbot-ai-engine-position-${chatbotAIEngine.position || 'bottom-right'}`;

			// Handle Admin Bar positioning
			if (chatbotAIEngine.isAdminBar) {
				container.style.bottom = '52px'; // 20px default + 32px admin bar
			}

			const bubble = document.createElement('div');
			bubble.id = this.config.bubbleId;
			bubble.className = 'chatbot-ai-engine-bubble';
			bubble.setAttribute('role', 'button');
			bubble.setAttribute('tabindex', '0');
			bubble.innerHTML = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>';

			const chatWindow = document.createElement('div');
			chatWindow.id = this.config.windowId;
			chatWindow.className = 'chatbot-ai-engine-window';
			chatWindow.style.display = 'none';
			chatWindow.innerHTML = `
				<div class="chatbot-ai-engine-header">
					<h3>${chatbotAIEngine.i18n.chatTitle}</h3>
					<button id="${this.config.closeBtnId}" class="chatbot-ai-engine-close">&times;</button>
				</div>
				<div id="${this.config.messagesId}" class="chatbot-ai-engine-messages"></div>
				<div class="chatbot-ai-engine-input-wrapper">
					<input type="text" id="${this.config.inputId}" class="chatbot-ai-engine-input" placeholder="${chatbotAIEngine.i18n.placeholder}" maxlength="5000" />
					<button id="${this.config.sendBtnId}" class="chatbot-ai-engine-send-btn">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
					</button>
				</div>
			`;

			container.appendChild(bubble);
			container.appendChild(chatWindow);
			document.body.appendChild(container);
		},

		bindEvents: function() {
			const bubble = document.getElementById(this.config.bubbleId);
			const closeBtn = document.getElementById(this.config.closeBtnId);
			const sendBtn = document.getElementById(this.config.sendBtnId);
			const input = document.getElementById(this.config.inputId);

			if (bubble) bubble.addEventListener('click', () => this.toggleWindow());
			if (closeBtn) closeBtn.addEventListener('click', () => this.closeAndClear());
			if (sendBtn) sendBtn.addEventListener('click', () => this.sendMessage());
			if (input) input.addEventListener('keypress', (e) => { if (e.key === 'Enter') this.sendMessage(); });
		},

		toggleWindow: function() {
			if (this.state.isOpen) this.closeAndClear();
			else this.openWindow();
		},

		openWindow: function() {
			document.getElementById(this.config.windowId).style.display = 'flex';
			this.state.isOpen = true;
			document.getElementById(this.config.inputId).focus();

			// Auto-greeting on first open
			if (this.state.messages.length === 0 && !this.state.isLoading) {
				this.sendGreeting();
			}
		},

		closeAndClear: function() {
			if (!this.state.isOpen) return;
			
			// Show goodbye message
			this.addMessage(chatbotAIEngine.i18n.goodbye, 'bot');
			
			// Wait a bit then close and clear
			setTimeout(() => {
				document.getElementById(this.config.windowId).style.display = 'none';
				document.getElementById(this.config.messagesId).innerHTML = '';
				this.state.messages = [];
				this.state.isOpen = false;
				
				// Clear storage
				const sessionId = sessionStorage.getItem('chatbot-ai-engine-session-id');
				localStorage.removeItem(`chatbot-ai-engine-messages-${sessionId}`);
			}, 1500);
		},

		sendGreeting: function() {
			this.state.isLoading = true;
			this.showLoading();

			const formData = new FormData();
			formData.append('action', 'chatbot_send_message');
			formData.append('message', 'INIT_GREETING');
			formData.append('history', JSON.stringify([]));
			formData.append('nonce', chatbotAIEngine.nonce);

			fetch(chatbotAIEngine.ajaxUrl, { method: 'POST', body: formData })
				.then(r => r.json())
				.then(d => {
					this.removeLoading();
					this.state.isLoading = false;
					if (d.success) this.addMessage(d.data.message, 'bot');
				})
				.catch(() => {
					this.removeLoading();
					this.state.isLoading = false;
				});
		},

		sendMessage: function() {
			const input = document.getElementById(this.config.inputId);
			const msg = input.value.trim();
			if (!msg || this.state.isLoading) return;

			const history = this.state.messages.slice(-6).map(m => ({
				role: m.sender === 'user' ? 'user' : 'assistant',
				content: m.text.replace(/<[^>]*>?/gm, '')
			}));

			this.addMessage(msg, 'user');
			input.value = '';
			this.state.isLoading = true;
			this.showLoading();

			const formData = new FormData();
			formData.append('action', 'chatbot_send_message');
			formData.append('message', msg);
			formData.append('history', JSON.stringify(history));
			formData.append('nonce', chatbotAIEngine.nonce);

			fetch(chatbotAIEngine.ajaxUrl, { method: 'POST', body: formData })
				.then(r => r.json())
				.then(d => {
					this.removeLoading();
					this.state.isLoading = false;
					if (d.success) this.addMessage(d.data.message, 'bot');
					else this.addMessage(chatbotAIEngine.i18n.error, 'error');
				})
				.catch(() => {
					this.removeLoading();
					this.state.isLoading = false;
					this.addMessage(chatbotAIEngine.i18n.error, 'error');
				});
		},

		addMessage: function(text, sender) {
			const container = document.getElementById(this.config.messagesId);
			if (!container) return;

			const div = document.createElement('div');
			div.className = `chatbot-ai-engine-message chatbot-ai-engine-message-${sender}`;
			
			const bubble = document.createElement('div');
			bubble.className = 'chatbot-ai-engine-message-bubble';
			
			if (sender === 'user') bubble.textContent = text;
			else bubble.innerHTML = text;

			div.appendChild(bubble);
			container.appendChild(div);
			container.scrollTop = container.scrollHeight;

			if (sender !== 'error' && sender !== 'loading') {
				this.state.messages.push({ text, sender });
				this.saveMessages();
			}
		},

		showLoading: function() {
			const container = document.getElementById(this.config.messagesId);
			const div = document.createElement('div');
			div.id = 'chatbot-ai-engine-loading';
			div.className = 'chatbot-ai-engine-message chatbot-ai-engine-message-bot';
			div.innerHTML = '<div class="chatbot-ai-engine-message-bubble chatbot-ai-engine-loading-bubble"><span></span><span></span><span></span></div>';
			container.appendChild(div);
			container.scrollTop = container.scrollHeight;
		},

		removeLoading: function() {
			const el = document.getElementById('chatbot-ai-engine-loading');
			if (el) el.remove();
		},

		saveMessages: function() {
			const sessionId = sessionStorage.getItem('chatbot-ai-engine-session-id');
			if (sessionId) {
				localStorage.setItem(`chatbot-ai-engine-messages-${sessionId}`, JSON.stringify(this.state.messages));
			}
		}
	};

	document.addEventListener('DOMContentLoaded', () => ChatbotAIEngine.init());
})();
