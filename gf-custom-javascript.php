<?php
/*
Plugin Name: Gravity Forms: Custom JavaScript on Submission
Description: This plugin injects custom JavaScript on gform_after_submission per form or globally
Author: Eric Defore
Author URI: http://realbigplugins.com
Version: 0.1
Text Domain: gf-custom-javascript
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
        
        // These need to be defined un-translatable up here. Otherwise the text in the Sidebar of the Gravity Forms options doesn't show.
        // That one instance of the String may sadly be un-translatable, but every other Instance will translate correctly.
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
            
            $this->load_textdomain();
            
            // The only way to translate these Strings is to set them this way.
            $this->_title = __( 'Gravity Forms: Custom JavaScript on Submission', 'gf-custom-javascript' );
            $this->_short_title = __( 'Custom JavaScript', 'gf-custom-javascript' );
            
            add_action( 'gform_after_submission', array( $this, 'inject_scripts' ), 10, 2 );
            
        }

        public function inject_scripts( $entry, $form ) {
            
            // Per Form
            $form_settings = $this->get_form_settings( $form );
            
            // Global
            $plugin_settings = $this->get_plugin_settings();
            
            $this->clean_script( $form_settings, __( 'This script only runs on this Form', 'gf-custom-javascript' ) );
            $this->clean_script( $plugin_settings, __( 'This script runs on every Form', 'gf-custom-javascript' ) );
            
        }
        
        private function clean_script( $settings, $message ) {
            
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
                    // <?php _e( 'Gravity Forms: Custom JavaScript on Submission', 'gf-custom-javascript' ); ?>
                    // <?php echo "$message\n" ?>
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
                    'title'  => __( 'Custom JavaScript on Form Submission', 'gf-custom-javascript' ),
                    'fields' => array(
                        array(
                            'label'   => __( 'Custom JavaScript Specific to this Form.<br /><br />If using jQuery, <a href="https://learn.jquery.com/using-jquery-core/avoid-conflicts-other-libraries/#use-an-immediately-invoked-function-expression">be sure to put it in a immediately invoked function expression!</a>', 'gf-custom-javascript'),
                            'type'    => 'textarea',
                            'name'    => 'gf_custom_javascript',
                            'tooltip' => __( '&lt; script &gt; tags are not necessary, but they can be used to enclose your JavaScript and add HTML elements if you\'re inclined to (Like for a "Tracking Pixel" within a &lt; noscript &gt; tag)', 'gf-custom-javascript' ),
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
                    'title'  => __( 'Custom JavaScript on Form Submission', 'gf-custom-javascript' ),
                    'fields' => array(
                        array(
                            'label'   => __( 'Custom JavaScript for Every Form.<br /><br />If using jQuery, <a href="https://learn.jquery.com/using-jquery-core/avoid-conflicts-other-libraries/#use-an-immediately-invoked-function-expression">be sure to put it in a immediately invoked function expression!</a>', 'gf-custom-javascript' ),
                            'type'    => 'textarea',
                            'name'    => 'gf_custom_javascript',
                            'tooltip' => __( '&lt; script &gt; tags are not necessary, but they can be used to enclose your JavaScript and add HTML elements if you\'re inclined to (Like for a "Tracking Pixel" within a &lt; noscript &gt; tag)', 'gf-custom-javascript' ),
                            'class'   => 'medium mt-position-right',
                        ),
                    ),
                ),
            );
        }
    
        private function load_textdomain() {

            // Set filter for language directory
            $lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
            $lang_dir = apply_filters( 'gf_custom_javascript_languages_directory', $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale', get_locale(), 'gf-custom-javascript' );
            $mofile = sprintf( '%1$s-%2$s.mo', 'gf-custom-javascript', $locale );

            // Setup paths to current locale file
            $mofile_local   = $lang_dir . $mofile;
            $mofile_global  = WP_LANG_DIR . '/gf-custom-javascript/' . $mofile;

            if ( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/gf-custom-javascript/ folder
                // This way translations can be overridden via the /wp-content directory
                load_textdomain( 'gf-custom-javascript', $mofile_global );
            }
            else if( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/gf-custom-javascript/languages/ folder
                load_textdomain( 'gf-custom-javascript', $mofile_local );
            }
            else {
                // Load the default language files
                load_plugin_textdomain( 'gf-custom-javascript', false, $lang_dir );
            }

        }
        
    }

    new Gravity_Forms_Custom_JavaScript();
    
}

add_action( 'plugins_loaded', function() {

    if ( ! class_exists( 'GFForms' ) ) {

        add_action( 'admin_notices', function() {
            
            ?>

            <div id="message" class="error notice is-dismissible">
                <p><?php __e( 'The Plugin <strong>Gravity Forms: Custom JavaScript on Submission</strong> requires <strong><a href = "http://www.gravityforms.com/" target="_blank">Gravity Forms</a></strong> to be Active!', 'gf-custom-javascript' ); ?></p>
            </div>

            <?php
            
        } );

    }

} );