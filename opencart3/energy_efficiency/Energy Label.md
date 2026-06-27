# Energy Label — Module Documentation

**Version:** 1.0.0
**Author:** Emanuil
**Compatibility:** OpenCart 3.x

---

## What it does

Energy Label adds EU energy efficiency information to OpenCart products. It allows administrators to assign energy classes, upload official EU energy label PDFs, and attach product information sheets directly from the product edit page.

The module displays energy labels on product pages, adds energy class information to product listings, and creates dedicated landing pages for each energy class to improve navigation and SEO.

---

## Installation

1. Upload `energy_label.ocmod.zip` via **Extensions → Installer**.
2. Go to **Extensions → Modifications** and click **Refresh**.
3. Install **Energy Label** from **Extensions → Extensions → Modules**.
4. Enable the module.

---

## Configuration

Found at **Extensions → Modules → Energy Label**

| Setting         | Description                                                       |
| --------------- | ----------------------------------------------------------------- |
| Status          | Enable or disable the module.                                     |
| Energy Classes  | Configure the available energy efficiency classes.                |
| Display Options | Control where energy labels and classes appear on the storefront. |

---

## How it works

```text
Create or edit a product
          ↓
Assign an energy class
          ↓
Upload the EU energy label and product information sheet
          ↓
Save the product
          ↓
Energy information is displayed on product and category pages
```

---

## Features

* Adds an **Energy** tab to the product edit page.
* Supports multiple energy label types (Cooling, Heating, General).
* Uploads official EU energy label PDFs and product information sheets.
* Displays energy classes on product pages.
* Shows energy information in category and product listings.
* Automatically creates SEO-friendly pages for each energy class.
* Uses OpenCart events and OCMOD for seamless integration.
* Compatible with OpenCart 3.x.
