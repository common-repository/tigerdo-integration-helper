<?php

class tiger_integrationSettingsClass {

    private $page;

    /**
     * Construct
     */
    public function __construct($page) {
        $this->page = $page;
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
        add_action('admin_head', array($this, 'custom_colors'));
        add_action('admin_footer', array($this, 'tiger_integration_allow_edit'));
        add_filter('plugin_action_links', array($this, 'add_plugin_links'), 10, 4);
    }

    /**
     * Add Branding
     */
    public function custom_colors() {
        echo '<style type="text/css">
        .tiger_integration img#tiger_integration_logo{padding: 20px;background: #303030;border-radius: 5px;height: 70px;}
        .tiger_integration label{text-transform: capitalize;}
        .tiger_integration .enable-button.button{margin-top:10px;}
        .tiger_integration .dashicons.dashicons-no-alt{color:#c0392b;font-size: 25px;line-height: 30px;display:none;opacity:0;-webkit-transition: all 400ms ease-in-out;-moz-transition: all 400ms ease-in-out;-ms-transition: all 400ms ease-in-out;-o-transition: all 400ms ease-in-out;transition: all 400ms ease-in-out;}
        .tiger_integration  .dashicons.dashicons-yes{color:#27ae60;font-size: 25px;line-height: 30px;display:none;opacity:0;-webkit-transition: all 400ms ease-in-out;-moz-transition: all 400ms ease-in-out;-ms-transition: all 400ms ease-in-out;-o-transition: all 400ms ease-in-out;transition: all 400ms ease-in-out;}
        </style>';
    }

    public function add_plugin_links($links, $file) {
        $mylinks = array();
        if (basename($this->page) == basename($file)) {
            $mylinks = array('<a href="' . admin_url('options-general.php?page=tiger-integration-settings') . '">Settings</a>',);
        }
        return array_merge($links, $mylinks);
    }

    /**
     * Add options page
     */
    public function add_plugin_page() {
        add_options_page('Tiger Settings', 'Integrate tiger.do', 'manage_options', 'tiger-integration-settings', array($this, 'add_tiger_integration_settings'));
    }

    /**
     * Options page callback
     */
    public function add_tiger_integration_settings() {
        $this->options = get_option('tiger_integration_options');
        ?><div class="wrap tiger_integration"><h2>Tiger.do Settings</h2><?php screen_icon(); ?>

            <form method="post" action="options.php">
                <?php
                settings_fields('tiger_integration_group');
                do_settings_sections('tiger-integration-admin-settings');
                submit_button();
                ?>
            </form>
            <a href="https://www.tiger.do/" target="_blank"><img id="tiger_integration_logo" src="https://cdn.filepicker.io/api/file/n8UUSvJqRWauzcZKgaEi?cache=true"></a>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function page_init() {

        register_setting('tiger_integration_group', // Option group
                'tiger_integration_options', // Option name
                array($this, 'sanitize') // Sanitize
        );
        add_settings_section('tiger-integration-setting-section-id', // ID
                'Your integration code is unique. Do not share it with anyone.', // Title
                array($this, 'tiger_integration_show_info_section'), // Callback
                'tiger-integration-admin-settings'
                // Page
        );
        add_settings_field('tiger_integration_script', // ID
                'Your Integration code:', // Title
                array($this, 'tiger_integration_add_tiger_key'), // Callback
                'tiger-integration-admin-settings', // Page
                'tiger-integration-setting-section-id'
                // Section
        );
        add_settings_field('tiger_integration_priority', // ID
                'High Priority:', // Title
                array($this, 'tiger_integration_show_priority'), // Callback
                'tiger-integration-admin-settings', // Page
                'tiger-integration-setting-section-id'
                // Section
        );
        add_settings_field('tiger_integration_type', // ID
                'Show Tiger on:', // Title
                array($this, 'tiger_integration_add_tiger_settings'), // Callback
                'tiger-integration-admin-settings', // Page
                'tiger-integration-setting-section-id'
                // Section
        );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize($input) {
        $new_input = array();
        if (isset($input['tiger_integration_script']))
            $new_input['tiger_integration_script'] = htmlentities2(stripslashes($input['tiger_integration_script']));
        if (isset($input['tiger_integration_type']))
            $new_input['tiger_integration_type'] = $input['tiger_integration_type'];
        if (isset($input['priority']))
            $new_input['priority'] = $input['priority'];
        return $new_input;
    }

    public function tiger_integration_show_info_section() {
        Print "Once you enter an integration code, you should only change it if you have made modifications to the website name on your app and you recieve a new integration key via mail";
    }

    public function tiger_integration_show_priority() {
        $selected = isset($this->options['priority']) ? "checked" : "";
        echo '<ul>';
        echo '<li><input value="true" type="checkbox" name="tiger_integration_options[priority]" ' . $selected . '  id="highPriority"/><label for="highPriority">Load tiger before everything?</label></li>';
        echo '</ul>';
    }

    public function tiger_integration_add_tiger_key() {
        $value = isset($this->options['tiger_integration_script']) ? $this->options['tiger_integration_script'] : '';
        $value != '' ? $disabled = 'readonly="true"' : $disabled = '';
        echo '<textarea  name="tiger_integration_options[tiger_integration_script]" rows="10" cols="60" class="tiger_integration_script" ' . $disabled . ' >' . $value . '</textarea><span class="dashicons dashicons-yes"></span><span class="dashicons dashicons-no-alt"></span><br><input type="button" class="button enable-button button-secondary" value="';
        echo $value != '' ? 'Edit' : 'Check';
        echo '" />';
    }

    public function tiger_integration_allow_edit() {
        $value = isset($this->options['tiger_integration_script']) ? $this->options['tiger_integration_script'] : '';
        ?>
        <script type="text/javascript">
            tflag = '<?php echo $value != '' ? 1 : 0; ?>';
            hostname = '<?php echo get_site_url(); ?>';
            jQuery(document).ready(function ($) {
        <?php echo $value == '' ? '$(".tiger_integration input[type=\'submit\']").attr("readonly","true");' : ''; ?>
                $(".enable-button").on("click", function (e) {
                    e.preventDefault();
                    if (tflag == 1) {
                        if (confirm("Are you sure you want to edit the website key?\n\nTiger might not work if an invalid key is entered.\n\n")) {
                            $(".tiger_integration_script").removeAttr("readonly")
                            $(this).val("Check");
                            $(".tiger_integration input[type='submit']").attr("disabled", "true");
                            tflag = 0;
                        }
                    } else
                    {
                        var int_key = $(".tiger_integration_script").val().match(/https:\/\/[a-zA-Z0-9]*\.(tigerapi.com|tiger.do)\/rest\/\?i=[a-zA-Z0-9=]*/);
                        var int_parse = "";
                        if (int_key != null) {
                            int_parse = int_key[0].split("/rest/?i=");
                            $.post(int_parse[0] + "/rest/websiteCheck.php", {gsywk: hostname, bsgte: int_parse[1]})
                                    .done(function (data) {
                                        if (data.status == "true") {
                                            $(".tiger_integration input[type='submit']").removeAttr("disabled");
                                            $(".tiger_integration .dashicons.dashicons-yes").css({"opacity": "1", "display": "inline-block"});
                                            $(".tiger_integration .dashicons.dashicons-no-alt").css({"opacity": "0", "display": "none"});
                                            $(".tiger_integration .tiger_integration_script").css({"border-color": "#27ae60"});
                                        } else {
                                            $(".tiger_integration .tiger_integration_script").css({"border-color": "#c0392b"});
                                            $(".tiger_integration .dashicons.dashicons-yes").css({"opacity": "0", "display": "none"});
                                            $(".tiger_integration .dashicons.dashicons-no-alt").css({"opacity": "1", "display": "inline-block"});
                                            $(".tiger_integration input[type='submit']").attr("disabled", "true");
                                        }
                                    });
                        }
                    }
                })
            });

        </script>
        <?php
    }

    /**
     * Get the settings option array and print one of its values
     */
    public function tiger_integration_add_tiger_settings() {
        echo '<ul>';
        $args = array('public' => true);
        $output = 'names'; // names or objects, note names is the default
        $operator = 'and'; // 'and' or 'or'
        $post_types = get_post_types($args, $output, $operator);

        $selected = isset($this->options['tiger_integration_type']) ? $this->options['tiger_integration_type'] : false;
        foreach ($post_types as $key => $post_type) {
            if ($selected == false) {
                $checked = 'checked="checked"';
            } else {
                $checked = (in_array($post_type, $selected)) ? 'checked="checked"' : '';
            }
            echo '<li><input value="' . $post_type . '" type="checkbox" name="tiger_integration_options[tiger_integration_type][]" ' . $checked . ' id="' . $key . '"/>
                <label for="' . $key . '">' . $post_type . '</label></li>';
        }
        echo '</ul>';
    }

}
