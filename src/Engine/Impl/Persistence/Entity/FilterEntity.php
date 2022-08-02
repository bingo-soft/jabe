<?php

namespace Jabe\Engine\Impl\Persistence\Entity;

use Jabe\Engine\EntityTypes;
use Jabe\Engine\Exception\NotValidException;
use Jabe\Engine\Filter\FilterInterface;
use Jabe\Engine\Impl\{
    AbstractQuery,
    ProcessEngineLogger,
    StoredQueryValidator
};
use Jabe\Engine\Impl\Db\{
    DbEntityInterface,
    DbEntityLifecycleAwareInterface,
    EnginePersistenceLogger,
    HasDbReferencesInterface,
    HasDbRevision
};
use Jabe\Engine\Impl\Json\{
    JsonObjectConverter,
    JsonTaskQueryConverter
};
use Jabe\Engine\Impl\Util\{
    EnsureUtil,
    JsonUtil
};
use Jabe\Engine\Query\QueryInterface;

class FilterEntity implements FilterInterface, \Serializable, DbEntityInterface, HasDbRevisionInterface, HasDbReferencesInterface, DbEntityLifecycleAwareInterface
{
    //protected static final EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;
    public static $queryConverter;
    protected $id;
    protected $resourceType;
    protected $name;
    protected $owner;
    protected $query;
    protected $properties = [];
    protected $revision = 0;

    public function __construct(?string $resourceType = null)
    {
        $this->setResourceType($resourceType);
        $this->setQueryInternal("{}");
        if (self::$queryConverter === null) {
            self::$queryConverter = [EntityTypes::TASK => new JsonTaskQueryConverter()];
        }
    }

    public function serialize()
    {
        return json_encode([
            'id' => $this->id,
            'resourceType' => $this->resourceType,
            'name' => $this->name,
            'owner' => $this->owner,
            'query' => serialize($this->query),
            'properties' => $this->properties,
            'revision' => $this->revision
        ]);
    }

    public function unserialize($data)
    {
        $json = json_decode($data);
        $this->id = $json->id;
        $this->resourceType = $json->resourceType;
        $this->name = $json->name;
        $this->owner = $json->owner;
        $this->query = unserialize($json->query);
        $this->properties = $json->properties;
        $this->revision = $json->revision;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setResourceType(string $resourceType): FilterInterface
    {
        EnsureUtil::ensureNotEmpty(NotValidException::class, "Filter resource type must not be null or empty", "resourceType", $resourceType);
        EnsureUtil::ensureNull(NotValidException::class, "Cannot overwrite filter resource type", "resourceType", $this->resourceType);

        $this->resourceType = $resourceType;
        return $this;
    }

    public function getResourceType(): string
    {
        return $this->resourceType;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): FilterInterface
    {
        EnsureUtil::ensureNotEmpty(NotValidException::class, "Filter name must not be null or empty", "name", $name);
        $this->name = $name;
        return $this;
    }

    public function getOwner(): string
    {
        return $this->owner;
    }

    public function setOwner(string $owner): FilterInterface
    {
        $this->owner = $owner;
        return $this;
    }

    public function getQuery(): QueryInterface
    {
        return $this->query;
    }

    public function getQueryInternal(): string
    {
        $converter = $this->getConverter();
        return $converter->toJson($this->query);
    }

    public function setQuery(QueryInterface $query): FilterInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "query", $query);
        $this->query = $query;
        return $this;
    }

    public function setQueryInternal(string $query): void
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "query", $query);
        $converter = $this->getConverter();
        $this->query = $converter->toObject(JsonUtil::asObject($query));
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getPropertiesInternal(): string
    {
        return JsonUtil::asString($this->properties);
    }

    public function setProperties(array $properties): FilterInterface
    {
        $this->properties = $properties;
        return $this;
    }

    public function setPropertiesInternal(string $properties): void
    {
        if ($properties !== null) {
            $json = JsonUtil::asObject($properties);
            $this->properties = JsonUtil::asMap($json);
        } else {
            $this->properties = null;
        }
    }

    public function getRevision(): int
    {
        return $this->revision;
    }

    public function setRevision(int $revision): void
    {
        $this->revision = $revision;
    }

    public function getRevisionNext(): int
    {
        return $this->revision + 1;
    }

    public function extend(QueryInterface $extendingQuery): FilterInterface
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "extendingQuery", $extendingQuery);

        if ($this->get_class($extendingQuery) != get_class($this->query)) {
            //throw LOG.queryExtensionException(query.getClass().getName(), extendingQuery.getClass().getName());
            throw new \Exception("Filter");
        }

        $copy = $this->copyFilter();
        $copy->setQuery($this->query->extend($extendingQuery));

        return $copy;
    }

    protected function getConverter(): JsonObjectConverter
    {
        if (array_key_exists($this->resourceType, $this->queryConverter)) {
            return $this->queryConverter[$this->resourceType];
        } else {
            //throw LOG.unsupportedResourceTypeException(resourceType);
            throw new \Exception("Filter");
        }
    }

    public function getPersistentState()
    {
        $persistentState = [];
        $persistentState["name"] = $this->name;
        $persistentState["owner"] = $this->owner;
        $persistentState["query"] = $this->query;
        $persistentState["properties"] = $this->properties;
        return $persistentState;
    }

    protected function copyFilter(): FilterEntity
    {
        $copy = new FilterEntity($this->getResourceType());
        $copy->setName($this->getName());
        $copy->setOwner($this->getOwner());
        $copy->setQueryInternal($this->getQueryInternal());
        $copy->setPropertiesInternal($this->getPropertiesInternal());
        return $copy;
    }

    public function postLoad(): void
    {
        if ($this->query !== null) {
            $this->query->addValidator(StoredQueryValidator::get());
        }
    }

    public function getReferencedEntityIds(): array
    {
        $referencedEntityIds = [];
        return $referencedEntityIds;
    }

    public function getReferencedEntitiesIdAndClass(): array
    {
        $referenceIdAndClass = [];
        return $referenceIdAndClass;
    }
}
