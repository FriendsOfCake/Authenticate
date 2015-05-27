# Authenticate plugin

[![Build Status](https://travis-ci.org/FriendsOfCake/Authenticate.png?branch=cake3)](https://travis-ci.org/FriendsOfCake/Authenticate)
[![Coverage Status](https://coveralls.io/repos/FriendsOfCake/Authenticate/badge.png)](https://coveralls.io/r/FriendsOfCake/Authenticate)

Plugin containing some authenticate classes for AuthComponent.

Current classes:
* MultiColumnAuthenticate, allow login with multiple db columns in single username field
  For example username or email
* CookieAuthenticate, login with a cookie
* TokenAuthenticate, login with a token as url parameter or header

## Requirements

* CakePHP 3.0

## Installation

_[Composer]_

run: `composer require friendsofcake/authenticate:dev-cake3` or
add `"friendsofcake/authenticate":"dev-cake3"` to `require` section in your
application's `composer.json`.

## Usage

In your app's `config/bootstrap.php` add: `Plugin::load('FOC/Authenticate');`

## Configuration:

Setup the authentication class settings

### MultiColumnAuthenticate:

```php
    //in $components
    public $components = [
        'Auth' => [
            'authenticate' => [
                'FOC/Authenticate.MultiColumn' => [
                    'fields' => [
                        'username' => 'login',
                        'password' => 'password'
                    ],
                    'columns' => ['username', 'email'],
                    'userModel' => 'Users',
                    'scope' => ['Users.active' => 1]
                ]
            ]
        ]
    ];

    // Or in beforeFilter()
    $this->Auth->config('authenticate', [
        'FOC/Authenticate.MultiColumn' => [
            'fields' => [
                'username' => 'login',
                'password' => 'password'
            ],
            'columns' => ['username', 'email'],
            'userModel' => 'Users',
            'scope' => ['Users.active' => 1]
        ]
    ]);
```

### CookieAuthenticate:

```php
    //in $components
    public $components = [
        'Auth' => [
            'authenticate' => [
                'FOC/Authenticate.Cookie' => [
                    'fields' => [
                        'username' => 'login',
                        'password' => 'password'
                    ],
                    'userModel' => 'SomePlugin.Users',
                    'scope' => ['User.active' => 1]
                ]
            ]
        ]
    ];
    //Or in beforeFilter()
    $this->Auth->authenticate = [
        'FOC/Authenticate.Cookie' => [
            'fields' => [
                'username' => 'login',
                'password' => 'password'
            ],
            'userModel' => 'SomePlugin.Users',
            'scope' => ['Users.active' => 1]
        ]
    ];
```

### Setup both:

It will first try to read the cookie, if that fails will try with form data:
```php
    //in $components
    public $components = [
        'Auth' => [
            'authenticate' => [
                'FOC/Authenticate.Cookie' => [
                    'fields' => [
                        'username' => 'login',
                        'password' => 'password'
                    ],
                    'userModel' => 'SomePlugin.Users',
                    'scope' => ['User.active' => 1]
                ],
                'FOC/Authenticate.MultiColumn' => [
                    'fields' => [
                        'username' => 'login',
                        'password' => 'password'
                    ],
                    'columns' => ['username', 'email'],
                    'userModel' => 'Users',
                    'scope' => ['Users.active' => 1]
                ]
            ]
        ]
    ];
```

### Setting the cookie

Example for setting the cookie:
```php
<?php
App::uses('AppController', 'Controller');
/**
 * Users Controller
 *
 * @property User $User
 */
class UsersController extends AppController {

	public $components = ['Cookie'];

    public function login() {
        if ($this->request->is('post')) {
            $user = $this->Auth->identify();
            if ($user) {
                $this->Auth->setUser($user);
                $this->_setCookie();
                return $this->redirect($this->Auth->redirectUrl());
            }
            $this->Flash->error(__('Invalid username or password, try again'));
        }
    }

	protected function _setCookie() {
		if (!$this->request->data('remember_me')) {
			return false;
		}
		$data = [
			'username' => $this->request->data('username'),
			'password' => $this->request->data('password')
		);
		$this->Cookie->write('RememberMe', $data, true, '+1 week');
		return true;
	}

}
```

### TokenAuthenticate

```php
    //in $components
    public $components = [
        'Auth' => [
            'authenticate' => [
                'FOC/Authenticate.Token' => [
                    'parameter' => '_token',
                    'header' => 'X-MyApiTokenHeader',
                    'userModel' => 'Users',
                    'scope' => ['Users.active' => 1],
                    'fields' => [
                        'username' => 'username',
                        'password' => 'password',
                        'token' => 'public_key',
                    ],
                    'continue' => true
                ]
            ]
        ]
    ];
    //Or in beforeFilter()
    $this->Auth->config('authenticate', [
        'FOC/Authenticate.Token' => [
            'parameter' => '_token',
            'header' => 'X-MyApiTokenHeader',
            'userModel' => 'Users',
            'scope' => ['Users.active' => 1],
            'fields' => [
                'username' => 'username',
                'password' => 'password',
                'token' => 'public_key',
            ],
            'continue' => true
        ]
    ]);
```
