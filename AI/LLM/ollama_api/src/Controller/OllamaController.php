<?php

namespace Drupal\ollama_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class OllamaController extends ControllerBase {

  /**
   * Generate a response from the Ollama API.
   */
  public function generateResponse(Request $request) {
    $api_key = 'YOUR_API_KEY'; // Replace with your actual API key.
    $model = 'llama3'; // Use the model you want to call.
    $prompts = ['Drupal CMS latest version?', 'How to install Drupal?']; // An array of prompts to send.

    // Prepare an array to hold all responses.
    $responses = [];

    // Iterate over prompts and send requests.
    foreach ($prompts as $prompt) {
      $data = [
        'model' => $model,
        'prompt' => $prompt,
      ];
      $response = $this->callOllamaApi($api_key, $data);
      $responses[] = $response;
    }

    return new JsonResponse($responses);
  }

  /**
   * Call the Ollama API.
   *
   * @param string $api_key
   *   The API key.
   * @param array $data
   *   The data to send to the API.
   *
   * @return array
   *   The response from the API.
   */
  private function callOllamaApi($api_key, array $data) {
    $client = \Drupal::httpClient();
    $url = 'http://172.17.0.1:11436/api/generate'; // Use Docker host IP

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
          $combinedResponse .= $json['response'] ?? ''; // Combine responses

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
