# D2i Accessibility Toolkit — WCAG Success Criteria Mapping

This table maps each widget feature to the WCAG Success Criteria (SCs) it helps users meet or work around limitations in a site's implementation.

> **Note:** "Helps users meet" means the feature provides a user-side adjustment that supports the SC. It does not mean the site automatically conforms to that SC — authoring responsibilities still apply.

---

## Widget Feature → WCAG SC Mapping

| Feature | Helps users address | WCAG SC | Level | Notes |
|---------|--------------------|---------|----|-------|
| **Contrast — Dark** | Insufficient contrast on bright pages | 1.4.3 Contrast (Minimum) | AA | Provides a dark-background alternative |
| **Contrast — Light** | Low contrast on dark pages | 1.4.3 Contrast (Minimum) | AA | Provides a light-background alternative |
| **Contrast — High Contrast** | Very low contrast; cognitive or visual impairment | 1.4.3 Contrast (Minimum), 1.4.6 Contrast (Enhanced) | AA / AAA | Pure black/white/yellow — exceeds 7:1 |
| **Contrast — Monochrome** | Reliance on color alone to convey info | 1.4.1 Use of Color | A | Removes color; useful to test site's color-independence |
| **Contrast — Invert** | Bright screens causing eye strain | 1.4.3 Contrast (Minimum) | AA | Inverts all colors including images (compensated) |
| **Highlight Links** | Links not visually distinct from body text | 1.4.1 Use of Color, 2.4.4 Link Purpose (In Context) | A | Adds outline, underline, background color to all links |
| **Bigger Text** | Small text size causing readability issues | 1.4.4 Resize Text | AA | Scales font-size on :root using rem propagation; up to 200 % without breaking layout |
| **Text Spacing** | Dense text spacing causing readability issues | 1.4.12 Text Spacing | AA | Applies WCAG 1.4.12 exact values exactly |
| **Pause Animations** | Animations causing distraction or seizure risk | 2.2.2 Pause, Stop, Hide | A | Pauses CSS animations and autoplay videos |
| **Pause Animations (auto)** | OS `prefers-reduced-motion` preference | 2.3.3 Animation from Interactions | AAA | Auto-activates when OS motion preference is set |
| **Hide Images** | Distracting or confusing images | 1.1.1 Non-text Content | A | Alt text is preserved as visible text; decorative images are hidden |
| **Dyslexia Friendly** | Reading difficulties with standard fonts | 1.4.8 Visual Presentation (guidance) | AAA | OpenDyslexic font, increased line-height and letter-spacing |
| **Big Cursor** | Difficulty locating or using standard cursor | 1.4.3 Contrast (Minimum) | AA | 64 px cursor with high-contrast outline |
| **Line Height** | Text too tightly spaced | 1.4.12 Text Spacing | AA | User can increase line-height independently of text spacing preset |
| **Text Alignment** | Right-aligned or centered text causing reading difficulty | 1.4.8 Visual Presentation | AAA | Justify intentionally excluded per WCAG 1.4.8 guidance |
| **Saturation** | Over-saturated color causing discomfort | 1.4.3 Contrast (Minimum) | AA | Low saturation can improve perceived contrast in some conditions |
| **Reset All** | Restoring defaults without site reload | — | — | Quality-of-life; clears LocalStorage |
| **Accessibility Statement link** | Users need to know status and contact | 3.2.6 Consistent Help | A (2.2) | Links to the site's WCAG conformance statement |

---

## WCAG 2.2 New SCs — Widget Compliance

The widget's own UI must conform to these new SCs:

| SC | Requirement | Implementation |
|----|-------------|----------------|
| 2.4.11 Focus Not Obscured (Minimum) | Focused component not fully hidden by sticky content | Widget panel has `z-index: 2147483647`; trigger has same; focus is never fully obscured |
| 2.4.12 Focus Not Obscured (Enhanced) | Focused component fully visible | Best effort — panel is always on top of page content |
| 2.5.8 Target Size (Minimum) | Interactive targets ≥ 24 × 24 CSS px | All tiles ≥ 88 × 88 px; trigger ≥ 48 px; close button 36 × 36 px with adequate spacing |
| 3.2.6 Consistent Help | Help mechanism in consistent location | Accessibility Statement link is always in panel footer (same location) |

---

## WCAG SCs Addressed by the Plugin Widget Itself

The widget's own UI is designed to conform to these SCs:

| SC | How |
|----|-----|
| 1.1.1 Non-text Content | All icons have `aria-hidden="true"`; buttons have accessible labels |
| 1.3.1 Info and Relationships | Semantic `<button>` elements; `role="dialog"`; headings in panel |
| 1.3.2 Meaningful Sequence | DOM order matches visual order |
| 1.3.3 Sensory Characteristics | No color-only state indication — text labels and icons both used |
| 1.4.1 Use of Color | Active state uses both color AND icon/label change |
| 1.4.3 Contrast (Minimum) | Widget UI ≥ 4.5:1 text contrast; ≥ 3:1 UI contrast |
| 1.4.4 Resize Text | Widget uses `em`/`rem` internally; scales with browser font size |
| 1.4.10 Reflow | Panel width adapts to 100 vw − 16 px on screens ≤ 480 px |
| 1.4.11 Non-text Contrast | Tile borders, icons ≥ 3:1 against background |
| 2.1.1 Keyboard | All controls keyboard operable |
| 2.1.2 No Keyboard Trap | Focus trap active while dialog is open; Escape always releases |
| 2.4.3 Focus Order | Focus moves logically: trigger → close → tiles → footer |
| 2.4.7 Focus Visible | `focus-visible` with 3 px ring; never `outline: none` |
| 2.4.11 Focus Not Obscured | `z-index: 2147483647` |
| 4.1.2 Name, Role, Value | All controls have `aria-label`, `aria-pressed`, `aria-expanded`, `aria-controls` |
| 4.1.3 Status Messages | State changes announced via `aria-live="polite"` region |

---

## Limitations

The following SCs require site-level content changes that the overlay cannot fix:

| SC | Why overlay cannot fix it |
|----|--------------------------|
| 1.1.1 Non-text Content (missing alt) | Widget cannot generate meaningful alt text for images it doesn't know the context of |
| 1.2.x Captions/Audio Description | Video captions must be authored |
| 1.3.1 Info and Relationships (broken HTML) | Semantic structure must be in the source HTML |
| 2.4.1 Bypass Blocks | Skip links must be in the page or injected — only injected if admin enables the option |
| 2.4.2 Page Titled | Page `<title>` must be set by the theme/CMS |
| 3.1.1 Language of Page | `lang` attribute must be on `<html>` in the source |
