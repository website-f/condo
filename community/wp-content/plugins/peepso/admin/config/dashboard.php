<?php
	$size = number_format((100 / $tab_count) - 1, 2);
	if ($size > 15)
		$size = 15;
?>


	<h2><img src="<?php echo esc_url(PeepSo::get_asset('images/admin/logo-icon.svg')); ?>" height="30" width="30" /></h2>
	<div class="row-fluid">
		<div class="dashtab">
		<?php
			foreach ($tabs as $section => $data) {
				echo	'<div class="infobox infobox-blue tab-', esc_attr($section), ' infobox-dark" style="width:', esc_attr($size), '%">', PHP_EOL;

				if ('/' === substr($data['slug'], 0, 1))
					echo	'<a href="', esc_url(get_admin_url(NULL, $data['slug'])), '">', PHP_EOL;
				else
					echo	'<a href="admin.php?page=', esc_attr($slug), '&section=', esc_attr($data['slug']), '">', PHP_EOL;

				echo			'<div class="infobox-icon dashicons dashicons-', esc_attr($data['icon']), '"></div>', PHP_EOL;
				echo			'<div class="infobox-caption">', esc_attr($data['menu']), '</div>', PHP_EOL;
				echo	'</a>', PHP_EOL;
				echo	'</div>';
			}
		?>
		</div>
	</div>
