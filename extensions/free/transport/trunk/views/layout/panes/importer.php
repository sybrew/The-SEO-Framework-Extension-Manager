<?php
/**
 * @package TSF_Extension_Manager\Extension\Transport\Admin\Views
 */

// phpcs:disable, VariableAnalysis.CodeAnalysis.VariableAnalysis.UndefinedVariable -- includes.
// phpcs:disable, WordPress.WP.GlobalVariablesOverride -- This isn't the global scope.

defined( 'TSF_EXTENSION_MANAGER_PRESENT' ) and $this->_verify_include_secret( $_secret ) or die;

use function \TSF_Extension_Manager\Transition\{
	convert_markdown,
	make_data_attributes,
};

?>
<div class=tsfem-pane-inner-wrap id=tsfem-e-transport-importer-wrap>
	<div class="tsfem-e-transport-importer tsfem-flex tsfem-flex-row tsfem-flex-nogrow hide-if-tsf-js">
		<div class=tsfem-pane-section>
			<h4 class=tsfem-info-title><?= esc_html__( 'JavaScript required', 'the-seo-framework-extension-manager' ); ?></h4>
			<p class=tsfem-description><?= esc_html__( 'To give live status updates, JavaScript is required.', 'the-seo-framework-extension-manager' ); ?></p>
		</div>
	</div>
	<div class="tsfem-e-transport-importer tsfem-flex tsfem-flex-row tsfem-flex-nogrow hide-if-no-tsf-js">
		<div class=tsfem-pane-section>
			<h4 class=tsfem-form-title><?= esc_html__( 'Create a database backup!', 'the-seo-framework-extension-manager' ); ?></h4>
			<span class=description><?=
			// phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped -- convert_markdown escapes.
			convert_markdown(
				sprintf(
					/* translators: %s = URL to backup documentation. Asterisks are markdown! */
					esc_html__( 'The importer updates index keys in the database of this WordPress installation. **Old data will be deleted** and some data will be transformed. The Transport extension actively logs all transactions on your screen and will halt transportation on failure. **Always make a backup before importing** in case you need to undo the transport. [Learn about WordPress backups](%s).', 'the-seo-framework-extension-manager' ),
					esc_url( _x(
						'https://wordpress.org/support/article/wordpress-backups/',
						'backup documentation',
						'the-seo-framework-extension-manager'
					) )
				),
				[ 'a', 'strong' ]
			);
			?>
		</div>
		<div class=tsfem-pane-section>
			<h4 class=tsfem-form-title><?= esc_html__( 'Start importing', 'the-seo-framework-extension-manager' ); ?></h4>
			<form id=tsfem-e-transport-importer autocomplete=off data-form-type=other>
				<?php
				$_importer_options = '';
				$_selected         = true;
				$_available        = true;
				foreach ( $this->get_importers() as $importer => $data ) {
					$_importer_options .= vsprintf(
						'<option value="%s" %s %s>%s</option>',
						[
							esc_attr( $importer ),
							$_selected && $_available ? 'selected' : ( $_available ? '' : 'disabled' ),
							make_data_attributes( $data ),
							esc_html( $data['title'] ),
						]
					);

					// When the first available is selected, disable selected clause.
					$_available
						and $_selected = false;
				}
				vprintf(
					'<p><label for=%1$s>%2$s</label></p>
					<p><select name=%1$s id=%1$s>%3$s</select></p>',
					[
						'tsfem-e-transport-importer[choosePlugin]',
						esc_html__( 'Choose a plugin to import SEO data from.', 'the-seo-framework-extension-manager' ),
						$_importer_options, // phpcs:ignore, WordPress.Security.EscapeOutput.OutputNotEscaped
					]
				);
				?>
				<div id=tsfem-e-transport-importer-options style=display:none></div>
				<p id=tsfem-e-transport-importer-supports-transformation-help style=display:none>
					<?php
					// TODO maybe later.
					// printf(
					// 	'<sup>&dagger;</sup> <em>%s</em>',
					// 	esc_html__( 'This data will be transformed to become usable; this process cannot be reversed.', 'the-seo-framework-extension-manager' )
					// );
					printf(
						'<em>%s</em>',
						esc_html__( 'Title and description data will have their syntax markup transformed into real text. Those results cannot be transported back.', 'the-seo-framework-extension-manager' )
					);
					?>
				</p>
				<a id=tsfem-e-transport-importer-submit href=javascript:; class="tsfem-button-primary tsfem-button-upload tsfem-button-disabled"><?= esc_html__( 'Import and delete old data', 'the-seo-framework-extension-manager' ); ?></a>
			</form>
			<template id=tsfem-e-transport-importer-options-template style=display:none>
				<p>
					<label class=tsfem-e-transport-importer-selectType>
						<input type=checkbox name=tsfem-e-transport-importer[selectType][] value="" checked></input>
						<span class=tsfem-e-transport-importer-selectType-description></span>
					</label>
					<div class=tsfem-e-transport-importer-selectType-supports></div>
				</p>
			</template>
			<template id=tsfem-e-transport-importer-supports-template style=display:none>
				<ul>
				<?php
				foreach ( [
					'title'               => __( 'Meta Title', 'the-seo-framework-extension-manager' ),
					'description'         => __( 'Meta Description', 'the-seo-framework-extension-manager' ),
					'canonical_url'       => __( 'Canonical URL', 'the-seo-framework-extension-manager' ),
					'og_title'            => __( 'Open Graph Title', 'the-seo-framework-extension-manager' ),
					'og_description'      => __( 'Open Graph Description', 'the-seo-framework-extension-manager' ),
					'og_image'            => __( 'Social Image', 'the-seo-framework-extension-manager' ),
					'twitter_title'       => __( 'Twitter Title', 'the-seo-framework-extension-manager' ),
					'twitter_description' => __( 'Twitter Description', 'the-seo-framework-extension-manager' ),
					'noindex'             => __( 'Robots Indexing', 'the-seo-framework-extension-manager' ),
					'nofollow'            => __( 'Robots Link Following', 'the-seo-framework-extension-manager' ),
					'noarchive'           => __( 'Robots Archiving', 'the-seo-framework-extension-manager' ),
					'primary_term'        => __( 'Primary Term', 'the-seo-framework-extension-manager' ),
					// 'article_type'        => __( 'Article Type', 'the-seo-framework-extension-manager' ),
				] as $selection => $i18n ) {
					vprintf(
						'<li class=tsfem-e-transport-importer-support[%s] style=display:none>%s<sup class=tsfem-e-transport-importer-transform>&dagger;</sup></li>',
						[
							esc_attr( $selection ), // redundant escape
							esc_html( $i18n ),
						]
					);
				}
				?>
				</ul>
			</template>
		</div>
	</div>
</div>
