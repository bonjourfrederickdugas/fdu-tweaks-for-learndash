<?php
namespace FDU\TweaksForLearnDash\Admin;

final class Menu {

    public static function register(): void {
        add_action( 'admin_menu', [ __CLASS__, 'add_submenu' ] );
    }

    public static function add_submenu(): void {
        add_submenu_page(
            'fdu-plugins',
            'FDU Tweaks',
            'Tweaks',
            'manage_options',
            'fdu-tweaks-for-learndash',
            [ __CLASS__, 'render' ]
        );
    }

    public static function render(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'tweaks';

        $all_settings = get_option( 'fdu_global_settings', [] );
        $plugin_file  = plugin_basename( FDU_TWEAKS_FILE );
        $current      = isset( $all_settings[ $plugin_file ] )
            ? (array) $all_settings[ $plugin_file ]
            : [];

        $tabs = [
            'tweaks' => 'Tweaks',
            'info'   => 'Info',
        ];

        ?>
        <div class="wrap">
            <h1>FDU Tweaks for LearnDash</h1>

            <nav class="nav-tab-wrapper">
                <?php foreach ( $tabs as $tab_key => $tab_label ) : ?>
                    <a href="<?php echo admin_url( 'admin.php?page=fdu-tweaks-for-learndash&tab=' . $tab_key ); ?>"
                       class="nav-tab <?php echo $active_tab === $tab_key ? 'nav-tab-active' : ''; ?>">
                        <?php echo esc_html( $tab_label ); ?>
                    </a>
                <?php endforeach; ?>
            </nav>

            <?php
            switch ( $active_tab ) {

                case 'info':
                    ?>
                    <p>Info content will be added here.</p>
                    <?php
                    break;

                case 'tweaks':
                default:
                    ?>
                    <form method="post" action="options.php">
                        <?php
                        settings_fields( 'fdu_global_settings_group' );
                        ?>

                        <table class="form-table" role="presentation">
                            <?php
                            do_action(
                                'fdu_render_plugin_settings_fields',
                                $plugin_file,
                                $current
                            );
                            ?>
                        </table>

                        <?php submit_button(); ?>
                    </form>
                    <?php
                    break;
            }
            ?>
        </div>
        <?php
    }
}