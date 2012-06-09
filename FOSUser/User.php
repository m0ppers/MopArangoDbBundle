<?php
namespace Mop\ArangoDbBundle\FOSUser;

use FOS\UserBundle\Document\User as BaseUser;

abstract class User extends BaseUser
{
    public function getFields()
    {
        return array('username',
                     'usernameCanonical',
                     'email',
                     'emailCanonical',
                     'enabled',
                     'salt',
                     'password',
                     'lastLogin',
                     'locked',
                     'expired',
                     'expiresAt',
                     'confirmationToken',
                     'passwordRequestedAt',
                     'roles',
                     'credentialsExpired',
                     'credentialsExpiredAt',
                    );
    }

    public final function toArray()
    {
        $result = array();
        // mop: grrrrrr
        $tryPrefix = array('get', 'is');
        foreach ($this->getFields() as $field) {
            foreach ($tryPrefix as $prefix) {
                $methodName = $prefix.ucfirst($field);
                if (method_exists($this, $methodName)) {
                    $result[$field] = call_user_func_array(array($this,$methodName), array());
                    break;
                }
            }
        }
        return $result;
    }

    public final function fromArray(array $data)
    {
        foreach ($this->getFields() as $field) {
            if (array_key_exists($field, $data)) {
                $methodName = 'set'.ucfirst($field);
                if (method_exists($this, $methodName)) {
                    call_user_func_array(array($this, $methodName), array($data[$field]));
                } elseif (in_array($field, get_object_vars($this))) {
                    $this->$field = $data[$field];
                } else {
                    throw new \Exception('Field '.$field.' unknown');
                }
            }
        }
    }

    public function setId($id)
    {
        $this->id = $id;
    }
}
