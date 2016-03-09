<?php
/*
Plugin Name: Gravity Forms: Custom JavaScript on Submission
Description: This plugin injects custom JavaScript on gform_after_submission per form or globally
Author: Eric Defore
Version: 0.1
License: GPL2
*/

if ( ( ! class_exists( 'Gravity_Forms_Custom_JavaScript' ) ) && ( class_exists( 'GFForms') ) ) {
    
    GFForms::include_addon_framework();

    class Gravity_Forms_Custom_JavaScript extends GFAddOn {

        protected $_version = '0.1';
        protected $_min_gravityforms_version = '1.7.9999';
        protected $_slug = 'gravity-forms-custom-javascript-submission';
        protected $_path = 'gravity-forms-custom-javascript-submission/gravity-forms-custom-javascript-submission.php';
        protected $_full_path = __FILE__;
        protected $_title = 'Gravity Forms: Custom JavaScript on Submission';
        protected $_short_title = 'Custom JavaScript';

        public function init(){
            parent::init();
            add_action( 'gform_after_submission', array( $this, 'inject_scripts'), 10, 2);
        }

        // Add the text in the plugin settings to the bottom of the form if enabled for this form
        public function inject_scripts( $entry, $form ) {
            
            // Per Form
            $form_settings = $this->get_form_settings( $form );
            
            // Global
            $plugin_settings = $this->get_plugin_settings( $form );
            
            if ( ( $form_settings ) && ( $form_settings['gf_custom_javascript'] !== '' ) ) {
                
                // If they included <script> tags, remove them
                $form_settings['gf_custom_javascript'] = preg_replace( '/\<\/?script(.*?)\>+/', '', $form_settings['gf_custom_javascript'] );
                
                ?>

                <script type = "text/javascript">
                    // Gravity Forms: Custom JavaScript on Submission
                    // This script only runs on this Form
                    <?php echo $form_settings['gf_custom_javascript']; ?>
                </script>

                <?php
            }
            
            if ( ( $plugin_settings ) && ( $plugin_settings['gf_custom_javascript'] !== '' ) ) {
                
                // If they included <script> tags, remove them
                $plugin_settings['gf_custom_javascript'] = preg_replace( '/\<\/?script(.*?)\>+/', '', $plugin_settings['gf_custom_javascript'] );
                
                ?>

                <script type = "text/javascript">
                    // Gravity Forms: Custom JavaScript on Submission
                    // This script runs on every Form
                    <?php echo $plugin_settings['gf_custom_javascript']; ?>
                </script>

                <?php
            }
            
        }

        // Per Form
        public function form_settings_fields( $form ) {
            return array(
                array(
                    'title'  => 'Custom JavaScript on Form Submission',
                    'fields' => array(
                        array(
                            'label'   => 'Custom JavaScript Specific to this Form.<br /><br />If using jQuery, <a href="https://learn.jquery.com/using-jquery-core/avoid-conflicts-other-libraries/#use-an-immediately-invoked-function-expression">be sure to put it in a immediately invoked function expression!</a>',
                            'type'    => 'textarea',
                            'name'    => 'gf_custom_javascript',
                            'tooltip' => '&lt; script &gt; tags are not necessary.',
                            'class'   => 'medium mt-position-right',
                        ),
                    ),
                ),
            );
        }

        // Global
        public function plugin_settings_fields() {
            return array(
                array(
                    'title'  => 'Custom JavaScript on Form Submission',
                    'fields' => array(
                        array(
                            'label'   => 'Custom JavaScript for Every Form.<br /><br />If using jQuery, <a href="https://learn.jquery.com/using-jquery-core/avoid-conflicts-other-libraries/#use-an-immediately-invoked-function-expression">be sure to put it in a immediately invoked function expression!</a>',
                            'type'    => 'textarea',
                            'name'    => 'gf_custom_javascript',
                            'tooltip' => '&lt; script &gt; tags are not necessary.',
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