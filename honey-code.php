<?php
/**
 * Plugin Name: Honey Code
 * Plugin URI:  https://honeyblocks.com
 * Description: A gutenberg block for syntax highlighting
 * Author:      Chris Kelley
 * Author URI:  https://iwritecode.blog
 * Version:     1.1.0
 * Text Domain: honey-code
 * Domain Path: languages
 *
 * @package HoneyCode
 *
 * Copyright (C) 2019 Chris Kelley <chris@organicbeemedia.com
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free Software Foundation, version 3.
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with this program.
 * If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * Main Plugin Class.
 *
 * @since 1.0.0
 */
final class HoneyCode {

	/**
	 * Undocumented variable
	 *
	 * @since 1.0.0
	 *
	 * @var object
	 */
	public static $instance = null;

	/**
	 * Undocumented variable
	 *
	 * @var string
	 */
	public $version = '1.1.0';

	/**
	 * Undocumented variable
	 *
	 * @var string
	 */
	public $plugin_slug = 'honey-code';

	/**
	 * Undocumented variable
	 *
	 * @var [type]
	 */
	public $file = __FILE__;

	/**
	 * Undocumented function
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function init() {
		add_action( 'enqueue_block_editor_assets', [ $this, 'editor_assets' ], 10 );
		add_action( 'wp_enqueue_scripts', [ $this, 'block_styles_scripts' ] );
	}

	/**
	 * Helper Method to Load Block Styles and Scripts on the frontend.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function block_styles_scripts() {

		global $post;

		if ( ! has_block( 'honey/code', $post ) ) {
			return;
		}

		wp_enqueue_script(
			'honey-rainbow-js',
			plugins_url( 'assets/scripts/rainbow.js', $this->file ),
			null,
			$this->version,
			true
		);

		$blocks = parse_blocks( $post->post_content );

		$code_blocks = array_keys( array_column( $blocks, 'blockName' ), 'honey/code', true );

		if ( is_array( $code_blocks ) ) {

			foreach ( $code_blocks as $block ) {

				$theme = isset( $blocks[ $block ]['attrs']['theme'] ) ? $blocks[ $block ]['attrs']['theme'] : null;

				if ( ! $theme ) {
					$this->load_theme( 'honey' );

				} else {

					$this->load_theme( $theme );

				}
			}
		}

	}

	/**
	 * Helper Method to Load BLock Theme.
	 *
	 * @param string $theme The Theme Slug.
	 * @return void
	 */
	public function load_theme( $theme = null ) {

		$themes = $this->get_register_themes();
		$theme  = $themes[ $theme ];

		// Only Load the Fira Code font for Honey Themes.
		$font = preg_match( '/honey/', $theme['value'] );

		if ( $font ) {
			wp_enqueue_style(
				'honey-code-font',
				plugins_url( 'assets/themes/css/fira-code.css', $this->file ),
				null,
				$this->version
			);
		}

		wp_enqueue_style(
			$theme['label'],
			plugins_url( 'assets/themes/css/' . $theme['value'] . '.css', $theme['file'] ),
			null,
			$this->version
		);

	}

	/**
	 * Undocumented function
	 *
	 * @return array
	 */
	public function supported_languages() {

		$languages = [
			[
				'label' => 'Generic',
				'value' => 'generic',
			],
			[
				'label' => 'PHP',
				'value' => 'php',
			],
			[
				'label' => 'HTML',
				'value' => 'html',
			],
			[
				'label' => 'Shell',
				'value' => 'shell',
			],
			[
				'label' => 'SQL',
				'value' => 'sql',
			],
			[
				'label' => 'Python',
				'value' => 'python',
			],
			[
				'label' => 'Ruby',
				'value' => 'ruby',
			],
			[
				'label' => 'Lua',
				'value' => 'lua',
			],
			[
				'label' => 'Json',
				'value' => 'json',
			],
			[
				'label' => 'CSS',
				'value' => 'css',
			],
			[
				'label' => 'Java',
				'value' => 'java',
			],
			[
				'label' => 'JavaScript',
				'value' => 'javascript',
			],
			[
				'label' => 'GO',
				'value' => 'go',
			],
			[
				'label' => 'C#',
				'value' => 'csharp',
			],
			[
				'label' => 'C',
				'value' => 'c',
			],
		];

		return apply_filters( 'honey_code_languages', $languages );
	}

	/**
	 * Helper Method to load Gutenberg Scripts.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function editor_assets() {

		$version = random_int( 1, 4000 );
		wp_enqueue_script(
			'honey-code-block',
			plugins_url( 'assets/js/honey-code.js', $this->file ),
			[ 'wp-blocks' ],
			$version,
			true
		);
		wp_enqueue_script(
			'honey-rainbow-js',
			plugins_url( 'assets/scripts/rainbow.js', $this->file ),
			null,
			$this->version,
			true
		);

		wp_enqueue_style(
			'honey-code-font',
			plugins_url( 'assets/themes/css/fira-code.css', $this->file ),
			null,
			$this->version
		);
		$themes = [];

		foreach ( $this->get_register_themes() as $theme ) {
			$themes[] = [
				'label' => $theme['label'],
				'value' => $theme['value'],
			];
			wp_enqueue_style(
				$theme['label'],
				plugins_url( 'assets/themes/css/' . $theme['value'] . '.css', $theme['file'] ),
				null,
				$this->version
			);

		}

		wp_localize_script(
			'honey-code-block',
			'honey_code',
			[
				'isLite'    => false,
				'themes'    => $themes,
				'languages' => $this->supported_languages(),
			]
		);

	}

	/**
	 * Helper Method to register Code themes.
	 *
	 * @since 1.0.0
	 *
	 * @return array Registered Themes.
	 */
	public function get_register_themes() {

		$themes = [];

		$themes['honey'] = [
			'label'   => esc_attr__( 'Honey', 'honey-code' ),
			'value'   => 'honey',
			'version' => '1.0.0',
			'file'    => $this->file,
		];

		$themes['honey-night'] = [
			'label'   => esc_attr__( 'Honey Night', 'honey-code' ),
			'value'   => 'honey-night',
			'version' => '1.0.0',
			'file'    => $this->file,
		];
		$themes['honey-bright'] = [
			'label'   => esc_attr__( 'Honey Bright', 'honey-code' ),
			'value'   => 'honey-bright',
			'version' => '1.0.0',
			'file'    => $this->file,
		];
		$themes['tomorrow'] = [
			'label'   => esc_attr__( 'Tomorrow', 'honey-code' ),
			'value'   => 'tomorrow',
			'version' => '1.0.0',
			'file'    => $this->file,
		];
		$themes['tomorrow-night'] = [
			'label'   => esc_attr__( 'Tomorrow Night', 'honey-code' ),
			'value'   => 'tomorrow-night',
			'version' => '1.0.0',
			'file'    => $this->file,
		];
		return apply_filters( 'honey_code_register_themes', $themes );

	}

	/**
	 * Get the singleton Instance.
	 *
	 * @since 1.0.0
	 *
	 * @return object|HoneyCode
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof HoneyCode ) ) {

			self::$instance = new self();
			self::$instance->init();

		}

		return self::$instance;

	}

}

add_action(
	'plugins_loaded',
	function() {
		return HoneyCode::get_instance();
	}
);
