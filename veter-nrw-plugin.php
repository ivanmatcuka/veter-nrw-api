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

if (!defined("ABSPATH")) {
  exit;
}

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

  // Add our WP admin hooks.
  public function load()
  {
    add_action('admin_menu', [$this, 'add_plugin_options_page']);
    add_action('admin_init', [$this, 'add_plugin_settings']);
  }

  // Add our plugin's option page to the WP admin menu.
  public function add_plugin_options_page()
  {
    add_options_page(
      'Veter NRW Plugin Settings',
      'Veter NRW Plugin Settings',
      'manage_options',
      'veter',
      function () {
        $this->render_admin_page();
      }
    );
  }

  // Render our plugin's option page.
  public function render_admin_page()
  {
?>
    <div class="wrap options-general-php">
      <h1>Veter NRW Plugin Settings</h1>
      <form method="post" action="options.php">
        <?php
        settings_fields('veter-plugin-settings');
        do_settings_sections('veter-plugin-settings');
        submit_button();
        ?>
      </form>
    </div>
<?php
  }

  public function render_field($field, $key)
  {
    $value = get_option($key, $field['value']);

    switch ($field['type']) {
      case 'text':
        echo "<input type='text' id='{$key}' name='{$key}' value='{$value}' class='regular-text'>";

        break;
      case 'select':
        echo "<select id='{$key}' name='{$key}'>";
        foreach ($field['options'] as $option) {
          $selected = ($value === $option) ? 'selected' : '';
          echo "<option value='{$option}' {$selected}>{$option}</option>";
        }
        echo "</select>";

        break;
      case 'textarea':
        echo "<textarea id='{$key}' name='{$key}' rows='5' cols='50' class='large-text'>" . esc_textarea($value) . "</textarea>";

        break;
      default:
        echo "No input type specified";
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
      'veter-plugin-settings',
      $section,
    );
  }

  public function add_fields($fields, $section)
  {
    foreach ($fields as $key => $field) {
      register_setting('veter-plugin-settings', $key);
      $this->add_field($field, $key, $section);
    }
  }


  // Initialize our plugin's settings.
  public function add_plugin_settings()
  {
    // Register a new setting for each field
    foreach (self::FIELDS as $section => $fields) {
      add_settings_section(
        $section,
        $section,
        function () use ($section) {
          echo "<p>Settings for {$section}</p>";
        },
        'veter-plugin-settings'
      );

      $this->add_fields($fields, $section);
    }
  }
}

// Load our plugin within the WP admin dashboard.
if (is_admin()) {
  $plugin = new VeterNRWPlugin();
  $plugin->load();
}
