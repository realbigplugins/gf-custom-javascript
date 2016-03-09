<?php
/*
Plugin Name: Gravity Forms: Custom JavaScript on Submission
Description: This plugin injects custom JavaScript on gform_after_submission per form or globally
Author: Eric Defore
Author URI: http://realbigplugins.com
Version: 0.1
License: GPL2
*/

if ( ( ! class_exists( 'Gravity_Forms_Custom_JavaScript' ) ) && ( class_exists( 'GFForms') ) ) {
    
    GFForms::include_addon_framework();

    class Gravity_Forms_Custom_JavaScript extends GFAddOn {

        protected $_version = '0.1';
        protected $_min_gravityforms_version = '1.7.9999';
        protected $_slug = 'gf-custom-javascript';
        protected $_path = 'gf-custom-javascript/gf-custom-javascript.php';
        protected $_full_path = __FILE__;
        protected $_title = 'Gravity Forms: Custom JavaScript on Submission';
        protected $_short_title = 'Custom JavaScript';
        
        // Members plugin integration
        protected $_capabilities = array( 'gravityforms_custom_javascript', 'gravityforms_custom_javascript_uninstall' );

        // Permissions
        protected $_capabilities_settings_page = 'gravityforms_custom_javascript';
        protected $_capabilities_form_settings = 'gravityforms_custom_javascript';
        protected $_capabilities_uninstall = 'gravityforms_custom_javascript_uninstall';

        public function init(){
            parent::init();
            add_action( 'gform_after_submission', array( $this, 'inject_scripts'), 10, 2);
        }

        public function inject_scripts( $entry, $form ) {
            
            // Per Form
            $form_settings = $this->get_form_settings( $form );
            
            // Global
            $plugin_settings = $this->get_plugin_settings();
            
            $this->clean_script( $form_settings );
            $this->clean_script( $plugin_settings );
            
        }
        
        private function clean_script( $settings ) {
            
            if ( ( $settings ) && ( $settings['gf_custom_javascript'] !== '' ) && ( $settings['gf_custom_javascript'] !== null ) ) {
                
                // If external JavaScript files have been defined, they will be placed after the main Script
                $has_external_scripts = preg_match_all( '/\<script\s(?:.+)?src="([^"]+)"\>+/i', $settings['gf_custom_javascript'], $scripts );
                
                // If they included <script> tags, remove them
                $settings['gf_custom_javascript'] = preg_replace( '/\<\/?script(.*?)\>+/', '', $settings['gf_custom_javascript'] );
                
                // If custom HTML has been included
                $has_custom_html = preg_match_all( '/\<(.*?)\>+/', $settings['gf_custom_javascript'], $html_tags );
                
                if ( $has_custom_html > 0 ) {
                    
                    // Match full string
                    foreach ( $html_tags[0] as $html ) {
                        echo $html;
                    }
                    
                }
                
                // Now we can remove any custom HTML from the JavaScript output
                $settings['gf_custom_javascript'] = preg_replace( '/\<(.*?)\>+/', '', $settings['gf_custom_javascript'] );
                
                ?>

                <script type = "text/javascript">
                    // Gravity Forms: Custom JavaScript on Submission
                    // This script only runs on this Form
                    <?php echo $settings['gf_custom_javascript']; ?>
                </script>

                <?php
                
                if ( $has_external_scripts > 0 ) {
                    
                    // Match first sub-pattern
                    foreach ( $scripts[1] as $script ) {
                        ?>
                        
                        <script type = "text/javascript" src="<?php echo $script; ?>"></script>

                        <?php
                    }
                    
                }
                
            }
            
        }

        // Per Form Settings
        public function form_settings_fields( $form ) {
            return array(
                array(
                    'title'  => 'Custom JavaScript on Form Submission',
                    'fields' => array(
                        array(
                            'label'   => 'Custom JavaScript Specific to this Form.<br /><br />If using jQuery, <a href="https://learn.jquery.com/using-jquery-core/avoid-conflicts-other-libraries/#use-an-immediately-invoked-function-expression">be sure to put it in a immediately invoked function expression!</a>',
                            'type'    => 'textarea',
                            'name'    => 'gf_custom_javascript',
                            'tooltip' => '&lt; script &gt; tags are not necessary, but they can be used to enclose your JavaScript and add HTML elements if you\'re inclined to (Like for a "Tracking Pixel" within a &lt; noscript &gt; tag)',
                            'class'   => 'medium mt-position-right',
                        ),
                    ),
                ),
            );
        }

        // Global Settings
        public function plugin_settings_fields() {
            return array(
                array(
                    'title'  => 'Custom JavaScript on Form Submission',
                    'fields' => array(
                        array(
                            'label'   => 'Custom JavaScript for Every Form.<br /><br />If using jQuery, <a href="https://learn.jquery.com/using-jquery-core/avoid-conflicts-other-libraries/#use-an-immediately-invoked-function-expression">be sure to put it in a immediately invoked function expression!</a>',
                            'type'    => 'textarea',
                            'name'    => 'gf_custom_javascript',
                            'tooltip' => '&lt; script &gt; tags are not necessary, but they can be used to enclose your JavaScript and add HTML elements if you\'re inclined to (Like for a "Tracking Pixel" within a &lt; noscript &gt; tag)',
                            'class'   => 'medium mt-position-right',
                        ),
                    ),
                ),
            );
        }
    }

    new Gravity_Forms_Custom_JavaScript();
}

add_action( 'plugins_loaded', function() {

    if ( ! class_exists( 'GFForms' ) ) {

        add_action( 'admin_notices', function() {
            
            ?>

            <div id="message" class="error notice is-dismissible">
                <p>The Plugin <strong>Gravity Forms: Custom JavaScript on Submission</strong> requires <strong><a href = "http://www.gravityforms.com/" target="_blank">Gravity Forms</a></strong> to be Active!</p>
            </div>

            <?php
            
        } );

    }

} );