# Changelog

All notable changes to D2i Accessibility Toolkit are documented here.

The format follows [Keep a Changelog](https://keepachangelog.com/en/1.0.0/).
This project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.0] — 2024-01-01

### Added

- Floating accessibility widget with 11 user-facing features
- Contrast modes: Dark, Light, High Contrast
- Highlight Links toggle
- Bigger Text: 6-step cycle (100–200%)
- Text Spacing: WCAG 1.4.12 exact values
- Pause Animations: stops CSS animations, transitions, and autoplay videos; auto-activates on `prefers-reduced-motion`
- Hide Images: hides img/picture/SVG; preserves alt text as visible nodes
- Dyslexia Friendly: OpenDyslexic font (bundled locally), increased line-height and letter-spacing
- Big Cursor: 64 px white and black SVG cursors, locally bundled
- Line Height: 5-step cycle (default, 1.5, 1.75, 2.0, 2.5)
- Text Alignment: 4-step cycle (default, left, centre, right); justify excluded per WCAG 1.4.8
- Reset All: clears all preferences and LocalStorage key
- Widget preferences stored in `d2i_a11y_user_prefs` LocalStorage (no cookies, no DB writes)
- Early inline script in `<head>` applies stored preferences before first paint (prevents FOUC)
- Admin Settings page (Settings → D2i Accessibility)
- Admin options: widget position, primary color, icon style, trigger size, enabled features, powered-by credit, auto-show, show-on rules, disable-on-admin, skip-link injection, statement page link
- Accessibility Statement page generator (one-click; does not overwrite existing manual edits)
- Fully accessible widget: all controls are `<button>` elements, ARIA dialog, focus trap, `aria-live` region, WCAG 2.1 AA + 2.2 conformant UI
- IE 11 graceful degradation (widget hidden silently, no JS errors)
- No external requests: all fonts, cursors, icons bundled locally
- Translation-ready with `.pot` template
- GPL-2.0-or-later licence
- Documentation: INSTALL.md, TESTING.md, WCAG-MAPPING.md
