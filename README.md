# PDFlip – PDF flip books for TYPO3

PDFlip shows PDF files as page-turning flip books in the TYPO3 frontend. The PDF is rendered
in the browser with pdf.js — there is no need to convert it into images beforehand.

It is the successor of [t3-flipbook](https://github.com/WapplerSystems/t3-flipbook)
(`wapplersystems/flipbook`).

## Features

* **Content element "PDFlip embedded"** – a flip book embedded directly in the page.
* **Content element "PDFlip gallery"** – a grid of PDF thumbnails, each opening its flip book in a lightbox.
* **Link type "Pdflip"** – link any text (RTE, link fields) to a PDF that opens as a full-page flip book.
* Sound on page turn, thumbnails, outline, zoom, fullscreen, download.
* Works with TYPO3's Content Security Policy: scripts are included with the CSP nonce.

## Requirements

| PDFlip | TYPO3 | PHP  | Branch        |
|--------|-------|------|---------------|
| 14.x   | 14    | ≥ 8.2 | `release/v14` |
| 13.x   | 13.4  | ≥ 8.2 | `release/v13` (not tagged yet, use `dev-release/v13`) |

The content elements are based on `lib.contentElement`, so **fluid_styled_content** (or a site
package providing `lib.contentElement`) is required. The flip book library needs **jQuery** — either
already on your site or loaded by the extension (see [Configuration](#configuration)).

## Installation

```bash
composer require wapplersystems/pdflip
```

Then add the site set **Pdflip** (`wapplersystems/pdflip`) as a dependency of your site — either in
the backend under *Site Management › Sites › Edit site › Sets*, or in your site package:

```yaml
# config/sites/<site>/config.yaml or Configuration/Sets/<YourSet>/config.yaml
dependencies:
  - wapplersystems/pdflip
```

The set provides the TypoScript for both content elements, the page type for the link type and the
Page TSconfig that adds the "Pdflip" tab to the link browser.

## Usage

### Embedded flip book

1. Add a content element of type **PDFlip embedded**.
2. On the *Media* tab, select exactly one PDF file.
3. Optionally set the **height** in pixels (default: 500).

### Gallery

1. Add a content element of type **PDFlip gallery**.
2. On the *Media* tab, select one or more PDF files.

Each PDF is shown as a thumbnail with its title (file metadata *Title*, falling back to the file
name). A click opens the flip book in a lightbox. The thumbnail is generated from the first page by
TYPO3's image processing, so GraphicsMagick/ImageMagick must be able to read PDFs.

### Link to a flip book

In the link browser, choose the **Pdflip** tab and select a PDF from the file list. The link is stored
as `t3://pdflip?uid=<sys_file uid>`.

In the frontend, the link points to the current page with the page type `68724` and the file uid as
parameter (`?type=68724&file=<uid>`). That page type renders nothing but the flip book in full size —
open it in a new window or in your own lightbox.

## Configuration

TypoScript constants (`plugin.tx_pdflip`):

| Constant | Default | Description |
|---|---|---|
| `settings.importJQuery` | `0` | Load the bundled jQuery. Set to `1` if your site does not include jQuery already. |
| `settings.useNonce` | `1` | Add the CSP nonce to the script tags. |
| `view.templateRootPath` | `EXT:pdflip/Resources/Private/Templates/` | Additional template path (priority 20). |
| `view.partialRootPath` | `EXT:pdflip/Resources/Private/Partials/` | Additional partial path (priority 20). |
| `view.layoutRootPath` | `EXT:pdflip/Resources/Private/Layouts/` | Additional layout path (priority 20). |

The full-page flip book of the link type (page type `68724`) always loads its own jQuery.

### Overriding templates

The content elements use the templates `PdflipEmbedded.html` and `PdflipGallery.html` and the
partial `PdflipAssets.html` (all script and style includes). Copy them into your site package and
point the `view.*RootPath` constants to it.

The flip book is initialised by the library from the markup: every element with the class
`_df_book` (embedded) or `_df_thumb` (lightbox) and a `source` attribute pointing to the PDF becomes
a flip book. Global defaults (sound file, image path, labels, WebGL off) are set by the ViewHelper
`{pdflip:flipbookGlobalConfig()}` on `DFLIP.defaults`; further library options can be set there in
your own JavaScript.

### Content Security Policy

The extension extends the frontend CSP (`Configuration/ContentSecurityPolicies.php`):

* `script-src 'unsafe-eval'` – required by the bundled flip book library,
* `img-src blob:` – the rendered PDF pages are displayed as blob images.

If your PDFs are served from another domain (CDN, object storage), add that domain to
`connect-src` in your site's `csp.yaml`, as pdf.js loads the file via XHR.

## Migrating from t3-flipbook

There is no automatic migration. Replace the old *Flipbook* plugin by a **PDFlip embedded**
content element and select the same PDF file. Flip books built from an image folder are not
supported — PDFlip works with PDF files only.

## Third-party libraries

PDFlip bundles

* [dFlip](https://dearhive.com) 1.7.36 by DearHive (flip book rendering),
* [pdf.js](https://mozilla.github.io/pdf.js/) by Mozilla (Apache-2.0),
* [three.js](https://threejs.org) (MIT),
* [jQuery](https://jquery.com) (MIT),
* [Themify Icons](https://themify.me/themify-icons).

## License

GPL-2.0-or-later. Developed by [WapplerSystems](https://wappler.systems).

Issues and pull requests: https://github.com/WapplerSystems/t3-pdflip/issues
