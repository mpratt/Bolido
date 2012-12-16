<?php
/**
 * MainUserModule.php
 * This class emulates a User Management class. Its called when no user
 * Module is found.
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bolido\Module\main\models;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

class MainUserModule implements \Bolido\App\Interfaces\IUser
{
    public function id() { return 0; }
    public function token() { return ''; }
    public function name() { return ''; }
    public function getData() { return array(); }
    public function loadUserData($userId) { return array(); }
    public function update($data, $userId, $table = '') { return false; }
    public function can($permission) { return false; }
    public function isLogged() { return false; }
}
?>
