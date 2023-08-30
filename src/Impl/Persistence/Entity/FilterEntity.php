<?php

namespace Jabe\Impl\Persistence\Entity;

use Jabe\EntityTypes;
use Jabe\Exception\NotValidException;
use Jabe\Filter\FilterInterface;
use Jabe\Impl\{
    AbstractQuery,
    ProcessEngineLogger,
    StoredQueryValidator
};
use Jabe\Impl\Db\{
    DbEntityInterface,
    DbEntityLifecycleAwareInterface,
    EnginePersistenceLogger,
    HasDbRevisionInterface,
    HasDbReferencesInterface,
    HasDbRevision
};
use Jabe\Impl\Json\{
    JsonObjectConverter,
    JsonTaskQueryConverter
};
use Jabe\Impl\Util\{
    EnsureUtil,
    JsonUtil
};
use Jabe\Query\QueryInterface;

class FilterEntity implements FilterInterface, DbEntityInterface, HasDbRevisionInterface, HasDbReferencesInterface, DbEntityLifecycleAwareInterface
{
    //protected static final EnginePersistenceLogger LOG = ProcessEngineLogger.PERSISTENCE_LOGGER;
    public static $queryConverter;
    protected $id;
    protected $resourceType;
    protected $name;
    protected $owner;
    protected $query;
    protected $properties = [];
    protected int $revision = 0;

    public function __construct(?string $resourceType = null)
    {
        $this->setResourceType($resourceType);
        $this->setQueryInternal("{}");
        if (self::$queryConverter === null) {
            self::$queryConverter = [EntityTypes::TASK => new JsonTaskQueryConverter()];
        }
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'resourceType' => $this->resourceType,
            'name' => $this->name,
            'owner' => $this->owner,
            'query' => serialize($this->query),
            'properties' => $this->properties,
            'revision' => $this->revision
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->resourceType = $data['resourceType'];
        $this->name = $data['name'];
        $this->owner = $data['owner'];
        $this->query = unserialize($data['query']);
        $this->properties = $data['properties'];
        $this->revision = $data['revision'];
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setResourceType(?string $resourceType): FilterInterface
    {
        EnsureUtil::ensureNotEmpty(NotValidException::class, "Filter resource type must not be null or empty", "resourceType", $resourceType);
        EnsureUtil::ensureNull(NotValidException::class, "Cannot overwrite filter resource type", "resourceType", $this->resourceType);

        $this->resourceType = $resourceType;
        return $this;
    }

    public function getResourceType(): ?string
    {
        return $this->resourceType;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): FilterInterface
    {
        EnsureUtil::ensureNotEmpty(NotValidException::class, "Filter name must not be null or empty", "name", $name);
        $this->name = $name;
        return $this;
    }

    public function getOwner(): ?string
    {
        return $this->owner;
    }

    public function setOwner(?string $owner): FilterInterface
    {
        $this->owner = $owner;
        return $this;
    }

    public function getQuery(): QueryInterface
    {
        return $this->query;
    }

    public function getQueryInternal(): ?string
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

    public function setQueryInternal(?string $query): void
    {
        EnsureUtil::ensureNotNull(NotValidException::class, "query", $query);
        $converter = $this->getConverter();
        $this->query = $converter->toObject(JsonUtil::asObject($query));
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function getPropertiesInternal(): ?string
    {
        return JsonUtil::asString($this->properties);
    }

    public function setProperties(array $properties): FilterInterface
    {
        $this->properties = $properties;
        return $this;
    }

    public function setPropertiesInternal(?string $properties): void
    {
        if ($properties !== null) {
            $json = JsonUtil::asObject($properties);
            $this->properties = JsonUtil::asMap($json);
        } else {
            $this->properties = null;
        }
    }

    public function getRevision(): ?int
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

    public function getDependentEntities(): array
    {
        return [];
    }
}
