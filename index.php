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
  private string $setting_section_default = 'terraone__first_section';
  private string $setting_slug = 'word-count-settings';
  private string $setting_group = 'word-count-plugin';
  private array $setting_names;
  function __construct()
  {
    $this->setting_names = array_map(fn ($base_name) => "terraone__$base_name", ['location']);
    add_action('admin_menu', array($this, 'admin_menu_option'));
    add_action('admin_init', array($this, 'settings'));
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
    add_settings_field(
      $this->setting_names[0],
      'Display Location',
      array($this, 'location_html'),
      $this->setting_slug,
      $this->setting_section_default
    );
    register_setting(
      $this->setting_group,
      $this->setting_names[0],
      array('sanitize_callback' => 'sanitize_text_field', 'default' => 0),
    );
  }

  function location_html()
  { ?>
    <div>Location HTML</div>
    <select name="<? echo $this->setting_names[0] ?>" id=""></select>
  <?php }

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
        do_settings_sections($this->setting_slug);
        submit_button();
        ?>
      </form>
    </div>
<?php }
}

$terraOnePlugin = new Vecktor_TerraOnePlugin();
