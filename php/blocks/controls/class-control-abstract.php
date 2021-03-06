<?php
/**
 * Control abstract.
 *
 * @package   Block_Lab
 * @copyright Copyright(c) 2018, Block Lab
 * @license http://opensource.org/licenses/GPL-2.0 GNU General Public License, version 2 (GPL-2.0)
 */

namespace Block_Lab\Blocks\Controls;

use Block_Lab\Blocks\Field;

/**
 * Class Control_Abstract
 */
abstract class Control_Abstract {

	/**
	 * Control name.
	 *
	 * @var string
	 */
	public $name = '';

	/**
	 * Control label.
	 *
	 * @var string
	 */
	public $label = '';

	/**
	 * Field variable type.
	 *
	 * @var string
	 */
	public $type = 'string';

	/**
	 * Control settings.
	 *
	 * @var Control_Setting[]
	 */
	public $settings = array();

	/**
	 * Control constructor.
	 *
	 * @return void
	 */
	public function __construct() {
		$this->register_settings();
	}

	/**
	 * Register settings.
	 *
	 * @return void
	 */
	abstract public function register_settings();

	/**
	 * Render additional settings in table rows.
	 *
	 * @param Field  $field The Field containing the options to render.
	 * @param string $uid   A unique ID to used to unify the HTML name, for, and id attributes.
	 *
	 * @return void
	 */
	public function render_settings( $field, $uid ) {
		foreach ( $this->settings as $setting ) {
			if ( isset( $field->settings[ $setting->name ] ) ) {
				$setting->value = $field->settings[ $setting->name ];
			} else {
				$setting->value = $setting->default;
			}

			$classes = array(
				'block-fields-edit-settings-' . $this->name . '-' . $setting->name,
				'block-fields-edit-settings-' . $this->name,
				'block-fields-edit-settings',
			);
			$name    = 'block-fields-settings[' . $uid . '][' . $setting->name . ']';
			$id      = 'block-fields-edit-settings-' . $this->name . '-' . $setting->name . '_' . $uid;
			?>
			<tr class="<?php echo esc_attr( implode( $classes, ' ' ) ); ?>">
				<td class="spacer"></td>
				<th scope="row">
					<label for="<?php echo esc_attr( $id ); ?>">
						<?php echo esc_html( $setting->label ); ?>
					</label>
					<p class="description">
						<?php echo wp_kses_post( $setting->help ); ?>
					</p>
				</th>
				<td>
					<?php
					$method = 'render_settings_' . $setting->type;
					if ( method_exists( $this, $method ) ) {
						$this->$method( $setting, $name, $id );
					} else {
						$this->render_settings_text( $setting, $name, $id );
					}
					?>
				</td>
			</tr>
			<?php
		}
	}

	/**
	 * Render text settings
	 *
	 * @param Control_Setting $setting The Control_Setting being rendered.
	 * @param string          $name    The name attribute of the option.
	 * @param string          $id      The id attribute of the option.
	 *
	 * @return void
	 */
	public function render_settings_text( $setting, $name, $id ) {
		?>
		<input
			name="<?php echo esc_attr( $name ); ?>"
			type="text"
			id="<?php echo esc_attr( $id ); ?>"
			class="regular-text"
			value="<?php echo esc_attr( $setting->get_value() ); ?>" />
		<?php
	}

	/**
	 * Render textarea settings
	 *
	 * @param Control_Setting $setting The Control_Setting being rendered.
	 * @param string          $name    The name attribute of the option.
	 * @param string          $id      The id attribute of the option.
	 *
	 * @return void
	 */
	public function render_settings_textarea( $setting, $name, $id ) {
		?>
		<textarea
			name="<?php echo esc_attr( $name ); ?>"
			id="<?php echo esc_attr( $id ); ?>"
			rows="6"
			class="large-text"><?php echo esc_html( $setting->get_value() ); ?></textarea>
		<?php
	}

	/**
	 * Render checkbox settings
	 *
	 * @param Control_Setting $setting The Control_Setting being rendered.
	 * @param string          $name    The name attribute of the option.
	 * @param string          $id      The id attribute of the option.
	 *
	 * @return void
	 */
	public function render_settings_checkbox( $setting, $name, $id ) {
		?>
		<input
			name="<?php echo esc_attr( $name ); ?>"
			type="checkbox"
			id="<?php echo esc_attr( $id ); ?>"
			class=""
			value="1"
			<?php checked( '1', $setting->get_value() ); ?> />
		<?php
	}

	/**
	 * Render number settings
	 *
	 * @param Control_Setting $setting The Control_Setting being rendered.
	 * @param string          $name    The name attribute of the option.
	 * @param string          $id      The id attribute of the option.
	 *
	 * @return void
	 */
	public function render_settings_number( $setting, $name, $id ) {
		?>
		<input
			name="<?php echo esc_attr( $name ); ?>"
			type="number"
			id="<?php echo esc_attr( $id ); ?>"
			class="regular-text"
			min="0"
			value="<?php echo esc_attr( $setting->get_value() ); ?>" />
		<?php
	}

	/**
	 * Render an array of settings inside a textarea.
	 *
	 * @param Control_Setting $setting The Control_Setting being rendered.
	 * @param string          $name    The name attribute of the option.
	 * @param string          $id      The id attribute of the option.
	 *
	 * @return void
	 */
	public function render_settings_textarea_array( $setting, $name, $id ) {
		$options = $setting->get_value();
		if ( is_array( $options ) ) {
			// Convert the array to text separated by new lines.
			$value = '';
			foreach ( $options as $option ) {
				if ( ! is_array( $option ) ) {
					$value .= $option . "\n";
					continue;
				}
				if ( ! isset( $option['value'] ) || ! isset( $option['label'] ) ) {
					continue;
				}
				if ( $option['value'] === $option['label'] ) {
					$value .= $option['label'] . "\n";
				} else {
					$value .= $option['value'] . ' : ' . $option['label'] . "\n";
				}
			}
			$setting->value = trim( $value );
		}
		$this->render_settings_textarea( $setting, $name, $id );
	}

	/**
	 * Sanitize checkbox.
	 *
	 * @param string $value The value to sanitize.
	 *
	 * @return string
	 */
	public function sanitise_checkbox( $value ) {
		if ( '1' === $value ) {
			return true;
		}
		return false;
	}

	/**
	 * Sanitize non-zero number.
	 *
	 * @param string $value The value to sanitize.
	 *
	 * @return int
	 */
	public function sanitise_number( $value ) {
		if ( empty( $value ) || '0' === $value ) {
			return null;
		}
		return (int) filter_var( $value, FILTER_SANITIZE_NUMBER_INT );
	}

	/**
	 * Sanitize an array of settings inside a textarea.
	 *
	 * @param string $value The value to sanitize.
	 *
	 * @return array
	 */
	public function sanitise_textarea_assoc_array( $value ) {
		$rows    = preg_split( '/\r\n|[\r\n]/', $value );
		$options = array();

		foreach ( $rows as $key => $option ) {
			if ( '' === $option ) {
				continue;
			}

			$key_value = explode( ' : ', $option );

			if ( count( $key_value ) > 1 ) {
				$options[ $key ]['label'] = $key_value[1];
				$options[ $key ]['value'] = $key_value[0];
			} else {
				$options[ $key ]['label'] = $option;
				$options[ $key ]['value'] = $option;
			}
		}

		// Reindex array in case of blank lines.
		$options = array_values( $options );

		return $options;
	}

	/**
	 * Sanitize an array of settings inside a textarea.
	 *
	 * @param string $value The value to sanitize.
	 *
	 * @return array
	 */
	public function sanitise_textarea_array( $value ) {
		$rows    = preg_split( '/\r\n|[\r\n]/', $value );
		$options = array();

		foreach ( $rows as $key => $option ) {
			if ( '' === $option ) {
				continue;
			}

			$key_value = explode( ' : ', $option );

			if ( count( $key_value ) > 1 ) {
				$options[] = $key_value[0];
			} else {
				$options[] = $option;
			}
		}

		// Reindex array in case of blank lines.
		$options = array_values( $options );

		return $options;
	}
}
