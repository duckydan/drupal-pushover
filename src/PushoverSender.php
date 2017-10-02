<?php
namespace Drupal\pushover;

use GuzzleHttp\Exception\RequestException;

class PushoverSender {

  public static $url = 'https://api.pushover.net/1/messages.json';
  public static $sound_url = 'https://api.pushover.net/1/sounds.json';
  public $options = [];

  public function __construct() {
    $config = \Drupal::config('pushover.config')->getRawData();
    $this->options = [
      'method' => 'POST',
      'data' => [
        'token' => $config['api_key'],
        'user' => $config['user_key'],
        'sound' => $config['sound'],
        'message' => '',
        'title' => '',
      ],
    ];
    if (trim($config['devices']) !== '') {
      $this->options['data']['device'] = $config['devices'];
    }
  }

  public function sendNotification($title, $message, $url = NULL, $url_title = NULL, $sound = NULL) {
    $this->options['data']['title'] = (string) $title;
    $this->options['data']['message'] = (string) $message;
    if ($url) {
      $this->options['data']['url'] = $url;
    }
    if ($url_title) {
      $this->options['data']['url_title'] = (string) $url_title;
    }
    if ($sound) {
      $this->options['data']['sound'] = (string) $sound;
    }
    $this->send();
  }

  private function send() {
    $client = \Drupal::httpClient();
    $url = self::$url;
    $options['form_params'] = $this->options['data'];
    try {
      $response = $client->request('POST', $url, $options);
    }
    catch (RequestException $e) {
      watchdog_exception('pushover', $e);
    }
  }

  public function getSoundOptions() {
    $client = \Drupal::httpClient();
    try {
      $url = self::$sound_url . '?' . http_build_query(['token' => $this->options['data']['token']]);
      $response = $client->request('GET', $url);
      $sounds = json_decode((string) $response->getBody());
      return (array) $sounds->sounds;
    }
    catch (RequestException $e) {
      watchdog_exception('pushover', $e);
    }

    return FALSE;
  }
}