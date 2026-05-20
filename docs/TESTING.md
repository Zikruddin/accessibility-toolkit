# D2i Accessibility Toolkit — Manual Testing Checklist

Use this checklist before each release and when testing in new environments.

---

## 1. Installation & Activation

- [ ] Plugin activates without PHP errors or warnings
- [ ] Default options are saved correctly (check `d2i_a11y_settings` in wp_options)
- [ ] Widget trigger button appears on the front-end immediately after activation
- [ ] Widget does NOT appear on wp-admin pages (when "Disable on Admin" is on)
- [ ] Widget does NOT appear on wp-login.php (when "Disable on Admin" is on)

---

## 2. Widget Trigger Button

- [ ] Button is visible in the configured position
- [ ] Button size matches the configured size (48/56/64 px)
- [ ] Button color matches the configured primary color
- [ ] Button has `aria-label="Open accessibility menu"`
- [ ] Button is focusable via Tab key
- [ ] Focus indicator is clearly visible (2 px ring, ≥ 3:1 contrast)
- [ ] Touch target is ≥ 44 × 44 px
- [ ] Activating with Enter/Space opens the panel

---

## 3. Panel Opening & Closing

- [ ] Panel opens smoothly (or instantly if `prefers-reduced-motion` is set)
- [ ] Focus moves to the Close button when panel opens
- [ ] `aria-expanded` on trigger changes to `"true"` when open
- [ ] Panel has `role="dialog"`, `aria-modal="true"`, `aria-labelledby="d2i-a11y-title"`
- [ ] Pressing Escape closes the panel
- [ ] Clicking the × Close button closes the panel
- [ ] Clicking outside the widget closes the panel
- [ ] Focus returns to the trigger button when panel closes
- [ ] `aria-expanded` returns to `"false"` when closed

---

## 4. Focus Trap

- [ ] Tab cycles through all interactive elements within the panel
- [ ] Shift+Tab reverses through elements
- [ ] Tab from the last element wraps to the first
- [ ] Shift+Tab from the first element wraps to the last
- [ ] No focus escapes to the page behind the panel while it's open

---

## 5. Feature Tiles — General

- [ ] All enabled tiles are rendered as `<button>` elements (not divs)
- [ ] All tiles are focusable via keyboard
- [ ] Focus indicator clearly visible on each tile (≥ 2 px, ≥ 3:1 contrast)
- [ ] Touch target ≥ 44 × 44 px per tile (tiles are 88 px minimum)
- [ ] Current state is visible (label updates, active visual state)
- [ ] Screen reader announces new state when a tile is activated (aria-live region)

---

## 6. Individual Feature Tests

### Contrast (cycle)
- [ ] Off → Dark Contrast: dark background, light text applied to page (not widget)
- [ ] Dark → Light Contrast: light background, dark text
- [ ] Light → High Contrast: black background, white text, yellow links
- [ ] High → High Contrast: black background, white text, yellow links
- [ ] **Widget remains readable in all contrast modes** (counter-filter applied)
- [ ] Invert → Off: page returns to normal

### Highlight Links
- [ ] All links get visible outline + underline + background
- [ ] Visited links have distinct style
- [ ] Hover/focus states are distinct
- [ ] Toggling off removes all highlight styles

### Bigger Text
- [ ] 100 % → 120 % → 140 % → 160 % → 180 % → 200 % steps work
- [ ] Page text scales; layout does not break (use `overflow-wrap: break-word`)
- [ ] Widget size is unaffected by text scaling
- [ ] Cycling back to 100 % restores original size

### Text Spacing
- [ ] Line-height 1.5, letter-spacing 0.12 em, word-spacing 0.16 em applied
- [ ] Paragraph spacing (margin-bottom 2 em on `<p>`) applied
- [ ] Toggling off removes all spacing overrides

### Pause Animations
- [ ] CSS animations stop when enabled
- [ ] CSS transitions are disabled when enabled
- [ ] `<video autoplay>` elements pause
- [ ] Toggling off resumes videos and re-enables animations
- [ ] If OS `prefers-reduced-motion` is set, feature auto-enables on first load

### Hide Images
- [ ] `<img>` elements hidden
- [ ] `<picture>` elements hidden
- [ ] Background images on common containers removed
- [ ] Alt text of hidden images is shown as visible text nodes (`.d2i-a11y-alt-text`)
- [ ] Toggling off restores images and removes injected alt text nodes
- [ ] Widget images (logo SVG) are unaffected

### Dyslexia Friendly
- [ ] OpenDyslexic font applied if font files are present
- [ ] Falls back to Comic Sans/Arial if font files absent — feature still activates
- [ ] Line-height 1.8, letter-spacing 0.05 em, text-align left applied
- [ ] Widget font is unaffected

### Cursor
- [ ] Default → Big White: large white cursor with black outline visible
- [ ] White → Big Black: large black cursor with white outline visible
- [ ] Black → Default: browser default cursor restored
- [ ] Widget cursor is unaffected (widget uses its own styles)

### Line Height
- [ ] Default → 1.5 → 1.75 → 2.0 → 2.5 steps work
- [ ] Cycling back to Default restores original line-height
- [ ] Widget line-height is unaffected

### Text Alignment
- [ ] Default → Left → Centre → Right steps work
- [ ] Justify is NOT offered (excluded per WCAG 1.4.8)
- [ ] Widget alignment is unaffected

### Saturation
- [ ] Default → Low 50 % → High 200 % → Desaturate 0 % steps work
- [ ] Widget color is counter-filtered back to normal appearance

### Reset All
- [ ] All features return to Off/Default state
- [ ] All CSS classes removed from `<html>`
- [ ] LocalStorage key is cleared
- [ ] Tiles visually reset

---

## 7. Preference Persistence

- [ ] Reload the page — all active features are still applied
- [ ] Open a different page — preferences persist
- [ ] Open a new tab — preferences persist
- [ ] Reset All then reload — preferences are cleared

---

## 8. Admin Settings

- [ ] Settings page loads without errors
- [ ] All fields save correctly
- [ ] Color picker works
- [ ] Enabling/disabling features in admin hides/shows tiles on front-end
- [ ] Changing position moves the widget to the correct corner
- [ ] Statement Generator creates a page and links it in widget footer

---

## 9. Screen Reader Testing

### NVDA + Firefox / Chrome
- [ ] Trigger button label announced: "Open accessibility menu, button"
- [ ] Panel announced as dialog on open: "Accessibility Tools dialog"
- [ ] Feature tile labels and states announced correctly
- [ ] State changes announced via live region (e.g. "Contrast: High Contrast")
- [ ] Close button announced: "Close accessibility menu, button"
- [ ] Reset button label clear
- [ ] Panel closes and focus returns to trigger with announcement

### JAWS + Chrome
- [ ] Same checks as NVDA above

### VoiceOver + Safari (macOS)
- [ ] Trigger discoverable by VO cursor
- [ ] Dialog announced on open
- [ ] All tiles reachable and operable
- [ ] Escape closes dialog

### VoiceOver + Safari (iOS)
- [ ] Trigger tappable with single tap
- [ ] Panel navigable with swipe gestures
- [ ] All features operable via touch

### TalkBack + Chrome (Android)
- [ ] Trigger reachable
- [ ] Panel navigable and operable

---

## 10. Keyboard-Only Navigation

- [ ] Can reach the trigger via Tab from the top of the page
- [ ] Can open the panel with Enter/Space
- [ ] Can navigate all tiles with Tab
- [ ] Can activate each tile with Enter/Space
- [ ] Can close with Escape
- [ ] Can close with mouse click outside — focus returns to trigger
- [ ] No keyboard trap outside the panel
- [ ] Skip link (if enabled) is the first focusable element and skips to `#main`

---

## 11. prefers-reduced-motion

- [ ] Enable "Reduce Motion" in OS settings → "Pause Animations" auto-enables on first load
- [ ] Widget panel open/close animation is instant when prefers-reduced-motion is active
- [ ] All page transitions/animations stop

---

## 12. prefers-color-scheme (informational)

- [ ] Plugin does not automatically apply contrast modes based on OS dark-mode setting (user must activate manually)

---

## 13. Performance

- [ ] Total CSS + JS (gzipped) < 50 KB
- [ ] No render-blocking assets
- [ ] JS loads with `defer`
- [ ] Early inline script in `<head>` is < 1 KB
- [ ] No console errors or warnings on clean install

---

## 14. Browser Compatibility

| Browser | Version | Pass? |
|---------|---------|-------|
| Chrome  | Latest  |       |
| Chrome  | Previous|       |
| Edge    | Latest  |       |
| Firefox | Latest  |       |
| Firefox | Previous|       |
| Safari  | Latest  |       |
| Safari  | Previous|       |
| iOS Safari | Latest |     |
| Android Chrome | Latest |  |

- [ ] IE 11: widget is hidden silently (CSS `@media all and (-ms-high-contrast)` rule), no JS errors

---

## 15. Security Checks

- [ ] No `innerHTML` usage with user-controlled input in JS
- [ ] No `eval()` in JS
- [ ] All admin form fields use nonces
- [ ] All output in PHP uses appropriate escaping (`esc_html`, `esc_attr`, `esc_url`)
- [ ] Settings sanitized on save (verify via browser devtools / DB inspection)
- [ ] No external network requests (verify in browser Network tab)
