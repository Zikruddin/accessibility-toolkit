/**
 * D2i Accessibility Toolkit — Widget JS
 * Vanilla ES6+, no jQuery, no external dependencies.
 * Deferred; runs after HTML is parsed.
 *
 * @package D2i_Accessibility_Toolkit
 */
(function () {
	'use strict';

	// -----------------------------------------------------------------------
	// Constants & settings
	// -----------------------------------------------------------------------

	var STORAGE_KEY = 'd2i_a11y_user_prefs';
	var HTML = document.documentElement;
	var SETTINGS = window.d2iA11ySettings || {};
	var pluginUrl = SETTINGS.pluginUrl || '';

	// -----------------------------------------------------------------------
	// Feature definitions
	// Each feature drives one tile in the panel.
	// -----------------------------------------------------------------------

	var FEATURES = [
		{
			id: 'contrast',
			type: 'cycle',
			label: 'Contrast',
			icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18V4c4.41 0 8 3.59 8 8s-3.59 8-8 8z" fill="currentColor"/></svg>',
			steps: [
				{ value: 'off',   label: 'Off',           cls: null },
				{ value: 'dark',  label: 'Dark',          cls: 'd2i-a11y-contrast-dark' },
				{ value: 'light', label: 'Light',         cls: 'd2i-a11y-contrast-light' },
				{ value: 'high',  label: 'High Contrast', cls: 'd2i-a11y-contrast-high' },
			],
		},
		{
			id: 'highlight_links',
			type: 'toggle',
			label: 'Highlight Links',
			icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><path d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1s-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5z" fill="currentColor"/></svg>',
			cls: 'd2i-a11y-highlight-links',
		},
		{
			id: 'bigger_text',
			type: 'cycle',
			label: 'Bigger Text',
			icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><path d="M9 4v3h5v12h3V7h5V4H9zm-6 8h3v7h3v-7h3V9H3v3z" fill="currentColor"/></svg>',
			steps: [
				{ value: '100', label: 'Normal', cls: null },
				{ value: '120', label: '120%',   cls: 'd2i-a11y-bigger-text-120' },
				{ value: '140', label: '140%',   cls: 'd2i-a11y-bigger-text-140' },
				{ value: '160', label: '160%',   cls: 'd2i-a11y-bigger-text-160' },
				{ value: '180', label: '180%',   cls: 'd2i-a11y-bigger-text-180' },
				{ value: '200', label: '200%',   cls: 'd2i-a11y-bigger-text-200' },
			],
		},
		{
			id: 'text_spacing',
			type: 'toggle',
			label: 'Text Spacing',
			icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><path d="M4 16h16v2H4v-2zm0-5h16v2H4v-2zm0-5h16v2H4V6z" fill="currentColor"/></svg>',
			cls: 'd2i-a11y-text-spacing',
		},
		{
			id: 'pause_animations',
			type: 'toggle',
			label: 'Pause Animations',
			icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z" fill="currentColor"/></svg>',
			cls: 'd2i-a11y-pause-animations',
		},
		{
			id: 'hide_images',
			type: 'toggle',
			label: 'Hide Images',
			icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><path d="M21 5v6.59l-3-3.01-4 4.01-4-4-4 4-3-3.01V5c0-1.1.9-2 2-2h14c1.1 0 2 .9 2 2zm-3 6.42l3 3.01V19c0 1.1-.9 2-2 2H5c-1.1 0-2-.9-2-2v-6.58l3 2.99 4-4 4 4 4-3.99z" fill="currentColor"/></svg>',
			cls: 'd2i-a11y-hide-images',
		},
		{
			id: 'dyslexia',
			type: 'toggle',
			label: 'Dyslexia Friendly',
			icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><text x="2" y="19" font-size="15" font-weight="bold" fill="currentColor" font-family="serif">Aa</text></svg>',
			cls: 'd2i-a11y-dyslexia',
		},
		{
			id: 'cursor',
			type: 'cycle',
			label: 'Cursor',
			icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><path d="M4 0l16 12.279-6.951 1.17 4.325 8.817-3.596 1.734-4.35-8.879-5.428 4.702z" fill="currentColor"/></svg>',
			steps: [
				{ value: 'default', label: 'Default',   cls: null },
				{ value: 'white',   label: 'Big White', cls: 'd2i-a11y-cursor-white' },
				{ value: 'black',   label: 'Big Black', cls: 'd2i-a11y-cursor-black' },
			],
		},
		{
			id: 'line_height',
			type: 'cycle',
			label: 'Line Height',
			icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><path d="M10 13h10v-2H10v2zm0-4h10V7H10v2zm0 8h10v-2H10v2zM6 7H4v2H2V7H.5L3 4.5 5.5 7H4zm0 10H2v-2H.5L3 17.5 5.5 15H4v-2H6v4z" fill="currentColor"/></svg>',
			steps: [
				{ value: 'default', label: 'Default', cls: null },
				{ value: '150',     label: '1.5',     cls: 'd2i-a11y-line-height-150' },
				{ value: '175',     label: '1.75',    cls: 'd2i-a11y-line-height-175' },
				{ value: '200',     label: '2.0',     cls: 'd2i-a11y-line-height-200' },
				{ value: '250',     label: '2.5',     cls: 'd2i-a11y-line-height-250' },
			],
		},
		{
			id: 'text_align',
			type: 'cycle',
			label: 'Text Align',
			icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><path d="M15 15H3v2h12v-2zm0-8H3v2h12V7zM3 13h18v-2H3v2zm0 8h18v-2H3v2zM3 3v2h18V3H3z" fill="currentColor"/></svg>',
			steps: [
				{ value: 'default', label: 'Default', cls: null },
				{ value: 'left',    label: 'Left',    cls: 'd2i-a11y-text-align-left' },
				{ value: 'center',  label: 'Center',  cls: 'd2i-a11y-text-align-center' },
				{ value: 'right',   label: 'Right',   cls: 'd2i-a11y-text-align-right' },
			],
		},
		{
			id: 'readable_font',
			type: 'toggle',
			label: 'Readable Font',
			icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><path d="M9.93 13.5h4.14L12 7.98zM20 2H4c-1.1 0-2 .9-2 2v16c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-4.05 16.5l-1.14-3H9.17l-1.12 3H5.96l5.11-13h1.86l5.11 13h-2.09z" fill="currentColor"/></svg>',
			cls: 'd2i-a11y-readable-font',
		},
		{
			id: 'bold_text',
			type: 'toggle',
			label: 'Bold Text',
			icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><path d="M15.6 10.79c.97-.67 1.65-1.77 1.65-2.79 0-2.26-1.75-4-4-4H7v14h7.04c2.09 0 3.71-1.7 3.71-3.79 0-1.52-.86-2.82-2.15-3.42zM10 6.5h3c.83 0 1.5.67 1.5 1.5S13.83 9.5 13 9.5h-3v-3zm3.5 9H10v-3h3.5c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5z" fill="currentColor"/></svg>',
			cls: 'd2i-a11y-bold-text',
		},
		{
			id: 'highlight_titles',
			type: 'toggle',
			label: 'Highlight Titles',
			icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><path d="M5 4v3h5.5v12h3V7H19V4z" fill="currentColor"/></svg>',
			cls: 'd2i-a11y-highlight-titles',
		},
		{
			id: 'mute_sounds',
			type: 'toggle',
			label: 'Mute Sounds',
			icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><path d="M16.5 12c0-1.77-1.02-3.29-2.5-4.03v2.21l2.45 2.45c.03-.2.05-.41.05-.63zm2.5 0c0 .94-.2 1.82-.54 2.64l1.51 1.51C20.63 14.91 21 13.5 21 12c0-4.28-2.99-7.86-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM4.27 3L3 4.27 7.73 9H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06c1.38-.31 2.63-.95 3.69-1.81L19.73 21 21 19.73l-9-9L4.27 3zM12 4L9.91 6.09 12 8.18V4z" fill="currentColor"/></svg>',
			cls: 'd2i-a11y-mute-sounds',
		},
		{
			id: 'reading_guide',
			type: 'toggle',
			label: 'Reading Guide',
			icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z" fill="currentColor"/></svg>',
			cls: 'd2i-a11y-reading-guide-on',
		},
		{
			id: 'keyboard_nav',
			type: 'toggle',
			label: 'Keyboard Nav',
			icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" aria-hidden="true" focusable="false"><path d="M20 5H4c-1.1 0-1.99.9-1.99 2L2 17c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm-9 3h2v2h-2V8zm0 3h2v2h-2v-2zM8 8h2v2H8V8zm0 3h2v2H8v-2zm-1 2H5v-2h2v2zm0-3H5V8h2v2zm9 7H8v-2h8v2zm0-4h-2v-2h2v2zm0-3h-2V8h2v2zm3 3h-2v-2h2v2zm0-3h-2V8h2v2z" fill="currentColor"/></svg>',
			cls: 'd2i-a11y-keyboard-nav',
		},
	];

	// -----------------------------------------------------------------------
	// Pre-made profiles — each applies a set of feature preferences at once
	// -----------------------------------------------------------------------

	var PROFILES = [
		{
			id: 'vision_impaired',
			label: 'Vision Impaired',
			icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" fill="currentColor"/></svg>',
			prefs: { contrast: 'high', bigger_text: '140', cursor: 'white', highlight_links: true },
		},
		{
			id: 'seizure_safe',
			label: 'Seizure Safe',
			icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" fill="currentColor"/></svg>',
			prefs: { pause_animations: true, contrast: 'light', mute_sounds: true },
		},
		{
			id: 'adhd_friendly',
			label: 'ADHD Friendly',
			icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path d="M12 2a10 10 0 100 20A10 10 0 0012 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z" fill="currentColor"/></svg>',
			prefs: { readable_font: true, highlight_links: true, highlight_titles: true, bold_text: true, reading_guide: true },
		},
		{
			id: 'blindness_mode',
			label: 'Blindness Mode',
			icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z" fill="currentColor"/></svg>',
			prefs: { contrast: 'high', text_spacing: true, pause_animations: true, keyboard_nav: true, cursor: 'white' },
		},
		{
			id: 'epilepsy_safe',
			label: 'Epilepsy Safe',
			icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false"><path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z" fill="currentColor"/></svg>',
			prefs: { pause_animations: true, contrast: 'dark', mute_sounds: true },
		},
	];

	// -----------------------------------------------------------------------
	// Enabled feature filter
	// -----------------------------------------------------------------------

	var enabledIds = SETTINGS.enabledFeatures || FEATURES.map(function (f) { return f.id; });

	var activeFeatures = FEATURES.filter(function (f) {
		return enabledIds.indexOf(f.id) !== -1;
	});

	// -----------------------------------------------------------------------
	// Preference state
	// -----------------------------------------------------------------------

	var prefs = {};

	function loadPrefs() {
		try {
			return JSON.parse(localStorage.getItem(STORAGE_KEY) || '{}');
		} catch (e) {
			return {};
		}
	}

	function savePrefs() {
		try {
			localStorage.setItem(STORAGE_KEY, JSON.stringify(prefs));
		} catch (e) { /* LocalStorage unavailable — silently degrade */ }
	}

	// -----------------------------------------------------------------------
	// Apply / remove HTML classes
	// -----------------------------------------------------------------------

	// All classes ever used — gathered at init for clean removal.
	var ALL_CLASSES = (function () {
		var cls = [];
		FEATURES.forEach(function (f) {
			if (f.type === 'toggle') {
				cls.push(f.cls);
			} else if (f.steps) {
				f.steps.forEach(function (s) { if (s.cls) cls.push(s.cls); });
			}
		});
		return cls.filter(Boolean);
	}());

	function removeAllFeatureClasses() {
		ALL_CLASSES.forEach(function (c) { HTML.classList.remove(c); });
	}

	function applyPrefs() {
		removeAllFeatureClasses();

		// Apply each feature's class.
		activeFeatures.forEach(function (f) {
			if (f.type === 'toggle') {
				if (prefs[f.id]) HTML.classList.add(f.cls);
			} else if (f.type === 'cycle' && f.steps) {
				var val = prefs[f.id];
				if (val) {
					var step = f.steps.find(function (s) { return s.value === val; });
					if (step && step.cls) HTML.classList.add(step.cls);
				}
			}
		});

		// Side-effects.
		handlePauseAnimations(!!prefs['pause_animations']);
		handleHideImages(!!prefs['hide_images']);
		handleMuteSounds(!!prefs['mute_sounds']);
		handleReadingGuide(!!prefs['reading_guide']);
	}

	// -----------------------------------------------------------------------
	// Profile handler — applies a preset combination of feature prefs
	// -----------------------------------------------------------------------

	var activeProfile = null;

	function applyProfile(profileId) {
		var profile = PROFILES.find(function (p) { return p.id === profileId; });
		if (!profile) return;

		// Toggle: clicking an active profile resets it.
		if (activeProfile === profileId) {
			activeProfile = null;
			resetAll();
			syncProfileButtons();
			return;
		}

		activeProfile = profileId;

		// Reset existing prefs then apply profile prefs.
		prefs = {};
		Object.keys(profile.prefs).forEach(function (key) {
			prefs[key] = profile.prefs[key];
		});
		savePrefs();
		applyPrefs();
		syncTiles();
		syncProfileButtons();
		announce(profile.label + ' profile applied');
	}

	function syncProfileButtons() {
		var btns = document.querySelectorAll('.d2i-a11y-profile-btn');
		btns.forEach(function (btn) {
			var pid = btn.getAttribute('data-profile');
			btn.classList.toggle('d2i-a11y-active', pid === activeProfile);
			btn.setAttribute('aria-pressed', String(pid === activeProfile));
		});
	}

	// -----------------------------------------------------------------------
	// Side-effect handlers
	// -----------------------------------------------------------------------

	// Track paused videos for resume.
	var pausedVideos = [];
	// Track alt-text nodes injected for images.
	var altNodes = [];

	function handlePauseAnimations(enable) {
		var videos = document.querySelectorAll('video[autoplay], video[data-autoplay]');
		if (enable) {
			pausedVideos = [];
			videos.forEach(function (v) {
				if (!v.paused) { v.pause(); pausedVideos.push(v); }
			});
		} else {
			pausedVideos.forEach(function (v) {
				try { v.play(); } catch (e) { /* autoplay policy may block */ }
			});
			pausedVideos = [];
		}
	}

	function handleHideImages(enable) {
		if (enable) {
			// Inject alt text next to each img that has a non-empty alt.
			var imgs = document.querySelectorAll('img:not(#d2i-a11y-wrapper img)');
			imgs.forEach(function (img) {
				var alt = img.getAttribute('alt');
				if (alt && alt.trim() && !img.nextSibling?.__d2iAlt) {
					var span = document.createElement('span');
					span.className = 'd2i-a11y-alt-text';
					span.textContent = alt;
					span.__d2iAlt = true;
					img.insertAdjacentElement('afterend', span);
					altNodes.push(span);
				}
			});
		} else {
			// Remove injected alt nodes.
			altNodes.forEach(function (n) { n.parentNode && n.parentNode.removeChild(n); });
			altNodes = [];
		}
	}

	// Mute / unmute all audio and video elements.
	function handleMuteSounds(enable) {
		var media = document.querySelectorAll('audio, video');
		media.forEach(function (el) { el.muted = enable; });
	}

	// Reading guide line — fixed horizontal bar that follows the mouse.
	var readingGuideEl = null;

	function handleReadingGuide(enable) {
		if (enable) {
			if (!readingGuideEl) {
				readingGuideEl = document.createElement('div');
				readingGuideEl.id = 'd2i-a11y-reading-guide';
				readingGuideEl.setAttribute('aria-hidden', 'true');
				document.body.appendChild(readingGuideEl);
			}
			document.addEventListener('mousemove', moveReadingGuide);
		} else {
			document.removeEventListener('mousemove', moveReadingGuide);
			if (readingGuideEl) {
				readingGuideEl.parentNode && readingGuideEl.parentNode.removeChild(readingGuideEl);
				readingGuideEl = null;
			}
		}
	}

	function moveReadingGuide(e) {
		if (readingGuideEl) {
			readingGuideEl.style.top = (e.clientY - 2) + 'px';
		}
	}

	// -----------------------------------------------------------------------
	// Widget DOM references (resolved after DOM ready)
	// -----------------------------------------------------------------------

	var wrapper, trigger, panel, closeBtn, resetBtn, liveRegion;

	// -----------------------------------------------------------------------
	// Panel open / close
	// -----------------------------------------------------------------------

	var focusTrapHandler = null;

	function openPanel() {
		panel.classList.add('d2i-a11y-panel-open');
		panel.removeAttribute('aria-hidden');
		trigger.setAttribute('aria-expanded', 'true');
		panel.setAttribute('tabindex', '-1');

		// Move focus to close button (first focusable element in panel).
		requestAnimationFrame(function () { closeBtn.focus(); });

		// Focus trap.
		focusTrapHandler = makeFocusTrap(panel, trigger);
		panel.addEventListener('keydown', focusTrapHandler);

		// Close on Escape.
		panel.addEventListener('keydown', handlePanelKeydown);

		// Close on outside click.
		document.addEventListener('click', handleOutsideClick, true);

		// Auto-save "has seen widget" for auto-show logic.
		try { localStorage.setItem('d2i_a11y_seen', '1'); } catch (e) {}
	}

	function closePanel() {
		panel.classList.remove('d2i-a11y-panel-open');
		panel.setAttribute('aria-hidden', 'true');
		trigger.setAttribute('aria-expanded', 'false');
		panel.removeAttribute('tabindex');

		if (focusTrapHandler) {
			panel.removeEventListener('keydown', focusTrapHandler);
			focusTrapHandler = null;
		}
		panel.removeEventListener('keydown', handlePanelKeydown);
		document.removeEventListener('click', handleOutsideClick, true);

		// Reset to features view so next open shows features, not statement.
		var sv = document.getElementById('d2i-a11y-statement-view');
		var pb = document.getElementById('d2i-a11y-panel-body');
		if (sv) { sv.hidden = true; sv.setAttribute('aria-hidden', 'true'); }
		if (pb) { pb.hidden = false; }

		// Return focus to trigger.
		trigger.focus();
	}

	function handlePanelKeydown(e) {
		if (e.key === 'Escape') {
			e.preventDefault();
			closePanel();
		}
	}

	function handleOutsideClick(e) {
		if (wrapper && !wrapper.contains(e.target)) {
			closePanel();
		}
	}

	// -----------------------------------------------------------------------
	// Focus trap — cycles focus within container
	// -----------------------------------------------------------------------

	function makeFocusTrap(container, firstFallback) {
		return function (e) {
			if (e.key !== 'Tab') return;

			var focusable = Array.prototype.slice.call(
				container.querySelectorAll(
					'button:not([disabled]), [href], input:not([disabled]), ' +
					'select:not([disabled]), textarea:not([disabled]), ' +
					'[tabindex]:not([tabindex="-1"])'
				)
			).filter(function (el) { return !el.offsetParent === false || el.offsetWidth > 0 || el.offsetHeight > 0; });

			if (focusable.length === 0) return;

			var first = focusable[0];
			var last  = focusable[focusable.length - 1];

			if (e.shiftKey) {
				if (document.activeElement === first) {
					e.preventDefault();
					last.focus();
				}
			} else {
				if (document.activeElement === last) {
					e.preventDefault();
					first.focus();
				}
			}
		};
	}

	// -----------------------------------------------------------------------
	// Tile interaction
	// -----------------------------------------------------------------------

	function handleTileClick(tile) {
		var featureId = tile.getAttribute('data-feature');
		var type      = tile.getAttribute('data-type');
		var feature   = activeFeatures.find(function (f) { return f.id === featureId; });

		if (!feature) return;

		if (type === 'toggle') {
			var isOn = tile.getAttribute('aria-pressed') === 'true';
			var next = !isOn;
			prefs[featureId] = next;
			tile.setAttribute('aria-pressed', String(next));
			tile.classList.toggle('d2i-a11y-active', next);
			updateTileState(tile, feature, next ? 'On' : 'Off');
			// aria-pressed change is announced natively by all SRs ("pressed"/"not pressed").
			// No aria-live call needed — it would cause a second announcement.

		} else if (type === 'cycle') {
			var currentStep = parseInt(tile.getAttribute('data-step') || '0', 10);
			var nextStep    = (currentStep + 1) % feature.steps.length;
			var stepDef     = feature.steps[nextStep];

			prefs[featureId] = stepDef.value;
			tile.setAttribute('data-step', String(nextStep));
			tile.classList.toggle('d2i-a11y-active', nextStep !== 0);

			var stateLabel = stepDef.label;
			updateTileState(tile, feature, stateLabel);
			// Update aria-label to "Contrast: Dark" — SRs detect the label change
			// on the focused element and announce it once. No aria-live call needed.
			tile.setAttribute('aria-label', feature.label + ': ' + stateLabel);
		}

		applyPrefs();
		savePrefs();
		syncTiles();
	}

	function updateTileState(tile, feature, stateText) {
		var stateEl = tile.querySelector('.d2i-a11y-tile-state');
		if (stateEl) stateEl.textContent = stateText;
	}

	// -----------------------------------------------------------------------
	// Sync all tiles to current prefs (called on load and after reset)
	// -----------------------------------------------------------------------

	function syncTiles() {
		if (!panel) return;
		var tiles = panel.querySelectorAll('.d2i-a11y-tile');

		tiles.forEach(function (tile) {
			var featureId = tile.getAttribute('data-feature');
			var type      = tile.getAttribute('data-type');
			var feature   = FEATURES.find(function (f) { return f.id === featureId; });
			if (!feature) return;

			if (type === 'toggle') {
				var isOn = !!prefs[featureId];
				tile.setAttribute('aria-pressed', String(isOn));
				tile.classList.toggle('d2i-a11y-active', isOn);
				updateTileState(tile, feature, isOn ? 'On' : 'Off');

			} else if (type === 'cycle' && feature.steps) {
				var val = prefs[featureId] || feature.steps[0].value;
				var idx = feature.steps.findIndex(function (s) { return s.value === val; });
				if (idx < 0) idx = 0;
				tile.setAttribute('data-step', String(idx));
				var stepDef = feature.steps[idx];
				tile.classList.toggle('d2i-a11y-active', idx !== 0);
				updateTileState(tile, feature, stepDef.label);
				tile.setAttribute('aria-label', feature.label + ': ' + stepDef.label);
			}
		});
	}

	// -----------------------------------------------------------------------
	// Screen reader announcement
	// -----------------------------------------------------------------------

	function announce(message) {
		if (!liveRegion) return;
		liveRegion.textContent = '';
		// Slight delay so the DOM change triggers the announcement.
		setTimeout(function () { liveRegion.textContent = message; }, 50);
	}

	// -----------------------------------------------------------------------
	// Reset all
	// -----------------------------------------------------------------------

	function resetAll() {
		prefs = {};
		activeProfile = null;
		savePrefs();
		removeAllFeatureClasses();
		handlePauseAnimations(false);
		handleHideImages(false);
		handleMuteSounds(false);
		handleReadingGuide(false);
		syncTiles();
		syncProfileButtons();
		announce('All accessibility settings reset.');
	}

	// -----------------------------------------------------------------------
	// prefers-reduced-motion auto-enable
	// -----------------------------------------------------------------------

	function checkReducedMotion() {
		if (!window.matchMedia) return;
		var mq = window.matchMedia('(prefers-reduced-motion: reduce)');

		function applyMotionPref(matches) {
			// Only auto-enable; never auto-disable (user may have explicitly turned it off).
			if (matches && prefs['pause_animations'] === undefined) {
				prefs['pause_animations'] = true;
				savePrefs();
				applyPrefs();
				syncTiles();
			}
		}

		applyMotionPref(mq.matches);

		if (mq.addEventListener) {
			mq.addEventListener('change', function (e) { applyMotionPref(e.matches); });
		} else if (mq.addListener) {
			mq.addListener(function (e) { applyMotionPref(e.matches); });
		}
	}

	// -----------------------------------------------------------------------
	// Initialise
	// -----------------------------------------------------------------------

	function init() {
		prefs = loadPrefs();
		applyPrefs(); // Apply immediately (also done by inline head script, this syncs side-effects).
		checkReducedMotion();

		// Resolve DOM references — the PHP class already rendered the HTML.
		wrapper    = document.getElementById('d2i-a11y-wrapper');
		trigger    = document.getElementById('d2i-a11y-trigger');
		panel      = document.getElementById('d2i-a11y-panel');
		closeBtn   = document.getElementById('d2i-a11y-close');
		resetBtn   = document.getElementById('d2i-a11y-reset');
		liveRegion = document.getElementById('d2i-a11y-live');

		if (!wrapper || !trigger || !panel) return; // Widget not rendered on this page.

		// Guarantee the wrapper is a direct child of <body>.
		// Some themes nest wp_footer output inside their main page container.
		// When we apply a CSS filter to body > * the filtered element becomes a
		// containing block for position:fixed — moving the wrapper to body ensures
		// it is always a sibling of the filtered element, never inside it.
		if (wrapper.parentNode !== document.body) {
			document.body.appendChild(wrapper);
		}

		// Sync tiles to stored prefs.
		syncTiles();

		// Wire trigger button.
		trigger.addEventListener('click', function () {
			var isOpen = panel.classList.contains('d2i-a11y-panel-open');
			isOpen ? closePanel() : openPanel();
		});

		// Wire close button.
		if (closeBtn) closeBtn.addEventListener('click', closePanel);

		// Wire reset button.
		if (resetBtn) resetBtn.addEventListener('click', function () {
			resetAll();
			announce('All settings reset.');
		});

		// Wire Statement button — show inline statement view.
		var stmtBtn  = document.getElementById('d2i-a11y-statement-btn');
		var stmtView = document.getElementById('d2i-a11y-statement-view');
		var panelBody = document.getElementById('d2i-a11y-panel-body');
		var backBtn  = document.getElementById('d2i-a11y-back');

		if (stmtBtn && stmtView && panelBody) {
			stmtBtn.addEventListener('click', function () {
				panelBody.hidden = true;
				stmtView.hidden  = false;
				stmtView.removeAttribute('aria-hidden');
				if (backBtn) backBtn.focus();
			});
		}

		if (backBtn && stmtView && panelBody) {
			backBtn.addEventListener('click', function () {
				stmtView.hidden  = true;
				stmtView.setAttribute('aria-hidden', 'true');
				panelBody.hidden = false;
				if (stmtBtn) stmtBtn.focus();
			});
		}

		// Wire profile buttons (event delegation on the profiles section).
		var profilesGrid = document.getElementById('d2i-a11y-profiles-grid');
		if (profilesGrid) {
			profilesGrid.addEventListener('click', function (e) {
				var btn = e.target.closest('.d2i-a11y-profile-btn');
				if (btn) applyProfile(btn.getAttribute('data-profile'));
			});
		}

		// Wire feature tiles (event delegation).
		var panelBody = document.getElementById('d2i-a11y-panel-body');
		if (panelBody) {
			panelBody.addEventListener('click', function (e) {
				var tile = e.target.closest('.d2i-a11y-tile');
				if (tile) handleTileClick(tile);
			});

			// Keyboard: Enter/Space on tiles (already handled by browser for buttons,
			// but guard for edge cases).
			panelBody.addEventListener('keydown', function (e) {
				if (e.key === 'Enter' || e.key === ' ') {
					var tile = e.target.closest('.d2i-a11y-tile');
					if (tile) {
						e.preventDefault();
						handleTileClick(tile);
					}
				}
			});
		}

		// Auto-show on first visit.
		if (SETTINGS.autoShow) {
			try {
				if (!localStorage.getItem('d2i_a11y_seen')) {
					setTimeout(openPanel, 800);
				}
			} catch (e) {}
		}
	}

	// Run after DOM is ready (script is deferred, so DOM is already parsed,
	// but guard for edge cases like document.write usage).
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

}());
