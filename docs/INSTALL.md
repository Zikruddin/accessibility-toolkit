# D2i Accessibility Toolkit — Installation Guide

## Requirements

| Requirement | Minimum |
|-------------|---------|
| WordPress   | 6.0     |
| PHP         | 7.4     |
| Browser     | Chrome, Edge, Firefox, Safari (last 2 major versions) |

## Installation Steps

### Via WordPress Admin (recommended)

1. Download the plugin ZIP file.
2. In your WordPress admin panel, go to **Plugins → Add New → Upload Plugin**.
3. Select the ZIP file and click **Install Now**.
4. Click **Activate Plugin**.

### Via FTP / SFTP

1. Extract the ZIP file to get the `d2i-accessibility-toolkit` folder.
2. Upload the folder to `/wp-content/plugins/` on your server.
3. In your WordPress admin panel, go to **Plugins → Installed Plugins**.
4. Find **D2i Accessibility Toolkit** and click **Activate**.

## Post-Installation: Add OpenDyslexic Fonts

The Dyslexia Friendly feature requires the OpenDyslexic font files (open-source, OFL licence).

1. Visit [https://opendyslexic.org](https://opendyslexic.org) and download the font.
2. Locate the following files from the download:
   - `OpenDyslexic-Regular.woff2`
   - `OpenDyslexic-Bold.woff2`
3. Place both files in:
   ```
   wp-content/plugins/d2i-accessibility-toolkit/public/fonts/
   ```

If the font files are absent, the Dyslexia Friendly feature will gracefully fall back to **Comic Sans MS** then **Arial**. The feature still works without the fonts; it just uses the fallback.

## Configuration

1. Go to **Settings → D2i Accessibility** in your WordPress admin.
2. Configure:
   - **Widget Position** — where the floating button appears on screen
   - **Primary Color** — brand color for the trigger button (default: D2i blue `#003366`)
   - **Trigger Icon** — choose from 4 bundled SVG icons
   - **Trigger Size** — small (48 px), medium (56 px), or large (64 px)
   - **Enabled Features** — hide individual features from the widget panel
   - **Show "Powered by" Credit** — toggle the D2i Technology footer credit
   - **Auto-open on First Visit** — panel opens automatically for new visitors
   - **Show Widget On** — all pages or specific pages
   - **Disable on Admin/Login** — recommended ON
   - **Skip-to-Content Link** — inject a skip link if your theme lacks one
3. Click **Save Settings**.

## Generate an Accessibility Statement Page

1. Go to **Settings → D2i Accessibility → Statement Generator** tab.
2. Fill in your organisation name, conformance status, and contact email.
3. Click **Generate Accessibility Statement Page**.
4. The plugin creates a WordPress page at `/accessibility-statement/` and links it in the widget footer.
5. Edit the page content in the WordPress editor to match your actual conformance status.

## Uninstalling

1. Deactivate the plugin via **Plugins → Installed Plugins**.
2. Click **Delete** to remove the plugin files.
3. The plugin's options (`d2i_a11y_settings`) will be removed automatically from the database.
4. The Accessibility Statement page is **not deleted** — you manage it manually.
