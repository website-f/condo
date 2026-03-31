<?php
/*
Plugin Name: Simple Photo Reveal Script
Description: Adds your custom JavaScript to the footer that reveals hidden images when clicking .show-photo-btn.
Version: 1.0
Author: You
*/

if ( !defined('ABSPATH') ) exit;

/**
 * Add your provided JS to the footer
 */
function spr_footer_script() {
    ?>
    <script>
    document.addEventListener("click", function(e){
        if(e.target.classList.contains("show-photo-btn")){
            const wrap = e.target.closest(".hidden-photo-wrap");
            wrap.querySelector(".hidden-photo-content").style.display = "block";
            e.target.style.display = "none"; // hide button after click
        }
    });
    </script>
    <?php
}
add_action('wp_footer', 'spr_footer_script');
