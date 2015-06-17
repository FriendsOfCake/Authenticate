<?php
namespace FOC\Authenticate\Auth;

use Cake\Auth\FormAuthenticate;
use Cake\Controller\ComponentRegistry;
use Cake\ORM\TableRegistry;

/**
 * An authentication adapter for AuthComponent
 *
 * Provides the ability to authenticate using POST data. The username form input
 * can be checked against multiple table columns, for instance username and email
 *
 * ```
 *    $this->Auth->config('authenticate', [
 *        'Authenticate.MultiColumn' => [
 *            'fields' => [
 *                'username' => 'username',
 *                'password' => 'password'
 *             ],
 *            'columns' => ['username', 'email'],
 *            'userModel' => 'Users',
 *            'scope' => ['User.active' => 1]
 *        ]
 *    ]);
 * ```
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class MultiColumnAuthenticate extends FormAuthenticate
{
    /**
     * Constructor
     *
     * Besides the keys specified in BaseAuthenticate::$_defaultConfig,
     * MultiColumnAuthenticate uses the following extra keys:
     *
     * - 'columns' Array of columns to check username form input against
     *
     * @param \Cake\Controller\ComponentRegistry $registry The Component registry
     *   used on this request.
     * @param array $config Array of config to use.
     */
    public function __construct(ComponentRegistry $registry, $config)
    {
        $this->_registry = $registry;

        $this->config([
            'columns' => [],
        ]);

        $this->config($config);
    }

    /**
     * Find a user record using the standard options.
     *
     * @param string $username The username/identifier.
     * @param string $password The password, if not provide password checking is
     *   skipped and result of find is returned.
     * @return bool|array Either false on failure, or an array of user data.
     */
    protected function _findUser($username, $password = null)
    {
        $userModel = $this->_config['userModel'];
        list($plugin, $model) = pluginSplit($userModel);
        $fields = $this->_config['fields'];
        $conditions = [$model . '.' . $fields['username'] => $username];

        $columns = [];
        foreach ($this->_config['columns'] as $column) {
            $columns[] = [$model . '.' . $column => $username];
        }
        $conditions = ['OR' => $columns];

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

        if ($password !== null) {
            $hasher = $this->passwordHasher();
            $hashedPassword = $result[$fields['password']];
            if (!$hasher->check($password, $hashedPassword)) {
                return false;
            }

            $this->_needsPasswordRehash = $hasher->needsRehash($hashedPassword);
            unset($result[$fields['password']]);
        }

        return $result;
    }
}
