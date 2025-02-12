<?php

/**
 * @package NRW
 * @version 1.0.0
 */
/*
Plugin Name: Veter NRW Plugin
Plugin URI: http://wordpress.org/plugins/hello-dolly/
Description: This is not just a plugin, it symbolizes the hope and enthusiasm of an entire generation summed up in two words sung most famously by Louis Armstrong: Hello, Dolly. When activated you will randomly see a lyric from <cite>Hello, Dolly</cite> in the upper right of your admin screen on every page.
Author: Ivan Matcuka
Version: 1.0.0
Author URI: https://github.com/ivanmatcuka
*/

namespace VeterNRWPlugin;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use buzzingpixel\twigswitch\SwitchTwigExtension;

defined('ABSPATH') or die();

require(__DIR__ . '/vendor/autoload.php');

const PLUGIN_SETTINGS = 'veter-plugin-settings';

class VeterNRWPlugin
{
  const FIELDS = [
    'API Keys' => [
      'api_chat_gpt' => [
        'type' => 'text',
        'label' => 'API ChatGPT',
      ],
      'api_claude' => [
        'type' => 'text',
        'label' => 'API Claude',
      ]
    ],
    'Model' => [
      'default_model' => [
        'type' => 'select',
        'label' => 'Default Model',
        'options' => [
          'Chat GPT',
          'Claude',
        ],
      ]
    ],
    'Morning Text' => [
      'morning_text_header' => [
        'type' => 'text',
        'label' => 'Header',
      ],
      'morning_text_before' => [
        'type' => 'text',
        'label' => 'Before',
      ],
      'morning_text_block_header' => [
        'type' => 'text',
        'label' => 'Block Header',
      ],
      'morning_text_after' => [
        'type' => 'text',
        'label' => 'After',
      ]
    ],
    'Evening Text' => [
      'evening_text_header' => [
        'type' => 'text',
        'label' => 'Header',
      ],
      'evening_text_before' => [
        'type' => 'text',
        'label' => 'Before',
      ],
      'evening_text_block_header' => [
        'type' => 'text',
        'label' => 'Block Header',
      ],
      'evening_text_after' => [
        'type' => 'text',
        'label' => 'After',
      ],
    ],
    'Prompts' => [
      'news_prompt' => [
        'type' => 'textarea',
        'label' => 'News Prompt',
      ],
      'news_header_prompt' => [
        'type' => 'textarea',
        'label' => 'News Header Prompt',
      ],
      'morning_prompt' => [
        'type' => 'textarea',
        'label' => 'Morning Prompt',
      ],
      'weather_morning_prompt' => [
        'type' => 'textarea',
        'label' => 'Weather Morning Prompt',
      ],
      'evening_prompt' => [
        'type' => 'textarea',
        'label' => 'Evening Prompt',
      ],
      'weather_evening_prompt' => [
        'type' => 'textarea',
        'label' => 'Weather Evening Prompt',
      ],
    ]
  ];

  protected Environment $twig;

  public function __construct()
  {
    $this->twig = new Environment(new FilesystemLoader(__DIR__ . '/templates'), [
      'cache' => false,
    ]);
    $this->twig->addExtension(new SwitchTwigExtension());
  }

  public function load()
  {
    add_action('rest_api_init', [$this, 'register_api_routes']);
    add_action('admin_menu', [$this, 'add_plugin_options_page']);
    add_action('admin_init', [$this, 'add_plugin_settings']);
  }

  public function add_plugin_options_page()
  {
    add_options_page(
      'Veter NRW Plugin Settings',
      'Veter NRW Plugin Settings',
      'manage_options',
      'veter',
      [$this, 'render_admin_page'],
    );
  }

  public function render_admin_page()
  {
    // TODO: refactor
    ob_start();
    settings_fields(PLUGIN_SETTINGS);
    do_settings_sections(PLUGIN_SETTINGS);
    submit_button();
    $output = ob_get_contents();
    ob_end_clean();

    echo $this->twig->render('settings.twig', [
      'output' => $output,
    ]);
  }

  public function render_field($field, $key)
  {
    try {
      $value = get_option($key, $field['value']);
      echo $this->twig->render('field.twig', [
        'field' => $field,
        'value' => $value,
        'key' => $key,
      ]);
    } catch (\Exception $e) {
      echo $e->getMessage();
    }
  }

  public function add_field($field, $key, $section)
  {
    $renderer = function () use ($field, $key) {
      $this->render_field($field, $key);
    };

    add_settings_field(
      $key,
      $field['label'],
      $renderer,
      PLUGIN_SETTINGS,
      $section,
    );
  }

  public function add_fields($fields, $section)
  {
    foreach ($fields as $key => $field) {
      register_setting(PLUGIN_SETTINGS, $key);
      $this->add_field($field, $key, $section);
    }
  }


  public function add_plugin_settings()
  {
    foreach (self::FIELDS as $section => $fields) {
      add_settings_section(
        $section,
        $section,
        function () use ($section) {
          echo "<p>Settings for {$section}</p>";
        },
        PLUGIN_SETTINGS
      );

      $this->add_fields($fields, $section);
    }
  }

  public function register_api_routes()
  {
    register_rest_route('veter-nrw-plugin/v1', '/settings', [
      'methods' => 'GET',
      'callback' => [$this, 'get_settings'],
      'permission_callback' => '__return_true'
    ]);
  }

  public function get_settings()
  {
    $settings = [];

    foreach (self::FIELDS as $_ => $fields) {
      foreach ($fields as $key => $field) {
        $settings[$key] = get_option($key, $field['value']);
      }
    }

    return rest_ensure_response($settings);
  }
}

$plugin = new VeterNRWPlugin();
$plugin->load();
