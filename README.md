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
    public $new_password, $confirmPsw, $oldPsw;

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
                'old_password' => 'oldPsw'
            ]
        ];
    }
}
```
