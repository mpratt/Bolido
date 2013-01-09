<?php
/**
 * IUser.php
 * This is an interface that is needed by the Users Module.
 * We make this interface available, so that everyone can modify or create a new way
 * to manage users, without breaking the framework.
 *
 * @package This file is part of the Bolido Framework
 * @author  Michael Pratt <pratt@hablarmierda.net>
 * @link    http://www.michael-pratt.com/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace Bolido\Interfaces;

if (!defined('BOLIDO'))
    die('The dark fire will not avail you, Flame of Udun! Go back to the shadow. You shall not pass!');

interface IUser
{
    /**
     * Gets the id of the current user
     *
     * @return int
     */
    public function id();

    /**
     * Gets a private token associated to the user
     *
     * @return string
     */
    public function token();

    /**
     * Returns the name of the user
     *
     * @return string
     */
    public function name();

    /**
     * Returns an array with the data of the current user
     *
     * @return array
     */
    public function getData();

    /**
     * Returns an array with the data of a specific user
     *
     * @param int $userID
     * @return array
     */
    public function loadUserData($userId);

    /**
     * Updates the information of a user
     *
     * @param array $data
     * @param int $userID
     * @return bool
     */
    public function update(array $data, $userId);

    /**
     * Checks if the current user has permission to do something
     *
     * @param string $permission The name of the permission
     * @return bool
     */
    public function can($permission);

    /**
     * Checks if the current user is logged on the system
     *
     * @return bool
     */
    public function isLogged();
}
?>
