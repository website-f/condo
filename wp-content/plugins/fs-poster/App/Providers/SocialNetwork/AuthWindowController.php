<?php

namespace FSPoster\App\Providers\SocialNetwork;


abstract class AuthWindowController
{

    public static function error ( $message = '' )
    {
        if ( empty( $message ) ) {
            $message = fsp__( 'An error occurred while processing your request! Please close the window and try again' );
        }

        echo '<div>' . esc_html( $message ) . '</div>';
        ?>
        <script type="application/javascript">
            (function () {
                const payload = {
                    origin: "FS_POSTER",
                    error: <?php echo json_encode($message); ?>
                };

                let sent = false;

                try {
                    if (
                        window.opener &&
                        window.opener.FSPosterToast &&
                        typeof window.opener.FSPosterToast.error === "function"
                    ) {
                        window.opener.FSPosterToast.error(payload.error);
                        sent = true;
                    }
                } catch (e) {
                    sent = false;
                }

                if (!sent) {
                    try {
                        const bc = new BroadcastChannel("fs_poster_auth");
                        bc.postMessage(payload);
                        bc.close();
                    } catch (e) {}
                }

                window.close();
            })();
        </script>
        <?php

        exit();
    }

    public static function closeWindow ( $channels )
    {
        echo '<div>' . fsp__( 'Loading...' ) . '</div>';
        ?>
        <script type="application/javascript">
            (function () {
                const payload = {
                  origin: "FS_POSTER",
                  channels: <?php echo json_encode($channels); ?>
                };

                const targetOrigin = "<?php echo site_url(); ?>";

                let sent = false;

                try {
                    if (window.opener && typeof window.opener.postMessage === "function") {
                        window.opener.postMessage(payload, targetOrigin);
                        sent = true;
                    }
                } catch (e) {
                    sent = false;
                }

                if (!sent) {
                    try {
                        const bc = new BroadcastChannel("fs_poster_auth");
                        bc.postMessage(payload);
                        bc.close();
                    } catch (e) {}
                }

                window.close();
            })();
        </script>
        <?php

        exit();
    }

}