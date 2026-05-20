=== D2i Accessibility Toolkit ===
Contributors:      d2itechnology
Tags:              accessibility, wcag, ada, widget
Requires at least: 6.0
Tested up to:      7.0
Requires PHP:      7.4
Stable tag:        1.0.1
License:           GPL-2.0-or-later
License URI:       https://www.gnu.org/licenses/gpl-2.0.html

Floating accessibility widget with one-click WCAG 2.1/2.2, ADA, Section 508 & EN 301 549 adjustments. Free, no upsells, no external requests..

== Description ==

**D2i Accessibility Toolkit** injects a floating accessibility panel into the front-end of any WordPress site. Visitors can activate assistive adjustments in a single click without needing to change their browser or operating system settings.

> **Important notice:** This plugin helps users customise their browsing experience. It does **not** automatically make your website fully compliant with WCAG, ADA, Section 508, or EN 301 549. Website owners remain responsible for ensuring their content (semantic HTML, image alt text, captions, heading structure, and color contrast in their own designs) meets applicable standards.

= Features =

* **Contrast modes** — Dark, Light, High Contrast (cycle through)
* **Highlight Links** — high-visibility outlines and backgrounds on all links
* **Bigger Text** — 100 % → 120 % → 140 % → 160 % → 180 % → 200 % (rem-based, reflows correctly)
* **Text Spacing** — applies WCAG 1.4.12 exact values (line-height 1.5, letter-spacing 0.12 em, word-spacing 0.16 em, paragraph spacing 2 em)
* **Pause Animations** — stops CSS animations/transitions; pauses `<video autoplay>`; respects `prefers-reduced-motion` automatically
* **Hide Images** — hides img/picture/SVG; injects alt text as visible text nodes
* **Dyslexia Friendly** — applies OpenDyslexic font (bundled locally, no CDN), increases line-height and letter-spacing
* **Big Cursor** — 64 px white or black cursor SVG, locally bundled
* **Line Height** — Default → 1.5 → 1.75 → 2.0 → 2.5
* **Text Alignment** — Default → Left → Centre → Right (justify excluded per WCAG 1.4.8)
* **Reset All** — one click restores defaults, clears LocalStorage

= Compliance support =

The widget provides user-facing adjustments that support meeting:

* **WCAG 2.1 Level AA** (all applicable SCs)
* **WCAG 2.2** new SCs (2.4.11 Focus Not Obscured, 2.5.8 Target Size ≥ 44 × 44 px, 3.2.6 Consistent Help)
* **ADA Title II / III**
* **Section 508 Refresh**
* **EN 301 549**

= Privacy =

* **No external requests** — all fonts, cursors, and icons are bundled locally
* **No tracking, no analytics, no phone-home**
* User preferences are stored in **browser LocalStorage** only — no cookies, no database writes

= Widget accessibility =

The widget itself is fully accessible:

* All controls are semantic `<button>` elements
* Color contrast ≥ 4.5:1 within the widget
* Touch targets ≥ 44 × 44 px
* Keyboard navigable; focus trapped while panel is open; Escape to close
* Full ARIA: `role="dialog"`, `aria-modal`, `aria-labelledby`, `aria-pressed`, `aria-expanded`, `aria-live`
* Tested with NVDA, JAWS, VoiceOver, TalkBack
* Widget is never affected by its own filters (invert/saturation are counter-filtered on the widget container)

== Installation ==

= From WordPress Dashboard (Recommended) =
1. Go to **Plugins → Add New**
2. Search for **D2i Accessibility Toolkit**
3. Click **Install Now**, then **Activate**
4. Go to **D2i Accessibility** in the WordPress admin sidebar menu to configure the widget

= Manual Installation =
1. Download the plugin ZIP from the [WordPress Plugin Directory](https://wordpress.org/plugins/d2i-accessibility-toolkit/)
2. Go to **Plugins → Add New → Upload Plugin**
3. Upload the ZIP file and click **Install Now**, then **Activate**
4. Go to **D2i Accessibility** in the WordPress admin sidebar menu to configure the widget

== Frequently Asked Questions ==

= Where does the OpenDyslexic font come from? =

OpenDyslexic is an open-source font licensed under the SIL Open Font Licence. `OpenDyslexic-Regular.woff2` and `OpenDyslexic-Bold.woff2` are downloaded from [opendyslexic.org](https://opendyslexic.org) and bundled in the `public/fonts/` directory — no CDN or external request is made.

= Does this plugin make my site WCAG compliant? =

No. Accessibility overlays and toolkits are a supplemental aid, not a substitute for accessible design and content. You must still ensure your site's HTML is semantic, images have alt text, videos have captions, and your color scheme meets contrast requirements.

= Where are user preferences stored? =

Browser LocalStorage under the key `d2i_a11y_user_prefs`. No data is sent to a server.

= Is this plugin GDPR-friendly? =

Yes. It makes no external requests, sets no cookies, and stores no personal data on the server.

== Screenshots ==

1. Widget trigger button (bottom-right, default position)
2. Open accessibility panel showing all feature tiles
3. High-contrast mode active
4. Admin settings page
5. Statement generator

== Changelog ==

= 1.0.1 =
* Confirmed compatibility with WordPress 7.0
* Fixed admin assets (CSS/JS) not loading on the plugin settings page

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.1 =
Confirmed compatibility with WordPress 7.0. Fixes admin settings page styles not loading correctly.

= 1.0.0 =
Initial release.
