# Category FAQ — Module Documentation

**Version:** 1.0.0
**Author:** Emanuil
**Compatibility:** OpenCart 3.x

---

## What it does

Category FAQ adds a dedicated **FAQ tab** to every category in the OpenCart administration panel, allowing administrators to create frequently asked questions for each category.

The FAQs are displayed automatically on the corresponding category page, include structured data (FAQ Schema) for SEO, and support multiple languages.

---

## Installation

1. Upload `category_faq.ocmod.zip` via **Extensions → Installer**.
2. Go to **Extensions → Modifications** and click **Refresh**.
3. Install **Category FAQ** from **Extensions → Extensions → Modules**.
4. Enable the module.

---

## Configuration

Found at **Extensions → Modules → Category FAQ**

| Setting | Description                                         |
| ------- | --------------------------------------------------- |
| Status  | Enables or disables the Category FAQ functionality. |

---

## How it works

```text
Create FAQs for a category
          ↓
Questions and answers are saved with the category
          ↓
Visitors open the category page
          ↓
FAQ section is displayed below the category content
          ↓
FAQ Schema (JSON-LD) is added automatically for search engines
```

---

## Features

* Adds a dedicated **FAQ** tab to category editing.
* Supports multiple FAQs per category.
* Multilingual questions and answers.
* Configurable sort order for FAQs.
* Automatically displays FAQs on category pages.
* Generates **FAQPage** structured data (JSON-LD) for improved SEO.
* Hides the FAQ section on paginated category pages (page 2 and above).
* Automatically removes FAQs when a category is deleted.
* Compatible with OpenCart 3.x.
