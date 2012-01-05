<?php
/**
 * Index.Module.php, Main Module.
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

class main extends ModuleAdapter
{
    /**
     * Shows a 404 error!
     * @return void
     */
    public function index() { $this->error->display('Page not Found', 404); }

    /**
     * Keeps Session Alive.
     * @return void
     */
    public function alive()
    {
        header('Cache-Control: no-cache');
        header('Content-type: image/gif');
        $img = imagecreate(1, 1);
        imagegif($img);
        imagedestroy($img);
        die();
    }
}
?>