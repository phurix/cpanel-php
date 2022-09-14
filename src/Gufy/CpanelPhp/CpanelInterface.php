<?php
namespace Gufy\CpanelPhp;


/**
 * cPanel/WHM API
 *
 * Provides easy to use class for calling some CPanel/WHM API functions.
 *
 * @author Mochamad Gufron <mgufronefendi@gmail.com>
 *
 * @version v1.0.2
 *
 * @link https://github.com/mgufrone/cpanel-php
 * @since v1.0.0
 */
interface CpanelInterface
{
    /**
     * set authorization for access.
     * It only set 'username' and 'password'.
     *
     * @param string $username Username of your whm server.
     * @param string $password Password or long hash of your whm server.
     *
     *
     *
     * @since v1.0.0
     */
    public function setAuthorization(string $username, string $password);

    /**
     * set API Host.
     *
     * @param string $host Host of your whm server.
     *
     *
     *
     * @since v1.0.0
     */
    public function setHost(string $host);

    /**
     * set Authentication Type.
     *
     * @param string $auth_type Authentication type for calling API.
     *
     *
     *
     * @since v1.0.0
     */
    public function setAuthType(string $auth_type);

    /**
     * set some header.
     *
     * @param string $name key of header you want to add
     * @param string $value value of header you want to add
     *
     *
     *
     * @since v1.0.0
     */
    public function setHeader(string $name, string $value = '');

    /**
     * get username.
     *
     * @return string return username
     *
     * @since v1.0.0
     */
    public function getUsername(): string;

    /**
     * get authentication type.
     *
     * @return string get authentication type
     *
     * @since v1.0.0
     */
    public function getAuthType(): string;

    /**
     * get password or long hash.
     *
     * @return string get password or long hash
     *
     * @since v1.0.0
     */
    public function getPassword(): string;

    /**
     * get host of your whm server.
     *
     * @return string host of your whm server
     *
     * @since v1.0.0
     */
    public function getHost(): string;

    /**
     * Use a cPanel API
     */
    public function cpanel($module, $function, $username, array $params = []);
}