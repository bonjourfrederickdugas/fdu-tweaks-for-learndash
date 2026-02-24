<?php
namespace FDU\TweaksForLearnDash\Admin;

final class Settings {

    public static function init(): void {
        add_action(
            'fdu_render_plugin_settings_fields',
            [ __CLASS__, 'render_settings_fields' ],
            10,
            2
        );

        add_filter(
            'fdu_sanitize_plugin_settings',
            [ __CLASS__, 'sanitize_settings' ],
            10,
            3
        );
    }

    protected static function get_plugin_file(): string {
        return plugin_basename( FDU_TWEAKS_FILE );
    }

    public static function render_settings_fields( string $plugin_file, array $current ): void {
        if ( $plugin_file !== static::get_plugin_file() ) {
            return;
        }

        $groups = [
            'learndash' => [
                'title' => 'LearnDash',
                'flags' => [
                    'disable_dashboard'        => 'Disable LearnDash Course Dashboard',
                    'disable_payments'         => 'Disable LearnDash native e-commerce features (orders, payments) and force Closed Enrollment',
                    'disable_course_grid_meta' => 'Disable Course Grid metabox on posts',
                    'disable_certificates'     => 'Disable LearnDash Certificates',
                    'disable_learndash_blocks_headings' => 'Disable LearnDash block and shortcode headings (H2 / H3)',
                    'simplify_learndash_navigation' => 'Simplify LearnDash navigation (Previous / Next buttons)',
                    'disable_learndash_challenge_exams' => 'Disable LearnDash Challenge Exams',
                    'disable_learndash_groups' => 'Disable LearnDash Groups',
                ],
                'descriptions' => [
                    'disable_dashboard' =>
                        'Removes the LearnDash course dashboard from the WordPress admin for a cleaner interface.',
                    'disable_payments' =>
                        'Disables LearnDash e-commerce features such as orders and payments and enforces closed enrollment.',
                    'disable_course_grid_meta' =>
                        'Removes the Course Grid metabox when editing posts and pages.',
                    'disable_certificates' =>
                        'Completely disables LearnDash certificates across the site.',
                    'disable_learndash_blocks_headings' =>
                        'Prevents LearnDash blocks and shortcodes from outputting automatic heading markup.',
                    'simplify_learndash_navigation' =>
                        'Reduces or simplifies the Previous and Next navigation buttons in LearnDash content.',
                    'disable_learndash_challenge_exams' =>
                        'Disables the LearnDash Challenge Exams feature if it is not used.',
                    'disable_learndash_groups' =>
                        'Turns off LearnDash Groups functionality entirely.',
                ],
            ],
            'general' => [
                'title' => 'General',
                'flags' => [
                    'disable_wp_account_emails' => 'Disable WordPress account emails',
                    'disable_wp_search'         => 'Disable WordPress search',
                ],
                'descriptions' => [
                    'disable_wp_account_emails' =>
                        'Disables WordPress account-related emails such as new user notifications and password reset emails.',
                    'disable_wp_search' =>
                        'Completely disables WordPress search, including widgets, blocks, frontend queries, and the admin bar search.',
                ],
            ],
        ];

        ?>
        <tr>
            <td style="width:50%; vertical-align:top; padding-right:20px;">
                <h2><?php echo esc_html( $groups['learndash']['title'] ); ?></h2>

                <table class="widefat striped" role="presentation">
                    <tbody>
                    <?php
                    $learndash_flags = $groups['learndash']['flags'];
                    asort( $learndash_flags );
                    foreach ( $learndash_flags as $key => $label ) :
                        $enabled = ! empty( $current[ $key ] );
                        ?>
                        <tr>
                            <td style="width:90%;">
                                <strong><?php echo esc_html( $label ); ?></strong>
                                <p style="margin:4px 0 0; color:#646970;">
                                    <?php
                                    echo esc_html(
                                        $groups['learndash']['descriptions'][ $key ] ?? ''
                                    );
                                    ?>
                                </p>
                            </td>
                            <td style="width:10%; text-align:right;">
                                <label>
                                    <input type="checkbox"
                                           name="fdu_global_settings[<?php echo esc_attr( $plugin_file ); ?>][<?php echo esc_attr( $key ); ?>]"
                                           value="1"
                                           <?php checked( $enabled ); ?> />
                                </label>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </td>

            <td style="width:50%; vertical-align:top; padding-left:20px;">
                <h2><?php echo esc_html( $groups['general']['title'] ); ?></h2>

                <?php if ( empty( $groups['general']['flags'] ) ) : ?>
                    <p><em>No settings available yet.</em></p>
                <?php else : ?>
                    <table class="widefat striped" role="presentation">
                        <tbody>
                        <?php
                        foreach ( $groups['general']['flags'] as $key => $label ) :
                            $enabled = ! empty( $current[ $key ] );
                            ?>
                            <tr>
                                <td style="width:90%;">
                                    <strong><?php echo esc_html( $label ); ?></strong>
                                    <p style="margin:4px 0 0; color:#646970;">
                                        <?php
                                        echo esc_html(
                                            $groups['general']['descriptions'][ $key ] ?? ''
                                        );
                                        ?>
                                    </p>
                                </td>
                                <td style="width:10%; text-align:right;">
                                    <label>
                                        <input type="checkbox"
                                               name="fdu_global_settings[<?php echo esc_attr( $plugin_file ); ?>][<?php echo esc_attr( $key ); ?>]"
                                               value="1"
                                               <?php checked( $enabled ); ?> />
                                    </label>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </td>
        </tr>
        <?php
    }

    public static function sanitize_settings(
        array $sanitized,
        string $plugin_file,
        array $raw
    ): array {
        if ( $plugin_file !== static::get_plugin_file() ) {
            return $sanitized;
        }

        foreach ( [
            'disable_dashboard',
            'disable_payments',
            'disable_course_grid_meta',
            'disable_certificates',
            'disable_learndash_blocks_headings',
            'simplify_learndash_navigation',
            'disable_learndash_challenge_exams',
            'disable_learndash_groups',
            'disable_wp_account_emails',
            'disable_wp_search',
        ] as $key ) {
            $sanitized[ $key ] = ! empty( $raw[ $key ] ) ? 1 : 0;
        }

        return $sanitized;
    }
}