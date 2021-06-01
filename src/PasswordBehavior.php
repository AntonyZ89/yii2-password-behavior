<?php


namespace antonyz89\password_behavior;

use Yii;
use yii\base\Behavior;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\db\ActiveRecord;

/**
 * Class PasswordBehaviour
 * @package common\behavior
 *
 * @author Antony Gabriel Pereira
 *
 * @link https://github.com/AntonyZ89
 *
 * @property ActiveRecord|Model $owner
 */
class PasswordBehavior extends Behavior
{

    /**
     * @var string
     *
     * Used on create
     */
    public $password_hash;

    /**
     * @var string
     *
     * Used on update
     */
    public $new_password;

    /**
     * @var string|false
     *
     * Set this property to false if you do not want to
     * compare `$confirm_password` with `$password_hash` (create) or `$new_password` (update)
     */
    public $confirm_password;

    /**
     * @var string|false
     *
     * Set this property to false if you do not want to compare `$new_password` with `$old_password`
     */
    public $old_password;

    /**
     * @var string|false
     *
     * Set this property to false if you do not want to regenerate authentication key after change password
     */
    public $auth_key;

    private $already_initiated = false;

    /**
     * @throws InvalidConfigException
     */
    public function customInit()
    {
        extract(get_object_vars($this));
        $compact = compact('password_hash', 'new_password', 'confirm_password', 'old_password', 'auth_key');

        foreach ($compact as $variable => $value) {
            if ($value !== null) {
                continue;
            }

            if (($this->owner instanceof ActiveRecord && $this->owner->hasAttribute($variable)) || $this->owner->hasProperty($variable)) {
                $this->$variable = $variable;
                continue;
            }

            throw new InvalidConfigException("PasswordBehaviour: \"$$variable\" is required.");
        }

        $this->already_initiated = true;
    }

    /**
     * @inheritDoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_INIT => 'customInit',
            ActiveRecord::EVENT_BEFORE_INSERT => 'check',
            ActiveRecord::EVENT_BEFORE_UPDATE => 'check',
            Model::EVENT_AFTER_VALIDATE => 'validate'
        ];
    }

    /**
     * @throws Exception
     */
    public function check()
    {
        if ($this->owner->isNewRecord) { // create
            $this->setPassword($this->owner->{$this->password_hash});
        } else if ($this->owner->{$this->new_password}) { // update
            $this->setPassword($this->owner->{$this->new_password});
        }
    }

    public function validate()
    {
        if ($this->owner->hasErrors()) {
            return;
        }

        !$this->already_initiated && $this->customInit();

        if ($this->owner->isNewRecord) {
            if (
                $this->owner->{$this->password_hash} &&
                $this->confirm_password !== false &&
                $this->owner->{$this->password_hash} !== $this->owner->{$this->confirm_password}
            ) {
                $this->owner->addErrors([
                    'password_hash' => Yii::t('psw', 'The passwords are different.'),
                    'confirm_password' => Yii::t('psw', 'The passwords are different.')
                ]);
            }
        } else if (
            $this->confirm_password !== false
            && ($this->owner->{$this->new_password} || $this->owner->{$this->confirm_password})
        ) {
            if ($this->owner->{$this->new_password} !== $this->owner->{$this->confirm_password}) {
                $this->owner->addErrors([
                    'new_password' => Yii::t('psw', 'The passwords are different.'),
                    'confirm_password' => Yii::t('psw', 'The passwords are different.')
                ]);
            } else if (!$this->validatePassword($this->owner->{$this->old_password})) {
                $this->owner->addError('old_password', Yii::t('psw', 'Incorrect password.'));
            }
        }
    }

    /**
     * @param $password
     * @throws Exception
     */
    protected function setPassword($password)
    {
        $this->owner->{$this->password_hash} = Yii::$app->security->generatePasswordHash($password);
        $this->setAuthKey();
    }

    /**
     * @throws Exception
     */
    protected function setAuthKey()
    {
        if ($this->auth_key !== false) {
            $this->owner->{$this->auth_key} = Yii::$app->security->generateRandomString();
        }
    }

    /**
     * @param $password
     * @return bool
     */
    protected function validatePassword($password)
    {
        try {
            return Yii::$app->security->validatePassword($password, $this->owner->{$this->password_hash});
        } catch (\Exception $e) {
            return false;
        }
    }
}
