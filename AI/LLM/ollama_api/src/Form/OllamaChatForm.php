<?php

namespace Drupal\ollama_api\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Utility\Html;

class OllamaChatForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'ollama_chat_form';
  }

  /**
   * Build the chat form.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['chat_message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Chat Message'),
      '#description' => $this->t('Enter your message to chat with Ollama.'),
      '#required' => TRUE,
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Send'),
      '#ajax' => [
        'callback' => '::ajaxSubmitCallback',
        'wrapper' => 'ollama-chat-response-wrapper',
      ],
    ];

    // Placeholder to show the response.
    $form['response'] = [
      '#type' => 'markup',
      '#markup' => '<div id="ollama-chat-response-wrapper"></div>',
    ];

    return $form;
  }

  /**
   * AJAX callback to process the form submission.
   */
  public function ajaxSubmitCallback(array &$form, FormStateInterface $form_state) {
    $prompt = $form_state->getValue('chat_message');
    $responseArray = $this->callOllamaApi($prompt);
  
    // Check if the response contains an error or the actual response.
    if (isset($responseArray['error'])) {
      $response = Html::escape($responseArray['error']); // Escape error message for security.
    }
    else {
      $response = $responseArray['combined_response']; // The combined response string.
    }
  
    // Display the response with allowed HTML tags (e.g., <strong>, <em>, <p>).
    $form['response']['#markup'] = '<div id="ollama-chat-response-wrapper"><strong>Response:</strong> ' . $response . '</div>';
    $form['response']['#allowed_tags'] = ['strong', 'em', 'p', 'div', 'br', 'a']; // Add any other tags you need.
  
    return $form['response'];
  }  

  /**
   * Handle form submission (non-AJAX fallback).
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // We handle the logic via AJAX in this case.
  }

  /**
   * Call Ollama API to get a response.
   */
  private function callOllamaApi($prompt) {
    $client = \Drupal::httpClient();
    $api_key = 'YOUR_API_KEY';  // Replace with your actual API key
    $url = 'http://172.17.0.1:11436/api/generate';  // Ollama API endpoint

    $data = [
      'model' => 'llama3',
      'prompt' => $prompt,
    ];

    try {
      // Send the POST request
      $response = $client->post($url, [
        'headers' => [
          'Content-Type' => 'application/json',
          'Authorization' => 'Bearer ' . $api_key,
        ],
        'json' => $data,
        'timeout' => 300,
      ]);

      // Stream the response
      $stream = $response->getBody()->getContents();
      $responseParts = explode("\n", trim($stream)); // Split the stream by line

      $combinedResponse = ''; // Variable to hold the combined response

      // Collect valid JSON responses
      foreach ($responseParts as $part) {
        if ($part) {
          $json = json_decode($part, true);
          if (json_last_error() !== JSON_ERROR_NONE) {
            \Drupal::logger('ollama_api')->error('JSON decode error: @error', ['@error' => json_last_error_msg()]);
            return ['error' => 'Invalid JSON response'];
          }

          // Append to combined response
          $combinedResponse .= $json['response'] ?? 'No response received.'; // Combine responses

          // Check if the response indicates completion
          if (isset($json['done']) && $json['done'] === true) {
            break; // Exit the loop if done
          }
        }
      }

      return ['combined_response' => $combinedResponse]; // Return the combined responses
    } catch (\Exception $e) {
      \Drupal::logger('ollama_api')->error('Error: @error', ['@error' => $e->getMessage()]);
      return ['error' => $e->getMessage()];
    }
  }
}
