# Authenticate plugin

[![Build Status](https://travis-ci.org/FriendsOfCake/Authenticate.png?branch=master)](https://travis-ci.org/FriendsOfCake/Authenticate)
[![Coverage Status](https://coveralls.io/repos/FriendsOfCake/Authenticate/badge.png)](https://coveralls.io/r/FriendsOfCake/Authenticate)

Plugin containing some authenticate classes for AuthComponent.

Current classes:
* MultiColumnAuthenticate, allow login with multiple db columns in single username field
  For example username or email
* CookieAuthenticate, login with a cookie
* TokenAuthenticate, login with a token as url parameter or header

GoogleAuthenticate is moved to separate repo: https://github.com/ceeram/GoogleAuthenticate

## Requirements

* PHP 5.3
* CakePHP 2.x

## Installation

_[Composer]_

run: `composer require friendsofcake/authenticate` or add `friendsofcake/authenticate` to `require` in your applications `composer.json`

_[Manual]_

* Download this: http://github.com/FriendsOfCake/Authenticate/zipball/master
* Unzip that download.
* Copy the resulting folder to app/Plugin
* Rename the folder you just copied to Authenticate

_[GIT Submodule]_

In your app directory type:
```
git submodule add git://github.com/FriendsOfCake/Authenticate.git Plugin/Authenticate
git submodule init
git submodule update
```

_[GIT Clone]_

In your plugin directory type
`git clone git://github.com/FriendsOfCake/Authenticate.git Authenticate`

## Usage

In `app/Config/bootstrap.php` add: `CakePlugin::load('Authenticate')`;

## Configuration:

Setup the authentication class settings

### MultiColumnAuthenticate:

```php
    //in $components
    public $components = array(
        'Auth' => array(
            'authenticate' => array(
                'Authenticate.MultiColumn' => array(
                    'fields' => array(
                        'username' => 'login',
                        'password' => 'password'
                    ),
                    'columns' => array('username', 'email'),
                    'userModel' => 'User',
                    'scope' => array('User.active' => 1)
                )
            )
        )
    );
    //Or in beforeFilter()
    $this->Auth->authenticate = array(
        'Authenticate.MultiColumn' => array(
            'fields' => array(
                'username' => 'login',
                'password' => 'password'
            ),
            'columns' => array('username', 'email'),
            'userModel' => 'User',
            'scope' => array('User.active' => 1)
        )
    );
```

### CookieAuthenticate:

```php
    //in $components
    public $components = array(
        'Auth' => array(
            'authenticate' => array(
                'Authenticate.Cookie' => array(
                    'fields' => array(
                        'username' => 'login',
                        'password' => 'password'
                    ),
                    'userModel' => 'SomePlugin.User',
                    'scope' => array('User.active' => 1)
                )
            )
        )
    );
    //Or in beforeFilter()
    $this->Auth->authenticate = array(
        'Authenticate.Cookie' => array(
            'fields' => array(
                'username' => 'login',
                'password' => 'password'
            ),
            'userModel' => 'SomePlugin.User',
            'scope' => array('User.active' => 1)
        )
    );
```

### Setup both:

It will first try to read the cookie, if that fails will try with form data:
```php
    //in $components
    public $components = array(
        'Auth' => array(
            'authenticate' => array(
                'Authenticate.Cookie' => array(
                    'fields' => array(
                        'username' => 'login',
                        'password' => 'password'
                    ),
                    'userModel' => 'SomePlugin.User',
                    'scope' => array('User.active' => 1)
                ),
                'Authenticate.MultiColumn' => array(
                    'fields' => array(
                        'username' => 'login',
                        'password' => 'password'
                    ),
                    'columns' => array('username', 'email'),
                    'userModel' => 'User',
                    'scope' => array('User.active' => 1)
                )
            )
        )
    );
```

### Security

For enhanced security, make sure you add this code to your `AppController::beforeFilter()` if you intend to use Cookie
authentication:

```php
public function beforeFilter() {
  $this->Cookie->type('rijndael'); //Enable AES symetric encryption of cookie
}
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

	public $components = array('Cookie');

	public function beforeFilter() {
		$this->Cookie->type('rijndael');
	}

	public function login() {
		if ($this->Auth->loggedIn() || $this->Auth->login()) {
			$this->_setCookie($this->Auth->user('id'));
			$this->redirect($this->Auth->redirect());
		}
	}

	protected function _setCookie($id) {
		if (!$this->request->data('User.remember_me')) {
			return false;
		}
		$data = array(
			'username' => $this->request->data('User.username'),
			'password' => $this->request->data('User.password')
		);
		$this->Cookie->write('User', $data, true, '+1 week');
		return true;
	}

	public function logout() {
		$this->Auth->logout();
		$this->Cookie->delete('User');
		$this->Session->setFlash('Logged out');
		$this->redirect($this->Auth->redirect('/'));
	}
}
```

### TokenAuthenticate

```php
    //in $components
    public $components = array(
        'Auth' => array(
            'authenticate' = array(
                'Authenticate.Token' => array(
                    'parameter' => '_token',
                    'header' => 'X-MyApiTokenHeader',
                    'userModel' => 'User',
                    'scope' => array('User.active' => 1),
                    'fields' => array(
                        'username' => 'username',
                        'password' => 'password',
                        'token' => 'public_key',
                    ),
                    'continue' => true
                )
            )
        )
    );
    //Or in beforeFilter()
    $this->Auth->authenticate = array(
        'Authenticate.Token' => array(
            'parameter' => '_token',
            'header' => 'X-MyApiTokenHeader',
            'userModel' => 'User',
            'scope' => array('User.active' => 1),
            'fields' => array(
                'username' => 'username',
                'password' => 'password',
                'token' => 'public_key',
            ),
            'continue' => true
        )
    );
```
