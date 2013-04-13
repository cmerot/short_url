<?php
namespace Chocopoche\Composer;

/**
 * Bijective helper to encode integers to string and vice versa.
 *
 * @see http://www.flickr.com/groups/api/discuss/72157616713786392/
 *
 * @author  Corentin Merot <co@tmb.io>
 */
class Script
{
    private static $folders = array(
        'web/qr'
    );

    public static function install($event)
    {
        $old_umask = umask(0);
        foreach (self::$folders as $folder) {
            if (!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }
        }
        umask($old_umask);
        echo "You may now have to fix perms by typing `chmod -R a+rX vendor/google`\n";
    }
}
