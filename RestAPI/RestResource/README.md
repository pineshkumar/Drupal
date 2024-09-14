# How to Create a Custom REST Resource in Drupal 9/10

**Date**: March 30, 2024

## Introduction

Drupal 9/10 offers powerful core capabilities and a flexible architecture, making it easy to create custom REST resources. RESTful APIs are integral to modern web development, allowing for the integration of diverse applications and seamless communication between systems. 

This tutorial will guide you through creating a custom REST resource in Drupal 9/10, covering the necessary steps, folder structure, and code examples.

## Prerequisites

Before you proceed, ensure the following are in place:
1. **Drupal 9/10**: Installed and configured.
2. **Module Development Knowledge**: Basic understanding of how to create custom modules in Drupal.
3. **PHP and OOP**: Familiarity with PHP and object-oriented programming concepts.

## Step 1: Define the Custom Module

First, we need to create a custom module to house the custom REST resource. In this example, we'll name the module `custom_rest_resource`.

1. Navigate to the `modules/custom` directory in your Drupal installation:
    ```bash
    cd /path/to/your/drupal-installation/modules/custom
    ```
   
2. Create a new directory for your module:
    ```bash
    mkdir custom_rest_resource
    ```

## Step 2: Define the Custom REST Resource Plugin

Inside the `custom_rest_resource` directory, we will create the file that defines the REST resource plugin.

1. Create the necessary file structure:

    ```bash
    mkdir -p custom_rest_resource/src/Plugin/rest/resource
    ```

2. Create the `CustomRestResource.php` file inside the `resource` directory with the following content:

```php
<?php
namespace Drupal\custom_rest_resource\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;

/**
 * Provides a custom REST resource.
 *
 * @RestResource(
 *   id = "custom_rest_resource",
 *   label = @Translation("Custom REST Resource"),
 *   uri_paths = {
 *     "canonical" = "/custom-rest-endpoint"
 *   }
 * )
 */
class CustomRestResource extends ResourceBase {

  /**
   * Responds to GET requests.
   *
   * @return \Drupal\rest\ResourceResponse
   *   The response.
   */
  public function get() {
    $data = [
      'message' => 'Hello from custom REST resource!',
    ];

    return new ResourceResponse($data);
  }

}
```

## Step 3: Create the Module Info File

Next, create the module info file to define the module and make it discoverable by Drupal.

1. In the `custom_rest_resource` directory, create a file named `custom_rest_resource.info.yml` with the following content:

```yaml
name: 'Custom REST Resource'
type: module
description: 'Provides a custom REST resource.'
core_version_requirement: ^9 || ^10
package: Custom
dependencies:
  - rest
```
This .info.yml file provides essential metadata about the module, including its name, type, description, and dependencies. The core_version_requirement ensures the module is compatible with Drupal 9 and 10, and the module depends on Drupal's REST API module (rest).

## Step 4: Enable the Module
Now that the module is defined, it needs to be enabled within Drupal.

1. Navigate to the Extend page in your Drupal admin interface (/admin/modules).
2. Locate the Custom REST Resource module and enable it.

Alternatively, you can enable the module via Drush by running:

    ```bash
    drush en custom_rest_resource
    ```

## Step 5: Test the Custom REST Resource
With the module enabled, you can now test the custom REST resource by sending a GET request to the defined endpoint (/custom-rest-endpoint).

Using cURL:

    ```bash
    curl -X GET http://your-drupal-site.com/custom-rest-endpoint
    ```
This should return a JSON response similar to the following:

    ```bash
    {
      "message": "Hello from custom REST resource!"
    }
    ```
Alternatively, you can test the endpoint using tools like Postman or browser plugins like RESTClient.

## Step 6: Folder Structure
After completing the steps, your module folder structure should look like this:

Using cURL:

    ```bash
    curl -X GET http://your-drupal-site.com/custom-rest-endpoint
    ```
This should return a JSON response similar to the following:

    ```bash
	modules/
	└── custom/
	    └── custom_rest_resource/
		├── custom_rest_resource.info.yml
		├── src/
		│   └── Plugin/
		│       └── rest/
		│           └── resource/
		│               └── CustomRestResource.php

    ```

These steps provide clear guidance on enabling, testing, and verifying the custom REST resource in Drupal 9/10, with code and folder structure examples.

