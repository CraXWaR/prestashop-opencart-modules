# InquiryBox — Module Documentation

**Version:** 1.0.0
**Author:** Emanuil
**Compatibility:** PrestaShop 1.7 – 9.x

---

## What it does

InquiryBox adds a dedicated public page where visitors can submit inquiries through a form. Submitted inquiries are stored in the database and can be reviewed, managed, and replied to by an administrator from the Back Office.

---

## Installation

1. Upload the `inquirybox/` folder to `modules/`
2. Go to **Back Office → Modules** and install **Inquiry Box**
3. The module automatically creates the required database tables during installation.

---

## Configuration

Found at **Back Office → Modules → Inquiry Box → Configure**

| Setting             | Description                                                            |
| ------------------- | ---------------------------------------------------------------------- |
| Email Notifications | Enable or disable email notifications when a new inquiry is submitted. |

---

## Inquiry Workflow

```
Visitor submits inquiry
        ↓
Form is validated and saved
        ↓
Administrator reviews the inquiry
        ↓
Administrator responds or marks it as handled
        ↓
Inquiry remains available for future reference
```

---

## Public Page

**URL:** `/module/inquirybox/page`

Visitors can submit inquiries using the contact form. After a successful submission, they are redirected back to the page with a confirmation message. Invalid submissions display an appropriate error message.

---

## Features

* Dedicated inquiry submission page
* Secure form validation
* Database storage of inquiries
* Back Office management interface
* Administrator replies and status management
* Optional email notifications
* Compatible with PrestaShop 1.7 through 9.x
