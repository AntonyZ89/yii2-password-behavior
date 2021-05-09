Yii2 Password Behavior
======================
Behavior for manage password

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist antonyz89/yii2-password-behavior "*"
```

or add

```
"antonyz89/yii2-password-behavior": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Just add `PasswordBehavior::class` on Model's behavior function:

```php
public function behaviors()
{
    return [
        PasswordBehavior::class,
    ];
}
```

To work properly, your Model need have this four variables:

`$password_hash`, `$new_password`, `$confirm_password`, `$old_password`

If you have this variables, but the name is different, just do:

```php
/**
 * @property string $password
 */
class ExampleModel extends ActiveRecord implements IdentityInterface, UserCredentialsInterface {
    public $new_password, $confirmPsw, $oldPsw, $authorizationKey;

    // new_password can be skipped because they already exists
    
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => PasswordBehavior::class,
                'password_hash' => 'password',
                'confirm_password' => 'confirmPsw',
                'auth_key' => 'authorizationKey',
                'old_password' => 'oldPsw'
            ]
        ];
    }
}
```

If want to ignore `$confirm_password`, `$old_password` and/or `$auth_key`, just do:

```php
/**
 * @property string $password
 */
class ExampleModel extends ActiveRecord implements IdentityInterface, UserCredentialsInterface {
    public $new_password;

    // new_password can be skipped because they already exists
    
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => PasswordBehavior::class,
                'password_hash' => 'password',
                /*
                 * Will be ignored and comparison between 
                 * `$confirm_password` and `$new_password` or `$password_hash` will not happen
                 */
                'confirm_password' => false, 
                /*
                 * Will be ignored and comparison between 
                 * `old_password` and `$new_password` will not happen
                 */
                'old_password' => false,
                /*
                 * Will be ignored and when a new password is defined
                 * a new Authorization Key will not generated
                 */
                'auth_key' => false
            ]
        ];
    }
}
```

i18n
--

add in `common\config\main.php`

````php
'components' => [
    ...
    'i18n' => [
        'translations' => [
            'psw' => [
                'class' => PhpMessageSource::class,
                'basePath' => '@antonyz89/password_behaviour/messages',
            ]
        ],
    ]
];
````