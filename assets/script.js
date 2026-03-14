/**
 * Chatbot AI Engine - Frontend Script
 * Manages chat UI, AJAX communication, and message handling
 */

(function() {
	'use strict';

	const ChatbotAIEngine = {
		/**
		 * Configuration
		 */
		config: {
			containerId: 'chatbot-ai-engine-container',
			bubbleId: 'chatbot-ai-engine-bubble',
			windowId: 'chatbot-ai-engine-window',
			messagesId: 'chatbot-ai-engine-messages',
			inputId: 'chatbot-ai-engine-input',
			sendBtnId: 'chatbot-ai-engine-send-btn',
			closeBtnId: 'chatbot-ai-engine-close-btn',
		},

		/**
		 * State
		 */
		state: {
			isOpen: false,
			isLoading: false,
			messages: [],
		},

		/**
		 * Initialize chatbot
		 */
		init: function() {
			if (typeof chatbotAIEngine === 'undefined') {
				console.error('Chatbot AI Engine: Global object not found');
				return;
			}

			this.createDOM();
			this.bindEvents();
			this.loadMessages();
		},

		/**
		 * Create DOM structure
		 */
		createDOM: function() {
			const container = document.createElement('div');
			container.id = this.config.containerId;
			container.className = `chatbot-ai-engine-position-${chatbotAIEngine.position || 'bottom-right'}`;

			// Floating bubble
			const bubble = document.createElement('div');
			bubble.id = this.config.bubbleId;
			bubble.className = 'chatbot-ai-engine-bubble';
			bubble.setAttribute('role', 'button');
			bubble.setAttribute('tabindex', '0');
			bubble.setAttribute('aria-label', chatbotAIEngine.i18n.chatTitle || 'AI Assistant');
			bubble.innerHTML = `
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
				</svg>
			`;

			// Chat window
			const chatWindow = document.createElement('div');
			chatWindow.id = this.config.windowId;
			chatWindow.className = 'chatbot-ai-engine-window';
			chatWindow.setAttribute('role', 'dialog');
			chatWindow.setAttribute('aria-labelledby', 'chatbot-ai-engine-title');
			chatWindow.style.display = 'none';
			chatWindow.innerHTML = `
				<div class="chatbot-ai-engine-header">
					<h3 id="chatbot-ai-engine-title">${chatbotAIEngine.i18n.chatTitle || 'AI Assistant'}</h3>
					<button id="${this.config.closeBtnId}" class="chatbot-ai-engine-close" aria-label="${chatbotAIEngine.i18n.closeChat || 'Close chat'}">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<line x1="18" y1="6" x2="6" y2="18"></line>
							<line x1="6" y1="6" x2="18" y2="18"></line>
						</svg>
					</button>
				</div>
				<div id="${this.config.messagesId}" class="chatbot-ai-engine-messages"></div>
				<div class="chatbot-ai-engine-input-wrapper">
					<input
						type="text"
						id="${this.config.inputId}"
						class="chatbot-ai-engine-input"
						placeholder="${chatbotAIEngine.i18n.placeholder || 'Type your message...'}"
						maxlength="5000"
						aria-label="Message input"
					/>
					<button id="${this.config.sendBtnId}" class="chatbot-ai-engine-send-btn" aria-label="${chatbotAIEngine.i18n.send || 'Send'}">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<line x1="22" y1="2" x2="11" y2="13"></line>
							<polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
						</svg>
					</button>
				</div>
			`;

			container.appendChild(bubble);
			container.appendChild(chatWindow);
			document.body.appendChild(container);
		},

		/**
		 * Bind event listeners
		 */
		bindEvents: function() {
			const bubble = document.getElementById(this.config.bubbleId);
			const closeBtn = document.getElementById(this.config.closeBtnId);
			const sendBtn = document.getElementById(this.config.sendBtnId);
			const input = document.getElementById(this.config.inputId);

			if (bubble) {
				bubble.addEventListener('click', () => this.toggleWindow());
				bubble.addEventListener('keypress', (e) => {
					if (e.key === 'Enter' || e.key === ' ') {
						e.preventDefault();
						this.toggleWindow();
					}
				});
			}

			if (closeBtn) {
				closeBtn.addEventListener('click', () => this.closeWindow());
			}

			if (sendBtn) {
				sendBtn.addEventListener('click', () => this.sendMessage());
			}

			if (input) {
				input.addEventListener('keypress', (e) => {
					if (e.key === 'Enter' && !e.shiftKey) {
						e.preventDefault();
						this.sendMessage();
					}
				});
			}

			// Close on Escape key
			document.addEventListener('keydown', (e) => {
				if (e.key === 'Escape' && this.state.isOpen) {
					this.closeWindow();
				}
			});
		},

		/**
		 * Toggle chat window
		 */
		toggleWindow: function() {
			if (this.state.isOpen) {
				this.closeWindow();
			} else {
				this.openWindow();
			}
		},

		/**
		 * Open chat window
		 */
		openWindow: function() {
			const chatWindow = document.getElementById(this.config.windowId);
			const input = document.getElementById(this.config.inputId);

			if (chatWindow) {
				chatWindow.style.display = 'flex';
				this.state.isOpen = true;

				// Focus input after animation
				setTimeout(() => {
					if (input) {
						input.focus();
					}
				}, 300);
			}
		},

		/**
		 * Close chat window
		 */
		closeWindow: function() {
			const chatWindow = document.getElementById(this.config.windowId);

			if (chatWindow) {
				chatWindow.style.display = 'none';
				this.state.isOpen = false;
			}
		},

		/**
		 * Send message
		 */
		sendMessage: function() {
			const input = document.getElementById(this.config.inputId);
			const message = input ? input.value.trim() : '';

			if (!message || this.state.isLoading) {
				return;
			}

			// Add user message to UI
			this.addMessage(message, 'user');

			// Clear input
			if (input) {
				input.value = '';
				input.focus();
			}

			// Send to server
			this.sendToServer(message);
		},

		/**
		 * Send message to server via AJAX
		 */
		sendToServer: function(message) {
			if (typeof chatbotAIEngine === 'undefined' || !chatbotAIEngine.ajaxUrl || !chatbotAIEngine.nonce) {
				this.addMessage(chatbotAIEngine.i18n.error, 'error');
				return;
			}

			this.state.isLoading = true;
			this.showLoadingIndicator();

			const formData = new FormData();
			formData.append('action', 'chatbot_send_message');
			formData.append('message', message);
			formData.append('nonce', chatbotAIEngine.nonce);

			fetch(chatbotAIEngine.ajaxUrl, {
				method: 'POST',
				body: formData,
			})
			.then(response => response.json())
			.then(data => {
				this.removeLoadingIndicator();
				this.state.isLoading = false;

				if (data.success && data.data.message) {
					this.addMessage(data.data.message, 'bot');
				} else {
					const errorMsg = data.data ? data.data.message : chatbotAIEngine.i18n.error;
					this.addMessage(errorMsg, 'error');
				}
			})
			.catch(error => {
				console.error('Chatbot AI Engine Error:', error);
				this.removeLoadingIndicator();
				this.state.isLoading = false;
				this.addMessage(chatbotAIEngine.i18n.error, 'error');
			});
		},

		/**
		 * Add message to chat
		 */
		addMessage: function(text, sender) {
			const messagesContainer = document.getElementById(this.config.messagesId);

			if (!messagesContainer) {
				return;
			}

			const messageDiv = document.createElement('div');
			messageDiv.className = `chatbot-ai-engine-message chatbot-ai-engine-message-${sender}`;
			messageDiv.setAttribute('role', 'article');

			const bubble = document.createElement('div');
			bubble.className = 'chatbot-ai-engine-message-bubble';
			bubble.innerHTML = this.escapeHtml(text);

			messageDiv.appendChild(bubble);
			messagesContainer.appendChild(messageDiv);

			// Scroll to bottom
			this.scrollToBottom();

			// Save to state
			this.state.messages.push({
				text: text,
				sender: sender,
				timestamp: new Date().getTime(),
			});

			this.saveMessages();
		},

		/**
		 * Show loading indicator
		 */
		showLoadingIndicator: function() {
			const messagesContainer = document.getElementById(this.config.messagesId);

			if (!messagesContainer) {
				return;
			}

			const loadingDiv = document.createElement('div');
			loadingDiv.id = 'chatbot-ai-engine-loading';
			loadingDiv.className = 'chatbot-ai-engine-message chatbot-ai-engine-message-bot';

			const bubble = document.createElement('div');
			bubble.className = 'chatbot-ai-engine-message-bubble chatbot-ai-engine-loading-bubble';
			bubble.innerHTML = '<span></span><span></span><span></span>';

			loadingDiv.appendChild(bubble);
			messagesContainer.appendChild(loadingDiv);

			this.scrollToBottom();
		},

		/**
		 * Remove loading indicator
		 */
		removeLoadingIndicator: function() {
			const loading = document.getElementById('chatbot-ai-engine-loading');

			if (loading) {
				loading.remove();
			}
		},

		/**
		 * Scroll to bottom of messages
		 */
		scrollToBottom: function() {
			const messagesContainer = document.getElementById(this.config.messagesId);

			if (messagesContainer) {
				setTimeout(() => {
					messagesContainer.scrollTop = messagesContainer.scrollHeight;
				}, 0);
			}
		},

		/**
		 * Save messages to localStorage
		 */
		saveMessages: function() {
			try {
				const sessionId = this.getSessionId();
				localStorage.setItem(
					`chatbot-ai-engine-messages-${sessionId}`,
					JSON.stringify(this.state.messages)
				);
			} catch (e) {
				console.warn('Could not save messages to localStorage', e);
			}
		},

		/**
		 * Load messages from localStorage
		 */
		loadMessages: function() {
			try {
				const sessionId = this.getSessionId();
				const saved = localStorage.getItem(`chatbot-ai-engine-messages-${sessionId}`);

				if (saved) {
					this.state.messages = JSON.parse(saved);

					// Display saved messages (only show last 10 to avoid clutter)
					const messagesToShow = this.state.messages.slice(-10);
					messagesToShow.forEach(msg => {
						this.addMessage(msg.text, msg.sender);
					});
				}
			} catch (e) {
				console.warn('Could not load messages from localStorage', e);
			}
		},

		/**
		 * Get session ID
		 */
		getSessionId: function() {
			const key = 'chatbot-ai-engine-session-id';

			let sessionId = sessionStorage.getItem(key);

			if (!sessionId) {
				sessionId = 'session-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
				sessionStorage.setItem(key, sessionId);
			}

			return sessionId;
		},

		/**
		 * Escape HTML to prevent XSS
		 */
		escapeHtml: function(text) {
			const map = {
				'&': '&amp;',
				'<': '&lt;',
				'>': '&gt;',
				'"': '&quot;',
				"'": '&#039;',
			};

			return text.replace(/[&<>"']/g, m => map[m]);
		},
	};

	/**
	 * Initialize when DOM is ready
	 */
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', () => {
			ChatbotAIEngine.init();
		});
	} else {
		ChatbotAIEngine.init();
	}
})();
