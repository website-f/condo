<?php

/**
 * Block Styles
 *
 * @link https://developer.wordpress.org/reference/functions/register_block_style/
 *
 * @package wydegrid
 * @since 1.0.0
 */

if (function_exists('register_block_style')) {
    /**
     * Register block styles.
     *
     * @since 0.1
     *
     * @return void
     */
    function hello_agency_register_block_styles()
    {
        register_block_style(
            'core/columns',
            array(
                'name'  => 'wydegrid-boxshadow',
                'label' => __('Box Shadow', 'wydegrid')
            )
        );

        register_block_style(
            'core/column',
            array(
                'name'  => 'wydegrid-boxshadow',
                'label' => __('Box Shadow', 'wydegrid')
            )
        );
        register_block_style(
            'core/column',
            array(
                'name'  => 'wydegrid-boxshadow-medium',
                'label' => __('Box Shadow Medium', 'wydegrid')
            )
        );
        register_block_style(
            'core/column',
            array(
                'name'  => 'wydegrid-boxshadow-large',
                'label' => __('Box Shadow Large', 'wydegrid')
            )
        );

        register_block_style(
            'core/group',
            array(
                'name'  => 'wydegrid-boxshadow',
                'label' => __('Box Shadow', 'wydegrid')
            )
        );
        register_block_style(
            'core/group',
            array(
                'name'  => 'wydegrid-boxshadow-medium',
                'label' => __('Box Shadow Medium', 'wydegrid')
            )
        );
        register_block_style(
            'core/group',
            array(
                'name'  => 'wydegrid-boxshadow-large',
                'label' => __('Box Shadow Larger', 'wydegrid')
            )
        );
        register_block_style(
            'core/image',
            array(
                'name'  => 'wydegrid-boxshadow',
                'label' => __('Box Shadow', 'wydegrid')
            )
        );
        register_block_style(
            'core/image',
            array(
                'name'  => 'wydegrid-boxshadow-medium',
                'label' => __('Box Shadow Medium', 'wydegrid')
            )
        );
        register_block_style(
            'core/image',
            array(
                'name'  => 'wydegrid-boxshadow-larger',
                'label' => __('Box Shadow Large', 'wydegrid')
            )
        );
        register_block_style(
            'core/image',
            array(
                'name'  => 'wydegrid-image-pulse',
                'label' => __('Iamge Pulse Effect', 'wydegrid')
            )
        );
        register_block_style(
            'core/image',
            array(
                'name'  => 'wydegrid-boxshadow-hover',
                'label' => __('Box Shadow on Hover', 'wydegrid')
            )
        );
        register_block_style(
            'core/image',
            array(
                'name'  => 'wydegrid-image-hover-pulse',
                'label' => __('Hover Pulse Effect', 'wydegrid')
            )
        );
        register_block_style(
            'core/image',
            array(
                'name'  => 'wydegrid-image-hover-rotate',
                'label' => __('Hover Rotate Effect', 'wydegrid')
            )
        );
        register_block_style(
            'core/columns',
            array(
                'name'  => 'wydegrid-boxshadow-hover',
                'label' => __('Box Shadow on Hover', 'wydegrid')
            )
        );

        register_block_style(
            'core/column',
            array(
                'name'  => 'wydegrid-boxshadow-hover',
                'label' => __('Box Shadow on Hover', 'wydegrid')
            )
        );

        register_block_style(
            'core/group',
            array(
                'name'  => 'wydegrid-boxshadow-hover',
                'label' => __('Box Shadow on Hover', 'wydegrid')
            )
        );

        register_block_style(
            'core/post-terms',
            array(
                'name'  => 'categories-background-with-round',
                'label' => __('Background Color', 'wydegrid')
            )
        );
        register_block_style(
            'core/button',
            array(
                'name'  => 'button-hover-primary-color',
                'label' => __('Hover: Primary Color', 'wydegrid')
            )
        );
        register_block_style(
            'core/button',
            array(
                'name'  => 'button-hover-secondary-color',
                'label' => __('Hover: Secondary Color', 'wydegrid')
            )
        );
        register_block_style(
            'core/button',
            array(
                'name'  => 'button-hover-primary-bgcolor',
                'label' => __('Hover: Primary color fill', 'wydegrid')
            )
        );
        register_block_style(
            'core/button',
            array(
                'name'  => 'button-hover-secondary-bgcolor',
                'label' => __('Hover: Secondary color fill', 'wydegrid')
            )
        );
        register_block_style(
            'core/button',
            array(
                'name'  => 'button-hover-white-bgcolor',
                'label' => __('Hover: White color fill', 'wydegrid')
            )
        );

        register_block_style(
            'core/read-more',
            array(
                'name'  => 'readmore-hover-primary-color',
                'label' => __('Hover: Primary Color', 'wydegrid')
            )
        );
        register_block_style(
            'core/read-more',
            array(
                'name'  => 'readmore-hover-secondary-color',
                'label' => __('Hover: Secondary Color', 'wydegrid')
            )
        );
        register_block_style(
            'core/read-more',
            array(
                'name'  => 'readmore-hover-primary-fill',
                'label' => __('Hover: Primary Fill', 'wydegrid')
            )
        );
        register_block_style(
            'core/read-more',
            array(
                'name'  => 'readmore-hover-secondary-fill',
                'label' => __('Hover: secondary Fill', 'wydegrid')
            )
        );

        register_block_style(
            'core/list',
            array(
                'name'  => 'list-style-no-bullet',
                'label' => __('Hide bullet', 'wydegrid')
            )
        );
        register_block_style(
            'core/gallery',
            array(
                'name'  => 'enable-grayscale-mode-on-image',
                'label' => __('Enable Grayscale Mode on Image', 'wydegrid')
            )
        );
        register_block_style(
            'core/social-links',
            array(
                'name'  => 'social-icon-size-small',
                'label' => __('Small Size', 'wydegrid')
            )
        );
        register_block_style(
            'core/social-links',
            array(
                'name'  => 'social-icon-size-large',
                'label' => __('Large Size', 'wydegrid')
            )
        );
        register_block_style(
            'core/page-list',
            array(
                'name'  => 'wydegrid-page-list-bullet-hide-style',
                'label' => __('Hide Bullet Style', 'wydegrid')
            )
        );
        register_block_style(
            'core/page-list',
            array(
                'name'  => 'wydegrid-page-list-bullet-hide-style-white-color',
                'label' => __('Hide Bullet Style with White Text Color', 'wydegrid')
            )
        );
        register_block_style(
            'core/categories',
            array(
                'name'  => 'wydegrid-categories-bullet-hide-style',
                'label' => __('Hide Bullet Style', 'wydegrid')
            )
        );
        register_block_style(
            'core/categories',
            array(
                'name'  => 'wydegrid-categories-bullet-hide-style-white-color',
                'label' => __('Hide Bullet Style with Text color White', 'wydegrid')
            )
        );
        register_block_style(
            'core/post-author-name',
            array(
                'name'  => 'author-name-with-icon',
                'label' => __('With Icon', 'wydegrid')
            )
        );
        register_block_style(
            'core/post-author-name',
            array(
                'name'  => 'author-name-with-white-icon',
                'label' => __('With White Icon', 'wydegrid')
            )
        );
        register_block_style(
            'core/post-date',
            array(
                'name'  => 'post-date-with-icon',
                'label' => __('With Icon', 'wydegrid')
            )
        );
        register_block_style(
            'core/post-date',
            array(
                'name'  => 'post-date-with-white-icon',
                'label' => __('With White Icon', 'wydegrid')
            )
        );
    }
    add_action('init', 'hello_agency_register_block_styles');
}
