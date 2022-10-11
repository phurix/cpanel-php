<?php namespace Gufy\CpanelPhp;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

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
class Cpanel implements CpanelInterface
{
    use CpanelShortcuts;

    /**
     * @var string[] Sets of headers that will be sent at request.
     *
     * @since v1.0.0
     */
    protected array $headers = [];
    /**
     * Query timeout (Guzzle option)
     *
     * @since v1.0.0
     */
    protected int $timeout = 10;
    /**
     * Connection timeout (Guzzle option)
     *
     * @since v1.0.0
     */
    protected int $connection_timeout = 2;
    /**
     * Username of your whm server. Must be string
     *
     * @since v1.0.0
     */
    private string $username;
    /**
     * Password or long hash of your whm server.
     *
     * @since v1.0.0
     */
    private string $password;
    /**
     * Authentication type you want to use. You can set as 'hash' or 'password'.
     *
     * @since v1.0.0
     */
    private string $auth_type;
    /**
     * Host of your whm server. You must set it with full host with its port and protocol.
     *
     * @since v1.0.0
     */
    private string $host;
    private ?Client $client = null;

    /**
     * Class constructor. The options must contain username, host, and password.
     * @param array $options options that will be passed and processed
     * @since v1.0.0
     */
    public function __construct(array $options = [], Client $client = null)
    {
        if ($client) {
            $this->client = $client;
        }
        if (!empty($options)) {
            if (!empty($options['auth_type'])) {
                $this->setAuthType($options['auth_type']);
            }

            return $this->checkOptions($options)
                ->setHost($options['host'])
                ->setAuthorization($options['username'], $options['password']);
        }
        return $this;
    }

    /**
     * set authorization for access.
     * It only set 'username' and 'password'.
     *
     * @param string $username Username of your whm server.
     * @param string $password Password or long hash of your whm server.
     * @since v1.0.0
     */
    public function setAuthorization(string $username, string $password): Cpanel
    {
        $this->username = $username;
        $this->password = $password;
        return $this;
    }

    /**
     * checking options for 'username', 'password', and 'host'. If they are not set, some exception will be thrown.
     * @param array $options list of options that will be checked
     * @throws \InvalidArgumentException
     * @since v1.0.0
     */
    private function checkOptions(array $options): Cpanel
    {
        if (empty($options['username'])) {
            throw new \InvalidArgumentException('Username is not set', 2301);
        }
        if (empty($options['password'])) {
            throw new \InvalidArgumentException('Password or hash is not set', 2302);
        }
        if (empty($options['host'])) {
            throw new \InvalidArgumentException('CPanel Host is not set', 2303);
        }

        return $this;
    }

    /**
     * Magic method who will call the CPanel/WHM Api.
     *
     * @since v1.0.0
     */
    public function __call(string $function, array $arguments = []): array
    {
        return $this->runQuery($function, $arguments, true);
    }

    /**
     * The executor. It will run API function and get the data.
     *
     * @param string $action function name that will be called.
     * @param array $arguments list of parameters that will be attached.
     * @param bool $throw defaults to false, if set to true rethrow every exception.
     *
     * @return array results of API call
     *
     * @throws RuntimeException|ClientException
     *
     * @since v1.0.0
     */
    protected function runQuery(string $action, array $arguments = [], bool $throw = false): array
    {
        $client = $this->getClient();
        try {
            $response = $this->getResponse($client, $action, $arguments);
        } catch (ClientException $e) {
            if ($throw) {
                throw $e;
            }
            $response = $e->getResponse();
        }
        $json = (string)$response->getBody();
        return $this->jsonDecode($json);
    }

    protected function getClient(): Client
    {
        return $this->client ?: new Client();
    }

    protected function getResponse(Client $client, string $action, array $arguments = []): ResponseInterface
    {
        $host = $this->getHost();
        return $client->post($host . '/json-api/' . $action, [
            'headers' => $this->createHeader(),
            'verify' => false,
            'query' => $arguments,
            'timeout' => $this->getTimeout(),
            'connect_timeout' => $this->getConnectionTimeout()
        ]);
    }

    /**
     * get host of your whm server.
     *
     * @return string host of your whm server
     *
     * @since v1.0.0
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * set API Host.
     *
     * @param string $host Host of your whm server.
     * @since v1.0.0
     */
    public function setHost(string $host): Cpanel
    {
        $this->host = $host;
        return $this;
    }

    /**
     * Extend HTTP headers that will be sent.
     *
     * @return array list of headers that will be sent
     *
     * @since v1.0.0
     */
    private function createHeader()
    {
        $headers = $this->headers;

        $username = $this->getUsername();
        $auth_type = $this->getAuthType();

        if ('hash' == $auth_type) {
            $headers['Authorization'] = 'WHM ' . $username . ':' . preg_replace("'(\r|\n|\s|\t)'", '', $this->getPassword());
        } elseif ('password' == $auth_type) {
            $headers['Authorization'] = 'Basic ' . base64_encode($username . ':' . $this->getPassword());
        }
        return $headers;
    }

    /**
     * get username.
     * @return string return username
     * @since v1.0.0
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * get authentication type.
     * @return string get authentication type
     * @since v1.0.0
     */
    public function getAuthType(): string
    {
        return $this->auth_type;
    }

    /**
     * set Authentication Type.
     *
     * @param string $auth_type Authentication type for calling API.
     * @since v1.0.0
     */
    public function setAuthType(string $auth_type): Cpanel
    {
        $this->auth_type = $auth_type;
        return $this;
    }

    /**
     * get password or long hash.
     * @return string get password or long hash
     * @since v1.0.0
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * get timeout.
     *
     * @return integer timeout of the Guzzle request
     *
     * @since v1.0.0
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * set timeout.
     * @since v1.0.0
     */
    public function setTimeout(int $timeout): Cpanel
    {
        $this->timeout = $timeout;
        return $this;
    }

    /**
     * get connection timeout.
     *
     * @return integer connection timeout of the Guzzle request
     *
     * @since v1.0.0
     */
    public function getConnectionTimeout(): int
    {
        return $this->connection_timeout;
    }

    /**
     * set connection timeout.
     * @since v1.0.0
     */
    public function setConnectionTimeout(int $connection_timeout): Cpanel
    {
        $this->connection_timeout = $connection_timeout;
        return $this;
    }

    protected function jsonDecode(string $json): array
    {
        if (($decodedBody = json_decode($json, true)) === false) {
            throw new RuntimeException(json_last_error_msg(), json_last_error());
        }
        return $decodedBody;
    }

    /**
     * set some header.
     *
     * @param string $name key of header you want to add
     * @param string $value value of header you want to add
     * @since v1.0.0
     */
    public function setHeader(string $name, string $value = ''): Cpanel
    {
        $this->headers[$name] = $value;

        return $this;
    }

    /**
     * Use a cPanel API
     */
    public function cpanel($module, $function, $username, array $params = []): array
    {
        $action = 'cpanel';
        $params = array_merge($params, [
            'cpanel_jsonapi_version' => 2,
            'cpanel_jsonapi_module' => $module,
            'cpanel_jsonapi_func' => $function,
            'cpanel_jsonapi_user' => $username,
        ]);

        return $this->runQuery($action, $params);
    }

    /**
     * Use cPanel API 1 or use cPanel API 2 or use UAPI.
     *
     * @param $api (1 = cPanel API 1, 2 = cPanel API 2, 3 = UAPI)
     */
    public function execute_action($api, $module, $function, $username, array $params = []): array
    {
        $action = 'cpanel';
        $params = array_merge($params, [
            'cpanel_jsonapi_apiversion' => $api,
            'cpanel_jsonapi_module' => $module,
            'cpanel_jsonapi_func' => $function,
            'cpanel_jsonapi_user' => $username,
        ]);
        return $this->runQuery($action, $params);
    }
}
