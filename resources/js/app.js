import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
	const root = document.getElementById('ai-chatbot');
	if (!root) {
		return;
	}

	const toggleBtn = root.querySelector('.chatbot__toggle');
	const panel = root.querySelector('.chatbot__panel');
	const closeBtn = root.querySelector('.chatbot__close');
	const form = root.querySelector('[data-element="form"]');
	const input = root.querySelector('.chatbot__input');
	const messages = root.querySelector('[data-element="messages"]');
	const typing = root.querySelector('[data-element="typing"]');
	const endpoint = root.dataset.endpoint;

	const scrollToBottom = () => {
		requestAnimationFrame(() => {
			messages.scrollTop = messages.scrollHeight;
		});
	};

	const createMessage = (role, html) => {
		const wrap = document.createElement('div');
		wrap.className = role === 'user' ? 'chatbot__message chatbot__message--user' : 'chatbot__message chatbot__message--assistant';

		const avatar = document.createElement('div');
		avatar.className = 'chatbot__avatar';
		avatar.textContent = role === 'user' ? '🙋' : '🤖';

		const bubble = document.createElement('div');
		bubble.className = 'chatbot__bubble';
		bubble.innerHTML = html;

		wrap.appendChild(avatar);
		wrap.appendChild(bubble);
		return wrap;
	};

	const renderReply = (data) => {
		if (data.type === 'movie_search' && Array.isArray(data.movies) && data.movies.length) {
			const list = data.movies.map((movie) => {
				const details = [movie.ten_phim, movie.the_loai, movie.quoc_gia]
					.filter(Boolean)
					.join(' • ');
				return `<li>${details}</li>`;
			}).join('');
			return `<p>Mình tìm được những phim sau:</p><ul>${list}</ul>`;
		}

		if (data.type === 'showtime_search' && Array.isArray(data.showtimes) && data.showtimes.length) {
			const list = data.showtimes.map((item) => {
				const movieName = item?.phim?.ten_phim ? `${item.phim.ten_phim} — ` : '';
				return `<li>${movieName}${item.thoi_gian_bat_dau}</li>`;
			}).join('');
			return `<p>Các suất chiếu phù hợp:</p><ul>${list}</ul>`;
		}

		if (data.type === 'booking_link' && data.link) {
			return `<p>Bạn có thể đặt vé tại đây: <a href="${data.link}" class="chatbot__link">${data.link}</a></p>`;
		}

		if (data.type === 'training' && data.response) {
			return `<p>${data.response}</p>`;
		}

		if (data.response) {
			return `<p>${data.response}</p>`;
		}

		return '<p>Xin lỗi, hiện mình chưa tìm được thông tin phù hợp.</p>';
	};

	const appendMessage = (role, html) => {
		messages.appendChild(createMessage(role, html));
		scrollToBottom();
	};

	const setTyping = (visible) => {
		typing.hidden = !visible;
	};

	const openPanel = () => {
		root.classList.add('chatbot--open');
		panel.setAttribute('aria-hidden', 'false');
		input.focus({ preventScroll: true });
	};

	const closePanel = () => {
		root.classList.remove('chatbot--open');
		panel.setAttribute('aria-hidden', 'true');
	};

	toggleBtn.addEventListener('click', () => {
		if (root.classList.contains('chatbot--open')) {
			closePanel();
			return;
		}
		openPanel();
	});

	closeBtn.addEventListener('click', closePanel);

	form.addEventListener('submit', async (event) => {
		event.preventDefault();
		const text = input.value.trim();
		if (!text) {
			return;
		}

		appendMessage('user', `<p>${text}</p>`);
		input.value = '';
		setTyping(true);

		try {
			const response = await fetch(endpoint, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'Accept': 'application/json',
					'X-Requested-With': 'XMLHttpRequest',
				},
				body: JSON.stringify({ message: text }),
			});

			if (!response.ok) {
				throw new Error('Network response was not ok');
			}

			const data = await response.json();
			appendMessage('assistant', renderReply(data));
		} catch (error) {
			appendMessage('assistant', '<p>Xin lỗi, hệ thống đang bận. Vui lòng thử lại sau ít phút.</p>');
			console.error('Chatbot error:', error);
		} finally {
			setTyping(false);
		}
	});
});
