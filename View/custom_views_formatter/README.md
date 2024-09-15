# Custom Views Formatter Module

The **Custom Views Formatter** module provides a custom field formatter that checks whether a field is empty. This formatter allows you to display `1` if a field is empty or `0` if it is not, and you can optionally negate the result.

## Features

- Custom field formatter to check if a field is empty.
- Option to negate the result (e.g., return `0` for empty and `1` for non-empty).
- Works with all field types.

## Requirements

- Drupal 9.x or 10.x or 11.x
- Field module enabled

## Installation

1. Download the `custom_views_formatter` module and place it in the `modules/custom/` directory of your Drupal installation.
2. Enable the module using one of the following methods:
   - Using Drush:  
     ```bash
     drush en custom_views_formatter
     ```
   - Via the Drupal UI: Navigate to **Extend** and search for "Custom Views Formatter," then enable it.

## Configuration

1. After enabling the module, you can apply the custom formatter to any field in your content types or views.
2. When editing the field in **Manage Display** or in **Views**, select the **Is field empty?** formatter from the list of available formatters.
3. Optionally, you can configure the formatter to **negate** the result:
   - Negate option: When enabled, the formatter will return `0` if the field is empty and `1` if it is not.

## Folder Structure

```plaintext
custom_views_formatter/
├── src/
│   ├── Plugin/
│   │   └── Field/
│   │       └── FieldFormatter/
│   │           └── FieldIsEmptyFormatter.php
├── custom_views_formatter.info.yml
├── custom_views_formatter.module
└── README.md
