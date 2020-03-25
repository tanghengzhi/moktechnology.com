<?php
/*
This code is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

This code is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this code. If not, see http://www.gnu.org/licenses/gpl-2.0.html.
*/

if(is_admin())
{

  /** Enable color picker script **/
  function agev_enqueue_color_picker($hook_suffix) {

      wp_enqueue_style( 'wp-color-picker' );
      wp_enqueue_script( 'agev-wp-color-picker-alpha', plugins_url( 'wp-color-picker-alpha.min.js', __FILE__ ), array( 'wp-color-picker' ), '2.0.0', true );
  }
  add_action( 'admin_enqueue_scripts', 'agev_enqueue_color_picker' );

  add_action('admin_menu', function() {
      add_options_page( 'WP Age Verification Settings', 'Age Verification Settings', 'manage_options', 'agev-age-verification', 'agev_age_verification_page' );
  });


  add_action( 'admin_init', function() {
      register_setting( 'agev-age-verification-settings', 'age_overlay_color' );
      register_setting( 'agev-age-verification-settings', 'age_dialog_color' );
      register_setting( 'agev-age-verification-settings', 'age_dialog_title' );
      register_setting( 'agev-age-verification-settings', 'age_dialog_text' );
      register_setting( 'agev-age-verification-settings', 'age_confirm_text' );
      register_setting( 'agev-age-verification-settings', 'age_decline_text' );
      register_setting( 'agev-age-verification-settings', 'age_session_duration' );
      register_setting( 'agev-age-verification-settings', 'age_show_credits' );
  });


  function agev_age_verification_page() {
    global $ageOverlayColor, $ageDialogColor, $ageDialogTitle, $ageDialogText, $ageConfirmText, $ageDeclineText, $ageShowCredits, $ageSessionDuration; // Set scope
    ?>
      <div class="wrap">
        <h1>WordPress Responsive Age Verification Settings</h1>
        <p style="font-size: 12px;">
          This plugin is free software: you can redistribute it and/or modify it under the terms of the <a href="http://www.gnu.org/licenses/gpl-2.0.html">GNU General Public License</a><br>
          as published by the Free Software Foundation, either version 2 of the License, or any later version.
        </p>
        <form action="options.php" method="post">

          <?php
            settings_fields( 'agev-age-verification-settings' );
            do_settings_sections( 'agev-age-verification-settings' );
          ?>
          <table class="form-table">

              <tr>
                  <th>Content Overlay Color</th>
                  <td><input type="text" class="my-color-field" data-alpha="true" placeholder="#282828" name="age_overlay_color" value="<?php echo esc_attr( $ageOverlayColor ); ?>" size="50" /></td>
              </tr>

              <tr>
                  <th>Dialog Background Color</th>
                  <td><input type="text" class="my-color-field" data-alpha="true" placeholder="#ff4646" name="age_dialog_color" value="<?php echo esc_attr( $ageDialogColor ); ?>" size="50" /></td>
              </tr>

              <tr>
                  <th>Dialog Title</th>
                  <td><input type="text" placeholder="Are you 21 or older?" name="age_dialog_title" value="<?php echo esc_attr( $ageDialogTitle ); ?>" size="50" /></td>
              </tr>

              <tr>
                  <th>Dialog Text</th>
                  <td><textarea placeholder="This website requires you to be 21 years of age or older. Please verify your age to view the content, or click &quot;Exit&quot; to leave." name="age_dialog_text" rows="5" cols="50"><?php echo esc_attr( $ageDialogText ); ?></textarea></td>
              </tr>

              <tr>
                  <th>Button Confirm Text</th>
                  <td><input type="text" placeholder="I am over 21" name="age_confirm_text" value="<?php echo esc_attr( $ageConfirmText ); ?>" size="50" /></td>
              </tr>

              <tr>
                  <th>Button Decline Text</th>
                  <td><input type="text" placeholder="I am over 21" name="age_decline_text" value="<?php echo esc_attr( $ageDeclineText ); ?>" size="50" /></td>
              </tr>

              <tr>
                  <th>Verified Duration (in hours)</th>
                  <td><input type="number" placeholder="8760" name="age_session_duration" value="<?php echo esc_attr( $ageSessionDuration ); ?>" size="50" /></td>
              </tr>



              <tr>
                  <th>Credits</th>
                  <td>
                      <label>
                          <input type="checkbox" name="age_show_credits" <?php echo (intval($ageShowCredits) === 1) ? 'checked="checked"' : ''; ?> /><span style="font-size: 20px;">&#9924;&#65039;</span> Check this box to enable credits
                      </label><br/>
                  </td>
              </tr>

              <tr>
                  <td>
                  	<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">
                  	<a class="button button-info" value="Preview" style="margin-left: 10px;" onclick="window.open('<?php echo get_home_url(); ?>/?preview_age_verification=1')">Preview</a>
                  </td>
              </tr>

          </table>

        </form>
      </div>

      <p>Want to block those annoying spam comments? Get <a href="https://wordpress.org/plugins/anti-spam-zapper/">Anti-Spam Zapper, our free anti-spam plugin!</a></p>
      <p>Plugin created by <a href="https://www.designsmoke.com">DesignSmoke Web Design</a></p>

      <p><br><a href="https://wordpress.org/plugins/responsive-age-verification/#reviews">Review this plugin</a> | <a href="https://wordpress.org/support/plugin/responsive-age-verification/">Free plugin support/troubleshooting</a></p>

      <!-- Color picker script -->
      <script>
        jQuery(document).ready(function($){
            $('.my-color-field').wpColorPicker();
        });
      </script>


    <?php
  }


}
