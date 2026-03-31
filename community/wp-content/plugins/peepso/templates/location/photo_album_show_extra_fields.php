<div class="ps-album__desc ps-js-album-location">
	<div class="ps-album__desc-title">
		<?php echo esc_attr__('Album location', 'peepso-core'); ?>
		<?php if ($can_edit) { ?>
		<a href="#" class="ps-album__edit-toggle ps-tip ps-tip--inline ps-js-album-location-edit" aria-label="<?php echo esc_attr__('Edit location', 'picso'); ?>" onclick="return false;">
			<i class="gcis gci-edit"></i>
		</a>
		<a href="#" class="ps-album__edit-toggle ps-tip ps-tip--inline ps-js-album-location-remove" aria-label="<?php echo esc_attr__('Remove location', 'picso'); ?>" onclick="return false;"
			<?php echo $loc ? '' : 'style="display:none"'; ?>
				data-post-id="<?php echo esc_attr($post_id); ?>">
			<i class="gcis gci-trash"></i>
		</a>
		<?php } ?>
	</div>
	<div class="ps-album__desc-text">
		<div class="ps-js-album-location-empty" <?php echo $loc ? 'style="display:none"' : ''; ?>>
			<i class="gcis gci-map-marker-alt"></i>
			<span><em><?php echo esc_attr__('No location', 'peepso-core'); ?></em></span>
		</div>
		<div class="ps-js-album-location-text" <?php echo $loc ? '' : 'style="display:none"'; ?>><?php

				if ($loc) {
					$lat = $loc['latitude'] ?? null;
					$lng = $loc['longitude'] ?? null;
					$viewport = $loc['viewport'] ?? null;
					$name = $loc['name'] ?? '';

					$data_location = json_encode($lat && $lng ? ['lat' => $lat, 'lng' => $lng] : null);
					$data_viewport = $viewport ?? json_encode(null); // Viewport value is already JSON-encoded.
					$data_label = json_encode($name ? $name : null);
					$onclick = "peepso.location.showMap($data_location, $data_viewport, $data_label)";
				}

			?><a href="#" title="<?php echo esc_attr($name); ?>" onclick="<?php echo esc_attr($onclick ?? '') ?>; return false;">
				<i class="gcis gci-map-marker-alt"></i>
				<span><?php echo esc_attr($name); ?></span>
			</a>
		</div>
	</div>
	<div class="ps-album__desc-edit ps-js-album-location-editor" style="display:none">
		<input type="text" class="ps-input ps-input--sm" value="<?php echo $loc ? esc_attr($loc['name']) : ''; ?>"
			data-location="<?php echo $loc ? esc_attr($loc['name']) : ''; ?>"
			data-latitude="<?php echo $loc ? esc_attr($loc['latitude']) : ''; ?>"
			data-latitude="<?php echo $loc ? esc_attr($loc['longitude']) : ''; ?>"
			data-post-id="<?php echo esc_attr($post_id); ?>" />
		<?php wp_nonce_field('set-album-location', '_wpnonce_set_album_location'); ?>
		<button type="button" class="ps-btn ps-btn--sm ps-js-cancel"><?php echo esc_attr__('Cancel', 'peepso-core'); ?></button>
		<button type="button" class="ps-btn ps-btn--sm ps-btn--action ps-js-submit">
			<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" class="ps-js-loading" alt="loading" style="margin-right:5px;display:none" />
			<?php echo esc_attr__('Save location', 'peepso-core'); ?>
		</button>
	</div>
</div>
