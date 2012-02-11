<?php
/**
 * iUser.interface.php
 * This is an interface that is needed by the Users Module.
 * We make this interface available, so that everyone can modify or create a new way
 * to manage users, without breaking the framework.
 *
 * @package This file is part of the Bolido Framework
 * @author    Michael Pratt <pratt@hablarmierda.net>
 * @link http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */
 if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

interface iUser
{
    public function id();
    public function name();
    public function getData();
    public function loadUserData($userId);
    public function update($data, $userId);
    public function can($permission); // Always return bool
    public function isLogged(); // Always return bool
}
?>