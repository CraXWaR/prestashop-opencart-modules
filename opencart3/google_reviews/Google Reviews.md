# Google Reviews — Module Documentation

**Version:** 1.0.0
**Author:** Emanuil
**Compatibility:** OpenCart 3.x

---

## What it does

Google Reviews displays customer reviews from your Google Business Profile on your OpenCart store.

Reviews are synchronized from the Google Places API and stored locally in the database, allowing fast page loads and reducing API requests. The module provides extensive customization options for appearance and layout.

---

## Installation

1. Upload `google_reviews.ocmod.zip` via **Extensions → Installer**.
2. Install **Google Reviews** from **Extensions → Extensions → Modules**.
3. Enter your Google Place ID and API Key.
4. Synchronize your reviews.
5. Add the module to a store layout.

---

## Configuration

Found at **Extensions → Modules → Google Reviews**

| Setting          | Description                                                                |
| ---------------- | -------------------------------------------------------------------------- |
| Status           | Enable or disable the module.                                              |
| API Settings     | Configure your Google Place ID, API Key, and review cache lifetime.        |
| Display Settings | Choose how many reviews to display, minimum rating, and optional elements. |
| Layout & Colors  | Customize the module's appearance, layout, and responsive behavior.        |

---

## How it works

```text
Configure Google API credentials
          ↓
Sync reviews from Google Places API
          ↓
Reviews are stored in the local database
          ↓
Add the module to a store layout
          ↓
Customers see your Google reviews on the storefront
```

---

## Features

* Displays reviews from your Google Business Profile.
* Local database storage for improved performance.
* Manual review synchronization.
* Configurable review filtering and display options.
* Responsive layouts with optional slider.
* Fully customizable colors and styling.
* Compatible with OpenCart 3.x.
