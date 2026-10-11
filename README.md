# PDFlip – PDF flip books for TYPO3

PDFlip shows PDF files as page-turning flip books in the TYPO3 frontend: embedded in the page,
as a gallery that opens the books in a lightbox, or as the target of a link.

It integrates the open-source library [pdflip](https://github.com/WapplerSystems/pdflip)
(BSD-3-Clause, pdf.js, no jQuery, no WebGL). The library is bundled in
`Resources/Public/Pdflip/`; nothing has to be installed separately.

## Features

* Realistic page turn (soft paper, hard covers or hard pages), single and double pages, right-to-left books
* Zoom with sharp re-rendering, pan, pinch, keyboard control
* Thumbnails, table of contents of the PDF, full-text search with highlights, selectable text
* Clickable links of the PDF, deep links to pages, share, download, fullscreen, autoplay
* Accessible controls (buttons with labels, keyboard, focus handling in the lightbox)
* Works with TYPO3's Content Security Policy: no inline scripts, nonce on the script tag
* pdf.js is loaded only when a book comes into view

## Requirements

| PDFlip | TYPO3 | PHP |
|---|---|---|
| 13.1 (`release/v13-os`) | 13.4 and 14 | ≥ 8.2 |
| 14.0 (`release/v14`) | 14 | ≥ 8.2 |

The content elements are based on `lib.contentElement`, so **fluid_styled_content** is required.

## Installation

```bash
composer require wapplersystems/pdflip
```

Add the site set **PDF Flip Book** (`wapplersystems/pdflip`) to your site — in the backend under
*Site Management › Sites › Edit site › Sets*, or in the site configuration:

```yaml
dependencies:
  - wapplersystems/pdflip
```

## Usage

**PDF flip book: embedded** — select one PDF on the *Media* tab. The *Flip book* tab sets the
height (empty = derived from width and page format), start page, page mode, page turn,
reading direction, sound, the panel to open on start and controls to hide. Empty values use
the site settings.

**PDF flip book: gallery** — select several PDFs. Each is shown with its cover (file metadata
*Title* as caption, otherwise the file name); a click opens the book in a lightbox.

**Link type "Pdflip"** — in the link browser, choose the *Pdflip* tab and a PDF. The link opens
the current page with the page type `68724`, which shows only the book, filling the window.
If your site has a `PageType` route enhancer, map a suffix for it, e.g. `flipbook: 68724`.

## Configuration

Site settings (*Site Management › Settings › PDF Flip Book*):

| Setting | Default | |
|---|---|---|
| `pdflip.pageMode` | `auto` | `auto`, `single`, `double` |
| `pdflip.flipStyle` | `soft` | `soft`, `cover` (hard covers), `hard` |
| `pdflip.direction` | `ltr` | `ltr`, `rtl` |
| `pdflip.controls` | all | Toolbar controls, comma-separated |
| `pdflip.controlsPosition` | `bottom` | `bottom`, `top`, `hidden` |
| `pdflip.sound` | off | Page turn sound |
| `pdflip.wheel` | `zoom` | Mouse wheel: `zoom` (with Ctrl, always in fullscreen/lightbox), `flip`, `none` |
| `pdflip.theme` | `light` | Toolbar theme: `light`, `dark` |
| `pdflip.galleryCovers` | `server` | Gallery covers rendered by TYPO3 (`server`, needs GraphicsMagick/ImageMagick with PDF support) or by pdf.js in the browser (`client`) |
| `pdflip.galleryThumbnailWidth` | `300` | Width of the gallery covers in px |

Colours and sizes are CSS custom properties (`--pdflip-accent`, `--pdflip-toolbar-background`,
…), see the [library README](https://github.com/WapplerSystems/pdflip#theming).

### Own templates

```typoscript
tt_content.pdflip-embedded.templateRootPaths.20 = EXT:my_site/Resources/Private/Pdflip/
tt_content.pdflip-gallery.templateRootPaths.20 = EXT:my_site/Resources/Private/Pdflip/
```

The templates only need an element with `data-pdflip="<pdf url>"` (embedded) or
`data-pdflip-lightbox` on a link to the PDF; options go into `data-pdflip-options` as JSON
(`{pdflipOptions}`) or into single `data-pdflip-*` attributes.

### Content Security Policy

The extension adds `'wasm-unsafe-eval'` to `script-src` in the frontend; pdf.js needs it to
decode JBIG2 and JPEG 2000 images. No `'unsafe-eval'` and no inline scripts are required.
PDFs from another domain need CORS headers on that domain and an entry in `connect-src`.

## Upgrading from the dFlip-based versions

The content types and the height setting are unchanged, existing content elements keep working.
Removed: the TypoScript constants `importJQuery` and `useNonce` (jQuery is no longer needed,
the nonce is always set) and the old `plugin.tx_pdflip.view.*` constants (use the TypoScript
above). The `Configuration/TypoScript` folder is gone — include the site set instead.

## Updating the bundled library

```bash
Build/sync-pdflip.sh                # latest main of WapplerSystems/pdflip
Build/sync-pdflip.sh ../pdflip      # a local checkout
```

## License

GPL-2.0-or-later. Developed by [WapplerSystems](https://wappler.systems).
The bundled pdflip library is BSD-3-Clause, pdf.js Apache-2.0 (see `Resources/Public/Pdflip/`).

Issues and pull requests: https://github.com/WapplerSystems/t3-pdflip/issues
