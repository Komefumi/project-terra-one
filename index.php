<?php
/*
  Plugin Name: Project Terra One
  Description: First Plugin
  Version: 0.1
  Author: Vecktor [komefumi]
  Author URI: komefumi.github.io
*/


class Vecktor_TerraOnePlugin
{
  private string $setting_prefix = "terraone__";
  private string $setting_section_default = 'terraone__first_section';
  private string $setting_slug = 'word-count-settings';
  private string $setting_group = 'word-count-plugin';
  private array $setting_base_names = [
    'location',
    'headline'
  ];
  private array $setting_name_to_data = array(
    'location' => array(
      'default_val' => 0,
      'display_name' => 'Display Location',
      'display_fn' => 'location_html',
    ),
    'headline' => array(
      'default_val' => 'Post Statistics',
      'display_name' => 'Headline Text',
      'display_fn' => 'headline_html',
    ),
  );
  function __construct()
  {
    add_action('admin_menu', array($this, 'admin_menu_option'));
    add_action('admin_init', array($this, 'settings'));
  }

  private function get_full_setting_name($base_name)
  {
    return "$this->setting_prefix$base_name";
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
        'display_fn' => $display_fn,
        'default_val' => $default_val,
      ) = $this->setting_name_to_data[$base_name];
      $full_setting_name = $this->get_full_setting_name($base_name);
      add_settings_field(
        $full_setting_name,
        $display_name,
        array($this, $display_fn),
        $this->setting_slug,
        $this->setting_section_default,
      );
      register_setting(
        $this->setting_group,
        $full_setting_name,
        array('sanitize_callback' => 'sanitize_text_field', 'default' => $default_val),
      );
    }
  }

  function location_html()
  {
    $setting_name = $this->get_full_setting_name($this->setting_base_names[0]);
?>
    <select name="<? echo $setting_name ?>">
      <option value="0" <? echo selected(get_option($setting_name), '0'); ?>>Beginning of Post</option>
      <option value="1" <? echo selected(get_option($setting_name), '1'); ?>>End of Post</option>
    </select>
  <?php }

  function headline_html()
  {
    $base_name = $this->setting_base_names[1];
    $setting_name = $this->get_full_setting_name($base_name);
  ?>
    <input name="<?php echo $setting_name ?>" value="<? echo $this->safely_get_option($base_name); ?>" />
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
      'Word Count',
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
