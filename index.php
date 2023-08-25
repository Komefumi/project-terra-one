<?php
/*
  Plugin Name: Project Terra One
  Description: First Plugin
  Version: 0.1
  Author: Vecktor [komefumi]
  Author URI: komefumi.github.io
  Text Domain: terraone_translation_domain
  Domain Path: /languages
*/

class Vecktor_TerraOnePlugin
{
  private string $setting_prefix = "terraone__";
  private string $setting_error_suffix = "__error";
  private string $domain_for_translations = "terraone_translation_domain";
  private string $setting_section_default = 'terraone__first_section';
  private string $setting_slug = 'word-count-settings';
  private string $setting_group = 'word-count-plugin';
  private array $setting_base_names = [
    'location',
    'headline',
    'word_count',
    'character_count',
    'read_time',
  ];
  private array $setting_name_to_data = array(
    "location" => array(
      'default_val' => '0',
      'display_name' => 'Display Location',
      'display_fn' => 'location_html',
      'sanitize_fn' => 'sanitize_location',
      'pass_data_to_display_fn' => false,
      'validation_error' => 'Display location must be beginning or end',
    ),
    'headline' => array(
      'default_val' => 'Post Statistics',
      'display_name' => 'Headline Text',
      'display_fn' => 'headline_html',
      'sanitize_fn' => 'default', // 'default' is checked for and handled specially
      'pass_data_to_display_fn' => false,
      'validation_error' => 'Headline must be valid text',
    ),
    'word_count' => array(
      'default_val' => '1',
      'display_name' => 'Word Count',
      'display_fn' => 'checkbox_html',
      'sanitize_fn' => 'sanitize_word_count',
      'pass_data_to_display_fn' => true,
      'validation_error' => 'Checkbox values can only be checked or unchecked',
    ),
    'character_count' => array(
      'default_val' => '0',
      'display_name' => 'Character Count',
      'display_fn' => 'checkbox_html',
      'sanitize_fn' => 'sanitize_character_count',
      'pass_data_to_display_fn' => true,
      'validation_error' => 'Checkbox values can only be checked or unchecked',
    ),
    'read_time' => array(
      'default_val' => '0',
      'display_name' => 'Read Time',
      'display_fn' => 'checkbox_html',
      'sanitize_fn' => 'sanitize_read_time',
      'pass_data_to_display_fn' => true,
      'validation_error' => 'Checkbox values can only be checked or unchecked',
    )
  );
  function __construct()
  {
    add_action('admin_menu', array($this, 'admin_menu_option'));
    add_action('admin_init', array($this, 'settings'));
    add_filter('the_content', array($this, 'if_wrap'));
    add_action('init', array($this, 'languages'));
  }

  function languages()
  {
    load_plugin_textdomain(
      "terraone_translation_domain",
      false,
      dirname(plugin_basename(__FILE__)) . '/languages'
    );
  }

  private function get_setting_error_name(string $base_name)
  {
    $setting_name = $this->get_full_setting_name($base_name);
    return "$setting_name$this->setting_error_suffix";
  }

  private function get_full_setting_name(string $base_name)
  {
    return "$this->setting_prefix$base_name";
  }

  function if_wrap($content)
  {
    $has_settings_needed_for_showing = count(array_filter(array_slice($this->setting_base_names, 2), function ($base_name) {
      $setting_name = $this->get_full_setting_name($base_name);
      /*
       * After user just installs the plugin, options might not be set.
       * So we need to give default value as second parameter
       */
      return get_option($setting_name, '1');
    })) > 0;

    if (is_main_query() and is_single() and $has_settings_needed_for_showing) {
      return $this->create_html($content);
    }

    return $content;
  }

  function create_html(string $content)
  {
    $setting_values = array_reduce(array_slice($this->setting_base_names, 0), function ($mapping, $base_name) {
      $setting_name = $this->get_full_setting_name($base_name);
      list('default_val' => $default_val) = $this->setting_name_to_data[$base_name];
      $mapping[$base_name] = get_option($setting_name, $default_val);
      return $mapping;
    }, array());
    $has_word_count = $setting_values['word_count'] == '1';
    $has_character_count = $setting_values['character_count'] == '1';
    $has_read_time = $setting_values['read_time'] == '1';
    $word_count =
      ($has_word_count or $has_read_time)
      ? str_word_count(strip_tags($content))
      : null;
    $character_count = $has_character_count ? strlen(strip_tags($content)) : null;
    $read_time = $has_read_time ? round($word_count / 225, 1) : null;

    $html = '';
    $headline = esc_html($setting_values['headline']);
    $translated__this_post_has = esc_html__("This post has", "terraone_translation_domain");
    $additional_html = <<< HTML
      <div>
        <h3>$headline</h3>
      </div>
      <p>
      <!-- <div>
        $content
      </div> -->
    HTML;
    if ($has_word_count) {
      $additional_html .= "$translated__this_post_has" . " $word_count "
        . esc_html__("words", "terraone_translation_domain") . ".<br/>";
    }
    if ($has_character_count) {
      $additional_html .= "$translated__this_post_has" . " $character_count "
        . esc_html__("characters", "terraone_translation_domain") . ".<br/>";
    }
    if ($has_read_time) {
      $additional_html .= esc_html__("Read time", "terraone_translation_domain") . ": $read_time "
        . esc_html__("minute(s)", "terraone_translation_domain") . ".<br/>";
    }
    $additional_html .= '</p>';
    if ($setting_values['location'] == '0') {
      $html = $additional_html . $content;
    } else {
      $html = $content . $additional_html;
    }
    return $html;
  }

  function settings()
  {
    add_settings_section(
      $this->setting_section_default,
      null,
      function () {
      },
      $this->setting_slug
    );
    foreach ($this->setting_base_names as $base_name) {
      list(
        'display_name' => $display_name,
        'default_val' => $default_val,
        'display_fn' => $display_fn,
        'sanitize_fn' => $sanitize_fn,
        'pass_data_to_display_fn' => $pass_data_to_display_fn,
      ) = $this->setting_name_to_data[$base_name];
      $full_setting_name = $this->get_full_setting_name($base_name);
      add_settings_field(
        $full_setting_name,
        $display_name,
        array($this, $display_fn),
        $this->setting_slug,
        $this->setting_section_default,
        $pass_data_to_display_fn ? array($base_name) : array(),
      );

      register_setting(
        $this->setting_group,
        $full_setting_name,
        array('sanitize_callback' => $sanitize_fn == 'default' ? 'sanitize_text_field' : array($this, $sanitize_fn), 'default' => $default_val),
      );
    }
  }

  function sanitize_location(mixed $input)
  {
    return $this->sanitize_on_and_off($input, 0);
  }

  function sanitize_word_count(mixed $input)
  {
    return $this->sanitize_on_and_off($input, 2, true);
  }

  function sanitize_character_count(mixed $input)
  {
    return $this->sanitize_on_and_off($input, 3, true);
  }

  function sanitize_read_time(mixed $input)
  {
    return $this->sanitize_on_and_off($input, 4, true);
  }

  function sanitize_on_and_off(mixed $input, string $base_name_index, $allow_empty = false)
  {
    $base_name = $this->setting_base_names[$base_name_index];
    $setting_name = $this->get_full_setting_name($base_name);
    if ($allow_empty and !$input) {
      if ($base_name_index > 1) return '0';
      return get_option($setting_name);
    }
    if (!in_array($input, array('0', '1'))) {
      $error_name = $this->get_setting_error_name($base_name);
      list('validation_error' => $validation_error) = $this->setting_name_to_data[$base_name];
      add_settings_error($setting_name, $error_name, $validation_error);
      return get_option($setting_name);
    }

    return $input;
  }

  function location_html()
  {
    $setting_name = $this->get_full_setting_name($this->setting_base_names[0]);
?>
    <select name="<? echo $setting_name ?>">
      <option value="0" <? selected(get_option($setting_name), '0'); ?>>Beginning of Post</option>
      <option value="1" <? selected(get_option($setting_name), '1'); ?>>End of Post</option>
    </select>
  <?php }

  function headline_html()
  {
    $base_name = $this->setting_base_names[1];
    $setting_name = $this->get_full_setting_name($base_name);
  ?>
    <input name="<?php echo $setting_name ?>" value="<? echo $this->safely_get_option($base_name); ?>" />
  <?php }

  function checkbox_html(array $args)
  {
    $base_name = $args[0];
    $setting_name = $this->get_full_setting_name($base_name);
  ?>
    <input type="checkbox" name="<?php echo $setting_name ?>" value="1" <?php checked(get_option($setting_name), '1') ?> />
  <?php }

  function safely_get_option($base_name)
  {
    $setting_name = $this->get_full_setting_name($base_name);
    list('default_val' => $default_val) = $this->setting_name_to_data[$base_name];
    $current_value = get_option($setting_name, $default_val);
    return esc_attr($current_value);
  }

  function admin_menu_option()
  {
    $required_capability_as_permission = 'manage_options';
    add_options_page(
      'Word Count Settings',
      esc_html__('Word Count', "terraone_translation_domain"),
      // 'Word Count',
      $required_capability_as_permission,
      $this->setting_slug,
      array($this, 'settings_page'),
    );
  }

  function settings_page()
  { ?>
    <div class="wrap">
      <h1>Word Count Settings</h1>
      <form action="options.php" method="post">
        <?php
        settings_fields($this->setting_group);
        do_settings_sections($this->setting_slug);
        submit_button();
        ?>
      </form>
    </div>
<?php }
}

$terraOnePlugin = new Vecktor_TerraOnePlugin();
