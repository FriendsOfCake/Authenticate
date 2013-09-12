# Authenticate plugin

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

run: `composer require ceeram/Authenticate` or add `ceeram/Authenticate` to `require` in your applications `composer.json`

_[Manual]_

* Download this: http://github.com/ceeram/Authenticate/zipball/master
* Unzip that download.
* Copy the resulting folder to app/Plugin
* Rename the folder you just copied to Authenticate

_[GIT Submodule]_

In your app directory type:
```
git submodule add git://github.com/ceeram/Authenticate.git Plugin/Authenticate
git submodule init
git submodule update
```

_[GIT Clone]_

In your plugin directory type
`git clone git://github.com/ceeram/Authenticate.git Authenticate`

## Usage

In `app/Config/bootstrap.php` add: `CakePlugin::load('Authenticate')`;

## Configuration:

Setup the authentication class settings

Example for MultiColumnAuthenticate:
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

Example for CookieAuthenticate:
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

Example to setup both, it will first try to read the cookie, if that fails will try with form data:
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

Read more on the wiki: https://github.com/ceeram/Authenticate/wiki/Set-Cookie