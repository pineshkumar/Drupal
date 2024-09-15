<?php

namespace Drupal\custom_views_formatter\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Plugin implementation of the FieldIsEmptyFormatter formatter.
 *
 * @FieldFormatter(
 *   id = "field_is_empty_formatter",
 *   module = "custom_views_formatter",
 *   label = @Translation("Is field empty?"),
 *   field_types = {}
 * )
 */
class FieldIsEmptyFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $settings = [];
    $settings['negate'] = FALSE;
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $form['negate'] = [
      '#title' => $this->t('Negate.'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('negate'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $result = $items->isEmpty();

    if ($this->getSetting('negate')) {
      $result = !$result;
    }

    $elements = [
      '#is_multiple' => FALSE,
      [
        '#plain_text' => (int) $result,
      ],
    ];

    return $elements;
  }

}

