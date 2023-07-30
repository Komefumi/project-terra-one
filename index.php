<?php
/*
  Plugin Name: Project Terra One
  Description: First Plugin
  Version: 0.1
  Author: Vecktor [komefumi]
  Author URI: komefumi.github.io
*/

class Vecktor_WordCountAndTimePlugin
{
  function __construct()
  {
    add_action('admin_menu', array($this, 'add_admin_page'));
  }

  function add_admin_page()
  {
    add_options_page('Word Count Settings', 'Word Count', 'manage_options', 'word-count-settings-page', array($this, 'output_admin_page_html'));
  }

  function output_admin_page_html()
  { ?>
    <div>Hello World from our new Plugin</div>
    <h2>Hello</h2>
<?php }
}

$wordCountAndTimePlugin = new Vecktor_WordCountAndTimePlugin();
