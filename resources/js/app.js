import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

const setupPageTransitions = () => {
	const bodyElement = document.body;
	const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	if (prefersReducedMotion) {
		bodyElement.classList.add('page-ready');
		return;
	}

	bodyElement.classList.add('page-boot');

	const transitionLayer = document.createElement('div');
	transitionLayer.className = 'page-transition-layer';
	bodyElement.appendChild(transitionLayer);

	requestAnimationFrame(() => {
		requestAnimationFrame(() => {
			bodyElement.classList.add('page-ready');
		});
	});

	let isNavigating = false;

	const isInternalNavigation = (link, event) => {
		if (isNavigating || event.defaultPrevented || event.button !== 0) {
			return false;
		}

		if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
			return false;
		}

		if (link.target && link.target !== '_self') {
			return false;
		}

		if (link.hasAttribute('download') || link.dataset.noTransition !== undefined) {
			return false;
		}

		const href = link.getAttribute('href') || '';
		if (href.startsWith('#')) {
			return false;
		}

		const destination = new URL(link.href, window.location.href);
		if (destination.origin !== window.location.origin) {
			return false;
		}

		if (destination.pathname === window.location.pathname && destination.search === window.location.search) {
			return false;
		}

		return true;
	};

	document.addEventListener('click', (event) => {
		const target = event.target;
		if (!(target instanceof Element)) {
			return;
		}

		const link = target.closest('a[href]');
		if (!(link instanceof HTMLAnchorElement) || !isInternalNavigation(link, event)) {
			return;
		}

		event.preventDefault();
		isNavigating = true;
		bodyElement.classList.add('is-leaving');

		window.setTimeout(() => {
			window.location.assign(link.href);
		}, 280);
	});

	window.addEventListener('pageshow', () => {
		bodyElement.classList.remove('is-leaving');
		isNavigating = false;
	});
};

setupPageTransitions();

const getOrCreateToast = () => {
	let toast = document.querySelector('.submit-toast');

	if (!toast) {
		toast = document.createElement('div');
		toast.className = 'submit-toast';
		toast.innerHTML = '<span class="dot"></span><span class="toast-text"></span>';
		document.body.appendChild(toast);
	}

	return toast;
};

const showToast = (message, type = 'success', autoHideMs = 0) => {
	if (!message) {
		return;
	}

	const toast = getOrCreateToast();
	const text = toast.querySelector('.toast-text');

	toast.classList.remove('success', 'error');
	toast.classList.add(type, 'show');

	if (text) {
		text.textContent = message;
	}

	if (autoHideMs > 0) {
		window.setTimeout(() => {
			toast.classList.remove('show');
		}, autoHideMs);
	}
};

const payload = document.querySelector('.toast-payload, .auth-toast-payload');
if (payload) {
	const successMessage = payload.getAttribute('data-toast-success') || '';
	const errorMessage = payload.getAttribute('data-toast-error') || '';

	if (errorMessage) {
		showToast(errorMessage, 'error', 4200);
	} else if (successMessage) {
		showToast(successMessage, 'success', 3500);
	}
}

document.querySelectorAll('.js-auth-submit').forEach((form) => {
	form.addEventListener('submit', () => {
		const submitButton = form.querySelector('.auth-submit-btn');

		if (!submitButton) {
			return;
		}

		submitButton.classList.add('is-loading');
		submitButton.setAttribute('disabled', 'disabled');

		showToast('Processing...', 'success', 0);
	});
});

document.querySelectorAll('form').forEach((form) => {
	form.addEventListener('submit', () => {
		const submitButton = form.querySelector('button[type="submit"]');
		if (!submitButton || submitButton.classList.contains('auth-submit-btn')) {
			return;
		}

		submitButton.setAttribute('disabled', 'disabled');
		submitButton.classList.add('is-loading');
	});
});

const staggerGroups = [
	'.products-grid .product-card',
	'.cart-items .cart-item',
	'.data-table tbody tr',
	'.dashboard-mini-stats .summary',
	'.dashboard-actions .summary',
	'.profile-cards .summary',
	'.profile-mini .summary',
	'.admin-stats-grid .summary',
];

staggerGroups.forEach((selector) => {
	document.querySelectorAll(selector).forEach((element, index) => {
		if (!(element instanceof HTMLElement)) {
			return;
		}

		element.classList.add('reveal');
		element.style.setProperty('--reveal-delay', `${Math.min(index, 10) * 65}ms`);
	});
});

const revealElements = document.querySelectorAll('.reveal');

if ('IntersectionObserver' in window && revealElements.length > 0) {
	const observer = new IntersectionObserver(
		(entries) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting) {
					entry.target.classList.add('in');
					observer.unobserve(entry.target);
				}
			});
		},
		{ threshold: 0.15 }
	);

	revealElements.forEach((item) => observer.observe(item));
} else {
	revealElements.forEach((item) => item.classList.add('in'));
}

const setupProductCardParallax = () => {
	const supportsFinePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
	const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	if (!supportsFinePointer || prefersReducedMotion) {
		return;
	}

	document.querySelectorAll('.product-card').forEach((cardElement) => {
		if (!(cardElement instanceof HTMLElement)) {
			return;
		}

		cardElement.addEventListener('pointermove', (event) => {
			const rect = cardElement.getBoundingClientRect();
			const ratioX = (event.clientX - rect.left) / rect.width;
			const ratioY = (event.clientY - rect.top) / rect.height;
			const rotateY = (ratioX - 0.5) * 12;
			const rotateX = (0.5 - ratioY) * 10;

			cardElement.classList.add('is-tilt');
			cardElement.style.setProperty('--mx', `${Math.max(0, Math.min(1, ratioX)) * 100}%`);
			cardElement.style.setProperty('--my', `${Math.max(0, Math.min(1, ratioY)) * 100}%`);
			cardElement.style.transform = `perspective(900px) rotateX(${rotateX.toFixed(2)}deg) rotateY(${rotateY.toFixed(2)}deg) translateY(-4px)`;
		});

		const resetTilt = () => {
			cardElement.classList.remove('is-tilt');
			cardElement.style.transform = '';
		};

		cardElement.addEventListener('pointerleave', resetTilt);
		cardElement.addEventListener('pointercancel', resetTilt);
		cardElement.addEventListener('blur', resetTilt, true);
	});
};

setupProductCardParallax();

document.querySelectorAll('.product-card-link[data-href]').forEach((cardElement) => {
	if (!(cardElement instanceof HTMLElement)) {
		return;
	}

	const goToProduct = () => {
		const href = cardElement.getAttribute('data-href');
		if (href) {
			window.location.assign(href);
		}
	};

	cardElement.addEventListener('click', (event) => {
		const target = event.target;
		if (target instanceof Element && target.closest('a, button, form, input, select, textarea, label')) {
			return;
		}

		goToProduct();
	});

	cardElement.addEventListener('keydown', (event) => {
		if (event.key === 'Enter' || event.key === ' ') {
			event.preventDefault();
			goToProduct();
		}
	});
});

const menuToggle = document.querySelector('.menu-toggle');
const siteNav = document.querySelector('#site-nav');

if (menuToggle && siteNav) {
	const firstNavItem = siteNav.querySelector('a, button');

	const closeMenu = () => {
		siteNav.classList.remove('is-open');
		menuToggle.classList.remove('is-open');
		menuToggle.setAttribute('aria-expanded', 'false');
	};

	menuToggle.addEventListener('click', () => {
		const isOpen = siteNav.classList.toggle('is-open');
		menuToggle.classList.toggle('is-open', isOpen);
		menuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

		if (isOpen && firstNavItem) {
			firstNavItem.focus();
		}
	});

	siteNav.querySelectorAll('a').forEach((link) => {
		link.addEventListener('click', closeMenu);
	});

	window.addEventListener('resize', () => {
		if (window.innerWidth > 900) {
			closeMenu();
		}
	});

	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape') {
			closeMenu();
			menuToggle.focus();
		}
	});

	document.addEventListener('click', (event) => {
		if (!siteNav.classList.contains('is-open')) {
			return;
		}

		const target = event.target;
		if (!(target instanceof Element)) {
			return;
		}

		if (!siteNav.contains(target) && !menuToggle.contains(target)) {
			closeMenu();
		}
	});
}

const themeToggle = document.querySelector('.theme-toggle');
const body = document.body;
const THEME_KEY = 'electrohub-theme';

const applyTheme = (theme) => {
	const isLight = theme === 'light';
	body.classList.toggle('light-theme', isLight);
	if (themeToggle) {
		themeToggle.innerHTML = isLight
			? '<i class="bi bi-brightness-high" aria-hidden="true"></i>'
			: '<i class="bi bi-moon-stars-fill" aria-hidden="true"></i>';
		themeToggle.setAttribute('aria-label', isLight ? 'U beddel dark mode' : 'U beddel light mode');
		themeToggle.setAttribute('title', isLight ? 'U beddel dark mode' : 'U beddel light mode');
	}
};

const savedTheme = localStorage.getItem(THEME_KEY) || 'dark';
applyTheme(savedTheme);

if (themeToggle) {
	themeToggle.addEventListener('click', () => {
		const nextTheme = body.classList.contains('light-theme') ? 'dark' : 'light';
		applyTheme(nextTheme);
		localStorage.setItem(THEME_KEY, nextTheme);
	});
}

document.querySelectorAll('[data-image-input]').forEach((input) => {
	input.addEventListener('change', () => {
		const fileInput = /** @type {HTMLInputElement} */ (input);
		const selector = fileInput.getAttribute('data-image-input');
		if (!selector) {
			return;
		}

		const preview = document.querySelector(selector);
		if (!(preview instanceof HTMLImageElement)) {
			return;
		}

		const file = fileInput.files?.[0];
		if (!file) {
			return;
		}

		preview.src = URL.createObjectURL(file);
		preview.style.display = 'block';
	});
});

document.querySelectorAll('form[data-draft-form]').forEach((formElement) => {
	const form = /** @type {HTMLFormElement} */ (formElement);
	const key = `draft:${form.getAttribute('data-draft-form')}`;

	const readState = () => {
		try {
			return JSON.parse(localStorage.getItem(key) || '{}');
		} catch {
			return {};
		}
	};

	const saved = readState();
	form.querySelectorAll('input[name], select[name], textarea[name]').forEach((field) => {
		const element = /** @type {HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement} */ (field);
		const name = element.name;
		if (!name || saved[name] === undefined) {
			return;
		}

		if (element instanceof HTMLInputElement && element.type === 'checkbox') {
			element.checked = Boolean(saved[name]);
		} else if (String(element.value || '').trim() === '') {
			element.value = String(saved[name]);
		}
	});

	const persist = () => {
		const data = {};
		form.querySelectorAll('input[name], select[name], textarea[name]').forEach((field) => {
			const element = /** @type {HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement} */ (field);
			if (element instanceof HTMLInputElement && element.type === 'file') {
				return;
			}
			if (!element.name) {
				return;
			}
			if (element instanceof HTMLInputElement && element.type === 'checkbox') {
				data[element.name] = element.checked;
			} else {
				data[element.name] = element.value;
			}
		});
		localStorage.setItem(key, JSON.stringify(data));
	};

	form.addEventListener('input', persist);
	form.addEventListener('change', persist);
	form.addEventListener('submit', () => localStorage.removeItem(key));
});
