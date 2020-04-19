<?php namespace Jxckaroo\Simpl;

use Jxckaroo\Simpl\Exceptions\SimplORMException;
use Jxckaroo\Simpl\Interfaces\OrmInterface;
use Jxckaroo\Simpl\Validation\StaticValidation;
use ReflectionClass;
use ReflectionObject;
use ReflectionProperty;

/**
 * Class Orm
 * @package Jxckaroo\Simpl
 */
class Orm implements OrmInterface
{
    /**
     * ER Fine Tuning
     */
    const
        FILTER_IN_PREFIX = 'filterIn',
        FILTER_OUT_PREFIX = 'filterOut';
    /**
     * Loading options.
     */
    const
        PRIMARY_KEY_LOAD = 1,
        ARRAY_LOAD = 2,
        NEW_LOAD = 3,
        EMPTY_LOAD = 4;
    /**
     * @var \PDO $db
     *
     * Active database connection
     */
    protected static
        $db,
        $staticValidation;

    protected
        $parentObject,
        $ignoreKeyOnUpdate = true,
        $ignoreKeyOnInsert = true;

    private
        $loaderMethod,
        $dataLoaded,
        $newLoad = false,
        $modifiedFields = [];

    public function __construct($data = null, $loaderMethod = self::EMPTY_LOAD)
    {
        // Store data passed in raw format
        $this->dataLoaded = $data;
        $this->loaderMethod = $loaderMethod;

        // See which method we need to boot
        switch ($loaderMethod)
        {
            case self::PRIMARY_KEY_LOAD:
                $this->loadByPrimaryKey();
                break;
            case self::ARRAY_LOAD;
                $this->loadArray();
                break;
            case self::NEW_LOAD;
                $this->loadArray();
                $this->insert();
                break;
            case self::EMPTY_LOAD:
                $this->loadEmpty();
                break;
            default:
                $this->loadEmpty();
        }

        $this->initialise();
    }

    /**
     * Load active class by Primary Key
     *
     * @access private
     * @return void
     */
    private function loadByPrimaryKey()
    {
        $this->{self::getPrimaryKey()} = $this->dataLoaded;
        $this->loadFromDatabase();
    }

    /**
     * Get the primayry key field name for the active class.
     *
     * @access public
     * @static
     * @return string
     */
    private static function getPrimaryKey()
    {
        $className = get_called_class();
        return $className::$primaryKey;
    }

    /**
     * Return a record from the database based on the primary key.
     *
     * @access private
     * @throws \Exception
     * @return void
     */
    private function loadFromDatabase()
    {
        $sql = sprintf(
            "SELECT * FROM `%s` WHERE `%s` = '%s';",
            self::getTableName(),
            self::getPrimaryKey(),
            $this->id()
        );

        $results = self::$db->query($sql);

        if ($results->rowCount() < 1)
        {
            throw new \Exception(sprintf("%s record not found in database. (PK: %s)", get_called_class(), $this->id()), 2);
        }

        $record = $results->fetch();

        foreach ($record as $key => $value)
        {
            $this->{$key} = $value;
        }

        $this->databaseOutputFilters();
    }

    /**
     * Get the active class table name
     *
     * @access private
     * @return mixed
     */
    private static function getTableName()
    {
        $activeClass = get_called_class();
        return $activeClass::$table;
    }

    /**
     * Return the primary key from the loaded data
     *
     * @access private
     * @return mixed
     */
    private function id()
    {
        return $this->{self::getPrimaryKey()};
    }

    private function databaseOutputFilters()
    {
        $reflector = new ReflectionClass(get_class($this));

        foreach ($reflector->getMethods() AS $method)
            if (substr($method->name, 0, strlen(self::FILTER_OUT_PREFIX)) == self::FILTER_OUT_PREFIX)
                $this->{$method->name}();
    }

    private function loadArray()
    {
        // Set the object properties
        foreach ($this->dataLoaded as $field => $value)
        {
            $this->{$field} = $value;
        }

        // Extract database columns
        $this->databaseOutputFilters();
    }

    private function insert()
    {
        $array = $this->get();

        // Run any additional pre-inserts
        $this->preInsert($array);

        // ER Input Filters
        $array = $this->databaseInputFilters($array);

        // Remove any irrelevant data
        $array = array_intersect_key(
            $array,
            array_flip(
                $this->getColumnNames()
            )
        );

        // Remove the primary key?
        if ($this->ignoreKeyOnInsert == true)
            unset($array[self::getPrimaryKey()]);

        // Compile statement
        $fieldNames = $fieldMarkers = $types = $values = [];

        foreach ($array AS $key => $value)
        {
            $fieldNames[] = sprintf('`%s`', $key);
            $fieldMarkers[] = '?';
            $types[] = $this->parseValueType($value);
            $values[] = &$array[$key];
        }

        // Build our SQL
        $sql = sprintf(
            "INSERT INTO `%s` (%s) VALUES (%s)",
            self::getTableName(),
            implode(
                ", ",
                $fieldNames
            ),
            implode(
                ", ",
                $fieldMarkers
            )
        );

        // Run the insert
        $insert = self::$db->prepare($sql)->execute(
            [
                implode(", ", $values)
            ]
        );

        // Error checking
        if (!$insert)
        {
            throw new \Exception("Error inserting in to [" . self::getTableName() . "]" . "\n\n" . $sql);
        }

        // Set our primary key if it exists
        if (self::$db->lastInsertId())
        {
            $this->{self::getPrimaryKey()} = self::$db->lastInsertId();
        }

        // Mark the entry as old
        $this->isNew = false;

        // Load back from the database
        $this->loadFromDatabase();

        $this->postInsert();
    }

    public function update()
    {
        if ($this->isNewLoad())
            throw new \Exception('Unable to update "new" object. Object must be saved first.');

        $primaryKey = self::getPrimaryKey();
        $id = $this->id();

        // Run the input filters
        $array = $this->databaseInputFilters($this->get());

        $array = array_intersect_key(
            $array,
            array_flip(
                $this->getColumnNames()
            )
        );

        if ($this->ignoreKeyOnUpdate === true)
        {
            unset($array[$primaryKey]);
        }

        // Compile statement
        $fields = $types = $values = array();

        foreach ($array AS $key => $value)
        {
            $fields[] = sprintf('`%s` = ?', $key);
            $types[] = $this->parseValueType($value);
            $values[] = &$array[$key];
        }

        // Where
        $values[] = &$id;

        // Build our SQL
        $sql = sprintf(
            "UPDATE `%s` SET %s WHERE `%s` = ?",
            self::getTableName(),
            implode(
                ", ",
                $fields
            ),
            $primaryKey
        );

        // Run the update
        $update = self::$db->prepare($sql)->execute(
            $values
        );

        // Error checking
        if (!$update)
        {
            throw new \Exception("Error updating record in [" . self::getTableName() . "]" . "\n\n" . $sql);
        }

        $this->modifiedfields = array();

    }

    public function delete()
    {
        if ($this->isNewLoad())
        {
            throw new \Exception('Unable to delete object, record is new (and therefore doesn\'t exist in the database).');
        }

        $sql = sprintf(
            "DELETE FROM `%s` WHERE `%s` = ?",
            self::getTableName(),
            self::getPrimaryKey()
        );

        $delete = self::$db->prepare($sql)->execute(
            [
                $this->id()
            ]
        );

        if (!$delete)
        {
            throw new SimplORMException(self::$db->errorInfo(), self::$db->errorCode());
        }
    }

    /**
     * Get current object to array or return specific field value.
     *
     * @param bool $field
     * @return array
     */
    public function get($field = false)
    {
        if ($field == false)
        {
            return self::convertObjectToArray($this);
        }

        return $this->{$field};
    }

    /**
     * Convert an object to an array.
     *
     * @access public
     * @static
     * @param object $object
     * @return array
     */
    public static function convertObjectToArray ($object)
    {
        if (!is_object($object))
            return (array) $object;

        $array = array();
        $r = new ReflectionObject($object);

        foreach ($r->getProperties(ReflectionProperty::IS_PUBLIC) AS $key => $value)
        {
            $key = $value->getName();
            $value = $value->getValue($object);

            $array[$key] = is_object($value) ? self::convertObjectToArray($value) : $value;
        }

        return $array;
    }

    /**
     * Executed before any new records are created
     * Placeholder for subclasses
     */
    public function preInsert()
    {}

    private function databaseInputFilters($array)
    {
        $reflector = new ReflectionClass(get_class($this));

        foreach ($reflector->getMethods() AS $method)
            if (substr($method->name, 0, strlen(self::FILTER_IN_PREFIX)) == self::FILTER_IN_PREFIX)
                $array = $this->{$method->name}($array);

        return $array;
    }

    /**
     * Fetch column names of active table
     *
     * @access public
     * @return array
     */
    public function getColumnNames()
    {
        $results = self::$db->query(
            sprintf(
                "DESCRIBE %s",
                self::getTableName()
            )
        )->fetchAll(\PDO::FETCH_ASSOC);

        $response = [];

        foreach ($results as $row)
        {
            $response[] = $row['Field'];
        }

        return $response;
    }
    
    /**
     * Return the correct bindParam value
     *
     * @acces private
     * @param $value
     * @return int
     */
    private function parseValueType($value)
    {
        // Integers
        if (is_int($value))
        {
            return \PDO::PARAM_INT;
        }

        return \PDO::PARAM_STR;
    }

    /**
     * Executed after any new records are created
     * Placeholder for subclasses
     */
    private function postInsert()
    {}

    /**
     * Create an empty object instance
     *
     * @access private
     */
    private function loadEmpty()
    {
        foreach ($this->getColumnNames() as $field)
        {
            $this->{$field} = NULL;
        }

        $this->newLoad = true;
    }

    public function useConnection($pdo)
    {
        if ($pdo instanceof \PDO)
        {
            self::setConnection($pdo);
        }
    }

    /**
     * Set the active database connection
     *
     * @access public
     * @param \PDO $pdo
     */
    public static function setConnection(\PDO $pdo)
    {
        self::$db = $pdo;
    }

    public function save()
    {
        if ($this->isNewLoad())
        {
            $this->insert();
        }
        else {
            $this->update();
        }
    }

    public function isNewLoad()
    {
        return $this->newLoad;
    }

    public static function __callStatic($name, $arguments = [])
    {
        $validation_method = lcfirst(ucwords($name)). "Validation";

        if (!method_exists(StaticValidation::class, $validation_method))
            throw new \Exception("Call to unknown static method [" . lcfirst(ucwords($name)) . "].", 500);

        $validation = StaticValidation::$validation_method($arguments);
        $class = get_called_class();

        if ($validation == true)
        {
            switch ($name)
            {
                case 'find':
                    return new $class($arguments[0], 1);
                    break;
            }
        }

        // Return an empty load by default
        return new $class();
    }

    public function initialise()
    {
    }
}