---
created: 2026-02-09T19:35:00
title: Add date range for report generation
area: ui
files:
  - public/index.php:466-500
  - src/ApiController.php:42,219-310
  - public/export_pdf.php:6
---

## Problem

Currently, reports can only be generated for predefined periods (day, week, month) with a single date picker. Users need the ability to generate reports for any custom date range (start_date to end_date) for more flexible reporting needs.

## Solution

Add a "custom" period option with two date inputs (start_date, end_date) in the report UI (index.php:466-500). Modify ApiController.php:42 to accept start_date and end_date parameters, update the SQL query to filter by date range, and pass these dates to the PDF generation. Update the PDF period label to show the custom date range.
