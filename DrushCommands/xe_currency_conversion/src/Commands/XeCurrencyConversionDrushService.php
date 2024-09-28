<?php

namespace Drupal\xe_currency_conversion\Commands;

use Drupal\xe_currency_conversion\XeCurrencyConversionService;
use Drush\Commands\DrushCommands;

/**
 * Drush service for XE Currency Conversion module.
 */
class XeCurrencyConversionDrushService extends DrushCommands {

  /**
   * The XE Currency Conversion service.
   *
   * @var \Drupal\xe_currency_conversion\XeCurrencyConversionService
   */
  protected $xeCurrencyConversionService;

  /**
   * Constructs a new XeCurrencyConversionDrushService object.
   *
   * @param \Drupal\xe_currency_conversion\XeCurrencyConversionService $xeCurrencyConversionService
   *   The XE Currency Conversion service.
   */
  public function __construct(XeCurrencyConversionService $xeCurrencyConversionService) {
    $this->xeCurrencyConversionService = $xeCurrencyConversionService;
  }

  /**
   * Drush command callback for importing XE Currency Conversion data.
   *
   * @command xe-currency-import
   * @aliases xeci
   * @usage drush xe-currency-import
   *   Imports XE Currency Conversion data.
   */
  public function importData() {
    $this->output()->writeln('Importing currency conversion data...');

    // Call the importData method from the service.
    $this->xeCurrencyConversionService->importData();
    $this->output()->writeln('Data imported successfully.');
  }

}
