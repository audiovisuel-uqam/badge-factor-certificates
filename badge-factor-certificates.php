<?php

/**
 * Plugin Name:       Badge Factor Certificate Generator
 * Plugin URI:        https://github.com/DigitalPygmalion/badge-factor-certificates
 * Description:       This plugin generates individual certificates with information concerning issued badges
 * Version:           1.0.0
 * Author:            ctrlweb
 * Author URI:        https://ctrlweb.ca/
 * License:           MIT
 * Text Domain:       badge-factor-cert
 * Domain Path:       /languages
 */

/*
 * Copyright (c) 2017 Digital Pygmalion
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and
 * to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of
 * the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO
 * THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

class BadgeFactorCertificates
{
    /**
     * BadgeFactorCertificates Version
     *
     * @var string
     */
    public static $version = '1.0.0';


    /**
     * Holds any blocker error messages stopping plugin running
     *
     * @var array
     *
     * @since 1.0.0
     */
    private $notices = array();


    /**
     * The plugin's required WordPress version
     *
     * @var string
     *
     * @since 1.0.0
     */
    public $required_bf_version = '1.0.0';


    /**
     * BadgeFactorCertificates constructor.
     */
    function __construct()
    {
        // Plugin constants
        $this->basename = plugin_basename(__FILE__);
        $this->directory_path = plugin_dir_path(__FILE__);
        $this->directory_url = plugin_dir_url(__FILE__);

        // Load translations
        load_plugin_textdomain('badgefactor_cert', false, basename( dirname( __FILE__ ) ).'/languages');

        // Activation / deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        add_action( 'admin_menu',     array($this, 'admin_menu'), 300);
        add_action( 'init', array($this, 'create_cpt_certificate'));

    }


    ///////////////////////////////////////////////////////////////////////////
    //                                 HOOKS                                 //
    ///////////////////////////////////////////////////////////////////////////

    /**
     * BadgeFactorCertificates plugin activation hook.
     */
    public function activate()
    {


    }


    /**
     * BadgeFactorCertificates plugin deactivation hook.
     */
    public function deactivate()
    {

    }


    function display_notices() {
        ?>
        <div class="error">
            <p><strong><?php esc_html_e( 'Badge Factor Certificates Installation Problem', 'badgefactor_cert' ); ?></strong></p>

            <p><?php esc_html_e( 'The minimum requirements for Badge Factor Certificates have not been met. Please fix the issue(s) below to continue:', 'badgefactor_cert' ); ?></p>
            <ul style="padding-bottom: 0.5em">
                <?php foreach ( $this->notices as $notice ) : ?>
                    <li style="padding-left: 20px;list-style: inside"><?php echo $notice; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
    }


    /**
     * Check if Badge Factor version is compatible
     *
     * @return boolean Whether compatible or not
     *
     * @since 1.0.0
     */
    public function is_compatible_bf_version() {

        /* Gravity Forms version not compatible */
        if ( ! class_exists( 'BadgeFactor' ) || ! version_compare( BadgeFactor::$version, $this->required_bf_version, '>=' ) ) {
            $this->notices[] = sprintf( esc_html__( '%sBadge Factor%s Version %s is required.', 'badgefactor_cert' ), '<a href="https://github.com/DigitalPygmalion/badge-factor">', '</a>', $this->required_bf_version  );

            return false;
        }

        return true;
    }


    /**
     * admin_menu hook.
     */
    public function admin_menu()
    {
        add_submenu_page('badgeos_badgeos', __('Badge Factor Options', 'badgefactor'), __('Certificates Settings', 'badgefactor_cert'), 'manage_options', 'badgefactor_cert', array($this, 'admin_options'));
    }


    /**
     * add_options_page hook.
     */
    public function badgefactor_options()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        include('admin/settings-page.tpl.php');
    }


    /**
     * init hook to create the certificate post type attached to a badge
     */
    public function create_cpt_certificate()
    {
        // Register the post type
        register_post_type( 'certificates', array(
            'labels'             => array(
                'name'               => __( 'Certificates', 'badgefactor_cert'),
                'singular_name'      => __( 'Certificate', 'badgefactor_cert'),
                'add_new'            => __( 'Add New', 'badgefactor_cert' ),
                'add_new_item'       => __( 'Add New Certificate', 'badgefactor_cert' ),
                'edit_item'          => __( 'Edit Certificate', 'badgefactor_cert' ),
                'new_item'           => __( 'New Certificate', 'badgefactor_cert' ),
                'all_items'          => __( 'Certificates', 'badgefactor_cert'),
                'view_item'          => __( 'View Certificates', 'badgefactor_cert' ),
                'search_items'       => __( 'Search Certificates', 'badgefactor_cert' ),
                'not_found'          => __( 'No certificate found', 'badgefactor_cert' ),
                'not_found_in_trash' => __( 'No certificate found in Trash', 'badgefactor_cert' ),
                'parent_item_colon'  => '',
                'menu_name'          => 'Certificates',
            ),
            'rewrite' => array(
                'slug' => 'certificates',
            ),
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => current_user_can( badgeos_get_manager_capability() ),
            'show_in_menu'       => 'badgeos_badgeos',
            'query_var'          => true,
            'capability_type'    => 'post',
            'has_archive'        => 'certificates',
            'hierarchical'       => true,
            'menu_position'      => null,
            'supports'           => array( 'title' )
        ) );


        if( function_exists('register_field_group') ):

            register_field_group(array (
                'id' => 'acf_certificats',
                'title' => 'Certificats',
                'fields' => array (
                    array (
                        'key' => 'field_59159115271cd',
                        'label' => 'PDF File',
                        'name' => 'pdf_file',
                        'type' => 'file',
                        'required' => 1,
                        'save_format' => 'object',
                        'library' => 'all',
                    ),
                    array (
                        'key' => 'field_59159159271ce',
                        'label' => 'Associated Badge',
                        'name' => 'badge',
                        'type' => 'relationship',
                        'return_format' => 'object',
                        'post_type' => array (
                            0 => 'badges',
                        ),
                        'taxonomy' => array (
                            0 => 'all',
                        ),
                        'filters' => array (
                            0 => 'search',
                        ),
                        'result_elements' => array (
                            0 => 'post_type',
                            1 => 'post_title',
                        ),
                        'max' => '',
                    ),
                    array (
                        'key' => 'field_59159bb617306',
                        'label' => 'Recipient Name Position (x)',
                        'name' => 'recipient_name_position_x',
                        'type' => 'number',
                        'required' => 1,
                        'default_value' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'min' => '',
                        'max' => '',
                        'step' => '',
                    ),
                    array (
                        'key' => 'field_59159bf417307',
                        'label' => 'Recipient Name Position (y)',
                        'name' => 'recipient_name_position_y',
                        'type' => 'number',
                        'required' => 1,
                        'default_value' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'min' => '',
                        'max' => '',
                        'step' => '',
                    ),
                    array (
                        'key' => 'field_59159c0617308',
                        'label' => 'Issue Date Position (x)',
                        'name' => 'issue_date_position_x',
                        'type' => 'number',
                        'required' => 1,
                        'default_value' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'min' => '',
                        'max' => '',
                        'step' => '',
                    ),
                    array (
                        'key' => 'field_59159c2717309',
                        'label' => 'Issue Date Position (y)',
                        'name' => 'issue_date_position_y',
                        'type' => 'number',
                        'required' => 1,
                        'default_value' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'min' => '',
                        'max' => '',
                        'step' => '',
                    ),
                    array (
                        'key' => 'field_59159c641730a',
                        'label' => 'Font Family',
                        'name' => 'font_family',
                        'type' => 'select',
                        'choices' => array (
                            'Courier' => 'Courier',
                            'Helvetica' => 'Helvetica',
                            'Times' => 'Times',
                        ),
                        'default_value' => 'Helvetica',
                        'allow_null' => 0,
                        'multiple' => 0,
                    ),
                    array (
                        'key' => 'field_59159cda1730b',
                        'label' => 'Font Style',
                        'name' => 'font_style',
                        'type' => 'select',
                        'choices' => array (
                            'none' => 'Regular',
                            'B' => 'Bold',
                            'I' => 'Italic',
                            'U' => 'Underline',
                        ),
                        'default_value' => '',
                        'allow_null' => 0,
                        'multiple' => 0,
                    ),
                    array (
                        'key' => 'field_59159d171730c',
                        'label' => 'Font Size',
                        'name' => 'font_size',
                        'type' => 'number',
                        'default_value' => 12,
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'min' => '',
                        'max' => '',
                        'step' => '',
                    ),
                ),
                'location' => array (
                    array (
                        array (
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => 'certificates',
                            'order_no' => 0,
                            'group_no' => 0,
                        ),
                    ),
                ),
                'options' => array (
                    'position' => 'normal',
                    'layout' => 'no_box',
                    'hide_on_screen' => array (
                    ),
                ),
                'menu_order' => 0,
            ));

        endif;

        flush_rewrite_rules();

    }


}

function load_badgefactor_cert()
{
    $GLOBALS['badgefactor']->cert = new BadgeFactorCertificates();
}
add_action('plugins_loaded', 'load_badgefactor_cert');

