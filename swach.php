<?php

/**
 * Plugin Name:Color Picker Plugin
 * Plugin URI: 
 * Description: Adds a color picker field to the 'pa_color' attribute for products.
 * Version: 1.0.0
 * Author: Ahir
 * Author URI: 
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */
/**
 * Add meta field to pa_color taxonomy
 */
function add_color_meta_field()
{
?>
    <div class="form-field term-group">
        <label for="color-preview"><?php _e('Color', 'looksyourstyle'); ?></label>
        <input type="text" class="color-field" name="color" id="color-preview" value="#ffffff">
    </div>
<?php
}
add_action('pa_color_add_form_fields', 'add_color_meta_field');


/**
 * Save meta field
 */
function save_color_meta_field($term_id)
{
    if (isset($_POST['color']) && '' !== $_POST['color']) {
        $color = sanitize_hex_color($_POST['color']);
        add_term_meta($term_id, 'color', $color, true);
    }
}
add_action('created_pa_color', 'save_color_meta_field');


/**
 * Edit meta field
 */
function edit_color_meta_field($term)
{
    $color = get_term_meta($term->term_id, 'color', true);
?>
    <tr class="form-field term-group-wrap">
        <th scope="row"><label for="color-preview"><?php _e('Color', 'looksyourstyle'); ?></label></th>
        <td><input type="text" class="color-field" name="color" id="color-preview" value="<?php echo $color; ?>"></td>
    </tr>
<?php
}
add_action('pa_color_edit_form_fields', 'edit_color_meta_field');


/**
 * Update meta field
 */
function update_color_meta_field($term_id)
{
    if (isset($_POST['color']) && '' !== $_POST['color']) {
        $color = sanitize_hex_color($_POST['color']);
        update_term_meta($term_id, 'color', $color);
    }
}
add_action('edited_pa_color', 'update_color_meta_field');


/**
 * Add color preview column to pa_color taxonomy admin screen
 */
function add_color_preview_column($columns)
{
    $columns['color'] = __('Color', 'looksyourstyle');
    return $columns;
}
add_filter('manage_edit-pa_color_columns', 'add_color_preview_column');


/**
 * Populate color preview column with color previews
 */
function populate_color_preview_column($content, $column_name, $term_id)
{
    if ('color' === $column_name) {
        $color = get_term_meta($term_id, 'color', true);
        $content .= '<div class="color-preview" style="background-color: ' . $color . ';"></div>';
    }
    return $content;
}
add_filter('manage_pa_color_custom_column', 'populate_color_preview_column', 10, 3);


/**
 * Add color preview to term list table styles
 */
function add_color_preview_styles()
{
    echo '<style>.color-preview { display: inline-block; width: 20px; height: 20px; }</style>';
}
add_action('admin_head-edit-tags.php', 'add_color_preview_styles');


/**

Enqueue the color picker script
 */
function enqueue_color_picker_script()
{
    if (is_admin() && isset($_GET['taxonomy']) && $_GET['taxonomy'] === 'pa_color') {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
    }
}
add_action('admin_enqueue_scripts', 'enqueue_color_picker_script');

function enqueue_variation_swatches_styles()
{
    wp_enqueue_style('variation-swatches', plugin_dir_url(__FILE__) . '/css/variation-swatches.css');
}
add_action('wp_enqueue_scripts', 'enqueue_variation_swatches_styles');


/**
    
    Add color picker to pa_color taxonomy term meta fields
 */
function add_color_picker_to_pa_color_meta_fields()
{
?>
    <script>
        jQuery(document).ready(function($) {
            // Add color picker to color input field
            $('#color-preview').wpColorPicker();
        });
    </script>
    <?php
}

add_action('admin_head-edit-tags.php', 'add_color_picker_to_pa_color_meta_fields');
add_action('admin_head-term.php', 'add_color_picker_to_pa_color_meta_fields');
add_action('admin_head-edit.php', 'add_color_picker_to_pa_color_meta_fields');
function add_custom_options_below_dropdown($html, $args)
{
    // Check if the attribute is 'pa_color'
    if ($args['attribute'] === 'pa_color') {
        $html .= '<ul class="swatch-' . $args['attribute'] . '">';
        foreach ($args['options'] as $term) {
            $term_obj = get_term_by('slug', $term, $args['attribute']);
            $color = get_term_meta($term_obj->term_id, 'color', true);
            $html .= '<li aria-checked="" tabindex="0" class="variable-item color-variable-item color-variable-item-' . $term . '" title="' . $term . '" data-title="' . $args['attribute'] . '" data-value="' . $term . '" role="radio">';
            $html .= '<div class="variable-item-contents">';
            $html .= '<span class="variable-item-span variable-item-span-color" style="background-color: ' . esc_attr($color) . '"></span>';
            $html .= '</div>';
            $html .= '</li>';
        }
        $html .= '</ul>';
    }
    return $html;
}
add_filter('woocommerce_dropdown_variation_attribute_options_html', 'add_custom_options_below_dropdown', 10, 2);

add_action('wp_footer', 'display_color_swatches');
function display_color_swatches()
{
    global $product;
    if (!is_a($product, 'WC_Product')) {
        return; // $product is not available or is not a valid product object.
    }
    // Check if the product has a color attribute.
    if ($product->get_attribute('pa_color')) {
    ?>
        <script>
            jQuery(document).ready(function() {
                var attribute = 'attribute_pa_color';
                var select = jQuery('.variations select[name="' + attribute + '"]');
                var options = select.find('option');
                // Hide the select drop-down.
                select.hide();
                // Handle the swatch click event.
                jQuery('.variable-item.color-variable-item').click(function() {
                    var value = jQuery(this).data('value');
                    jQuery('.variable-item.color-variable-item').removeClass('selected');
                    jQuery(this).addClass('selected');
                    select.val(value).trigger('change');
                });
                jQuery('.reset_variations').on('click', function() {
                    jQuery('.variable-item').removeClass('selected');
                });
            });
        </script>
<?php
    }
}
