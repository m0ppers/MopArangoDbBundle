<?php
namespace Mop\ArangoDbBundle\FOSUser\Model;

use ArangoDBClient\Connection;
use ArangoDBClient\Document;
use ArangoDBClient\DocumentHandler;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManager as BaseUserManager;
use FOS\UserBundle\Util\CanonicalizerInterface;
use Mop\ArangoDbBundle\FOSUser\User;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Validator\Constraint;

class UserManager extends BaseUserManager
{
    protected $connection;
    protected $class;
    protected $documentHandler;
    protected $collection;

    public function __construct(EncoderFactoryInterface $encoderFactory, CanonicalizerInterface $usernameCanonicalizer, CanonicalizerInterface $emailCanonicalizer, Connection $connection, $collection, $class)
    {
        $this->connection = $connection;
        $this->collection = $collection;
        $this->documentHandler = new DocumentHandler($this->connection);
        $this->class = $class;
        parent::__construct($encoderFactory, $usernameCanonicalizer, $emailCanonicalizer);
    }

    public function findUsers()
    {
        throw new \InvalidArgumentException('Not implemented');
    }

    private function findUsersBy(array $criteria)
    {
        $result = array();
        $class = $this->getClass();
        foreach ($this->documentHandler->getByExample($this->collection, $criteria) as $document) {
            $user = new $class;
            $this->fromDocument($user, $document);
            $result[] = $user;
        }
        return $result;
    }

    function deleteUser(UserInterface $user)
    {
        return $this->documentHandler->delete($this->collection, $user->getId());
    }

    function findUserBy(array $criteria)
    {
        $users = $this->findUsersBy($criteria);
        if (count($users) == 0) {
            throw new \Exception('User not found');
        }
        return $users[0];
    }

    private function fromDocument(UserInterface $user, Document $document)
    {
        if (!$user instanceof User) {
            throw new \InvalidArgumentException('Invalid user object');
        }
        $user->fromArray($document->getAll());
        $user->setId($document->getId());
        return $user;
    }

    function getClass()
    {
        return $this->class;
    }

    function reloadUser(UserInterface $user)
    {
        $document = $this->documentHandler->get($this->collection, $user->getId());
        $this->fromDocument($user, $document);
    }

    function updateUser(UserInterface $user)
    {
        $this->updateCanonicalFields($user);
        $this->updatePassword($user);

        $id = $user->getId();
        $data = $user->toArray();

        $document = new Document;

        foreach ($data as $k => $v) {
            if (isset($v)) {
                if (is_object($v)) {
                    if ($v instanceof \DateTime) {
                        $v = $v->getTimestamp();
                    } else {
                        throw new \Exception('Can\'t handle '.get_class($v));
                    }
                } else {
                    $document->$k = $v;
                }
            }
        }

        if ($id) {
            $this->documentHandler->updateById($this->collection, $id, $document);
        } else {
            $id = $this->documentHandler->add($this->collection, $document);
            $user->setId($id);
        }
    }

    function validateUnique(UserInterface $value, Constraint $constraint)
    {
        $this->updateCanonicalFields($value);
        $fields = array_map('trim', explode(',', $constraint->property));

        $criteria = array();
        foreach ($fields as $field) {
            $criteria[$field] = call_user_func_array(array($value, 'get'.ucfirst($field)), array());
        }

        $users = $this->findUsersBy($criteria);
        if (count($users) == 0) {
            return true;
        }

        foreach ($users as $user) {
            if ($value->isUser($user)) {
                return true;
            }
        }
        return false;
    }
}
