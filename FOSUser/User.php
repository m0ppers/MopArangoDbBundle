<?php
namespace Mop\ArangoDbBundle\FOSUser;

use FOS\UserBundle\Document\User as BaseUser;

abstract class User extends BaseUser
{
    protected $fields = array(
        'username',
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
        'credentialsExpireAt',
    );

    /**
     * @return string[]
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return mixed[string]
     */
    public function toArray()
    {
        $result = array();
        foreach ($this->getFields() as $field) {
            $result[$field] = $this->{$field};
        }
        return $result;
    }

    /**
     * Sets the data from any flat array.
     *
     * The previous implementation was unnecessarily final and
     * threw exceptions on unknown properties. There is no need from
     * my perspective, so this is a much less complex implementation.
     *
     * @param array $data
     *
     * @author Mario Mueller
     */
    public function fromArray(array $data)
    {
        foreach (array_intersect_key($data, $this->getFields()) as $key => $value) {
            $this->{$key} = $data[$value];
        }
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
}
