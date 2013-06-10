<?php
/**
 * MainUserModule.php
 * This class emulates a User Management class. Its called when no user
 * Module is found.
 *
 * @package Module.Main.Models
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bolido\Modules\main\models;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

class MainUserModule implements \Bolido\Interfaces\IUser
{
    /**
     * The \Bolido\Interfaces\IUser
     * has the proper documentation.
     */
    public function __construct() {}
    public function id() { return 0; }
    public function token() { return ''; }
    public function name() { return ''; }
    public function getData() { return array(); }
    public function loadUserData($userId) { return array(); }
    public function update(array $data, $userId = 0) { return false; }
    public function can($permission) { return false; }
    public function isLogged() { return false; }
}
?>
