<?php
namespace Mop\ArangoDbBundle\FOSUser\Model;

use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManager as BaseUserManager;
use FOS\UserBundle\Util\CanonicalizerInterface;
use Mop\ArangoDbBundle\FOSUser\User;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Validator\Constraint;
use triagens\ArangoDb\CollectionHandler;
use triagens\ArangoDb\Connection;
use triagens\ArangoDb\Document;
use triagens\ArangoDb\DocumentHandler;
use triagens\ArangoDb\Statement;

class UserManager extends BaseUserManager
{
    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var DocumentHandler
     */
    protected $documentHandler;

    /**
     * @var string
     */
    protected $collection;

    public function __construct(
        EncoderFactoryInterface $encoderFactory,
        CanonicalizerInterface $usernameCanonicalizer,
        CanonicalizerInterface $emailCanonicalizer,
        Connection $connection,
        $collection,
        $class
    ) {
        $this->connection = $connection;
        $this->collection = $collection;
        $this->documentHandler = new DocumentHandler($this->connection);
        $this->class = $class;
        parent::__construct($encoderFactory, $usernameCanonicalizer, $emailCanonicalizer);
    }

    /**
     * @return array
     * @throws \triagens\ArangoDb\ClientException
     * @author Mario Mueller
     */
    public function findUsers()
    {
        $aql = "FOR u in {$this->collection} RETURN u";
        $statement = new Statement(
            $this->connection, array(
            "query" => $aql,
            "batchSize" => 1000,
            "sanitize" => true,
        )
        );

        $values = $statement->execute()->getAll();
        $result = array();
        $cls = $this->getClass();
        foreach ($values as $user) {
            /* @var $user Document */
            $targetModel = new $cls;
            /* @var $targetModel User */
            $result[] = $this->fromDocument($targetModel, $user);
        }

        return $result;
    }

    /**
     * @param array $criteria
     *
     * @return array
     * @throws \triagens\ArangoDb\ClientException
     */
    private function findUsersBy(array $criteria)
    {
        $result = array();
        $class = $this->getClass();
        $collectionHandler = new CollectionHandler($this->connection);
        foreach ($collectionHandler->byExample($this->collection, $criteria) as $document) {
            /* @var $document Document */
            $user = new $class;
            $result[] = $this->fromDocument($user, $document);
        }
        return $result;
    }

    /**
     * @param UserInterface $user
     *
     * @return bool
     */
    public function deleteUser(UserInterface $user)
    {
        return $this->documentHandler->removeById($this->collection, $user->getId());
    }

    /**
     * @param array $criteria
     *
     * @return mixed
     * @throws \Exception
     */
    public function findUserBy(array $criteria)
    {
        if (count($criteria) == 1 && array_key_exists('id', $criteria)) {
            $class = $this->getClass();
            /* @var $document Document */
            $user = new $class;
            $documentHandler = new DocumentHandler($this->connection);
            $arangoDocument = $documentHandler->get($this->collection, $criteria['id']);
            return $this->fromDocument($user, $arangoDocument);
        }
        $users = $this->findUsersBy($criteria);
        if (count($users) == 0) {
            throw new \Exception('User not found');
        }
        return $users[0];
    }

    /**
     * @param UserInterface $user
     * @param Document      $document
     *
     * @return UserInterface
     * @author Mario Mueller
     */
    private function fromDocument(UserInterface $user, Document $document)
    {
        if (!$user instanceof User) {
            throw new \InvalidArgumentException('Invalid user object');
        }
        $user->fromArray($document->getAll());
        $user->setId($document->getId());
        return $user;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param UserInterface $user
     */
    public function reloadUser(UserInterface $user)
    {
        $document = $this->documentHandler->get($this->collection, $user->getId());
        $this->fromDocument($user, $document);
    }

    /**
     * @param UserInterface $user
     *
     * @throws \Exception
     * @throws \triagens\ArangoDb\ClientException
     */
    public function updateUser(UserInterface $user)
    {
        /* @var $user User */
        $this->updateCanonicalFields($user);
        $this->updatePassword($user);

        $id = $user->getId();
        $data = $user->toArray();

        $document = new Document();

        foreach ($data as $k => $v) {
            if (isset($v)) {
                if (is_object($v)) {
                    if ($v instanceof \DateTime) {
                        $v = $v->getTimestamp();
                        $document->$k = $v;
                    } else {
                        throw new \Exception('Can\'t handle ' . get_class($v));
                    }
                } else {
                    $document->$k = $v;
                }
            }
        }

        if (!empty($id)) {
            $this->documentHandler->updateById($this->collection, $id, $document);
        } else {
            $id = $this->documentHandler->save($this->collection, $document);
            $user->setId($id);
        }
    }

    /**
     * @param UserInterface $value
     * @param Constraint    $constraint
     *
     * @return bool
     */
    public function validateUnique(UserInterface $value, Constraint $constraint)
    {
        /* @var $value User */
        $this->updateCanonicalFields($value);

        $dataArray = $constraint->getTargets();

        if (!is_array($constraint->getTargets())) {
            $dataArray = explode(',', $constraint->getTargets());
        }

        $fields = array_map('trim', $dataArray);
        $criteria = array();

        $valueData = $value->toArray();
        foreach ($fields as $field) {
            $criteria[$field] = $valueData[$field];
        }

        $users = $this->findUsersBy($criteria);
        if (count($users) === 0) {
            return true;
        }

        foreach ($users as $user) {
            if ($value->isUser($user)) {
                return false;
            }
        }
        return true;
    }
}
