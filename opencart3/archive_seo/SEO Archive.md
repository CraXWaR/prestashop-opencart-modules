# SEO Archive — Module Documentation

**Version:** 1.0.0
**Author:** Emanuil
**Compatibility:** OpenCart 3.0.3.8

---

## What it does

SEO Archive adds a third product status, **SEO Archive**, allowing products to be removed from category listings and search results while keeping their product pages accessible via direct URL.

Archived products cannot be purchased. Instead of the add-to-cart button, customers see a notice indicating that the product is no longer available.

---

## Installation

1. Upload `archive_seo.ocmod.zip` via **Extensions → Installer**.
2. Go to **Extensions → Modifications** and click **Refresh**.
3. Install the **SEO Archive** module from **Extensions → Modules**.
4. Enable the module.

---

## Configuration

Found at **Extensions → Modules → SEO Archive**

| Setting | Description                                        |
| ------- | -------------------------------------------------- |
| Status  | Enables or disables the SEO Archive functionality. |

---

## How it works

```text
Product status = SEO Archive
          ↓
Hidden from categories and search
          ↓
Product page remains accessible by URL
          ↓
Purchase controls are removed
          ↓
Customer sees an "Unavailable" notice
```

---

## Features

* Adds a **SEO Archive** product status.
* Preserves direct product URLs for SEO.
* Hides archived products from storefront listings.
* Prevents archived products from being purchased.
* Compatible with the default OpenCart theme and Journal 3.
