<?php
namespace FOC\Authenticate\Auth;

use Cake\Auth\BaseAuthenticate;
use Cake\Controller\ComponentRegistry;
use Cake\Error\Exception;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\ORM\TableRegistry;

/**
 * An authentication adapter for AuthComponent
 *
 * Provides the ability to authenticate using Token
 *
 * {{{
 *    $this->Auth->config('authenticate', [
 *        'FOC/Authenticate.Token' => [
 *            'parameter' => '_token',
 *            'header' => 'X-MyApiTokenHeader',
 *            'userModel' => 'Users',
 *            'scope' => ['User.active' => 1]
 *            'fields' => [
 *                'token' => 'public_key',
 *            ],
 *             'continue' => true
 *        ]
 *    ]);
 * }}}
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class TokenAuthenticate extends BaseAuthenticate
{

    /**
     * Constructor.
     *
     * Settings for this object.
     *
     * - `parameter` The url parameter name of the token.
     * - `header` The token header value.
     * - `userModel` The model name of the User, defaults to Users.
     * - `fields` The fields to use to identify a user by. Make sure `'token'` has
     *    been added to the array
     * - `scope` Additional conditions to use when looking up and authenticating users,
     *    i.e. `['Users.is_active' => 1].`
     * - `contain` Extra models to contain.
     * - `continue` Continue after trying token authentication or just throw the
     *   `unauthorized` exception.
     * - `unauthorized` Exception name to throw or a status code as an integer.
     *
     * @param \Cake\Controller\ComponentRegistry $registry The Component registry
     *   used on this request.
     * @param array $config Array of config to use.
     * @throws Cake\Error\Exception If header is not present.
     */
    public function __construct(ComponentRegistry $registry, $config)
    {
        $this->_registry = $registry;

        $this->config([
            'parameter' => '_token',
            'header' => 'X-ApiToken',
            'fields' => ['token' => 'token', 'password' => 'password'],
            'continue' => false,
            'unauthorized' => 'Cake\Network\Exception\BadRequestException'
        ]);

        $this->config($config);

        if (empty($this->_config['parameter']) &&
        empty($this->_config['header'])
        ) {
            throw new Exception(__d(
                'authenticate',
                'You need to specify token parameter and/or header'
            ));
        }
    }

    /**
     * Implemented because CakePHP forces you to.
     *
     * @param Request $request The request object.
     * @param Response $response The response object.
     * @return bool Always false.
     */
    public function authenticate(Request $request, Response $response)
    {
        return false;
    }

    /**
     * If unauthenticated, try to authenticate and respond.
     *
     * @param Request $request The request object.
     * @param Response $response The response object.
     * @return bool False on failure, user on success.
     * @throws HttpException Or the one specified using $settings['unauthorized'].
     */
    public function unauthenticated(Request $request, Response $response)
    {
        if ($this->_config['continue']) {
            return false;
        }
        if (is_string($this->_config['unauthorized'])) {
         // @codingStandardsIgnoreStart
            throw new $this->_config['unauthorized'];
         // @codingStandardsIgnoreEnd
        }
        $message = __d('authenticate', 'You are not authenticated.');
        throw new HttpException($message, $this->_config['unauthorized']);
    }

    /**
     * Get token information from the request.
     *
     * @param Request $request Request object.
     * @return mixed Either false or an array of user information
     */
    public function getUser(Request $request)
    {
        if (!empty($this->_config['header'])) {
            $token = $request->header($this->_config['header']);
            if ($token) {
                return $this->_findUser($token);
            }
        }
        if (!empty($this->_config['parameter']) &&
        !empty($request->query[$this->_config['parameter']])
        ) {
            $token = $request->query[$this->_config['parameter']];
            return $this->_findUser($token);
        }
        return false;
    }

    /**
     * Find a user record.
     *
     * @param string $username The token identifier.
     * @param string $password Unused password.
     * @return Mixed Either false on failure, or an array of user data.
     */
    protected function _findUser($username, $password = null)
    {
        $userModel = $this->_config['userModel'];
        list($plugin, $model) = pluginSplit($userModel);
        $fields = $this->_config['fields'];

        $conditions = [$model . '.' . $fields['token'] => $username];
        if (!empty($this->_config['scope'])) {
            $conditions = array_merge($conditions, $this->_config['scope']);
        }
        $table = TableRegistry::get($userModel)->find('all');
        if ($this->_config['contain']) {
            $table = $table->contain($this->_config['contain']);
        }

        $result = $table
            ->where($conditions)
            ->hydrate(false)
            ->first();

        if (empty($result)) {
            return false;
        }

        unset($result[$fields['password']]);

        return $result;
    }
}
