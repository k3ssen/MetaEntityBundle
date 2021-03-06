<?php
declare(strict_types=1);

namespace K3ssen\MetaEntityBundle\MetaData;

use K3ssen\MetaEntityBundle\MetaData\EntityAnnotation\AnnotationInterface;
use K3ssen\MetaEntityBundle\MetaData\EntityAnnotation\OrmEntityAnnotation;
use K3ssen\MetaEntityBundle\MetaData\EntityAnnotation\OrmTableAnnotation;
use K3ssen\MetaEntityBundle\MetaData\Property\PrimitiveMetaPropertyInterface;
use K3ssen\MetaEntityBundle\MetaData\Property\RelationMetaPropertyInterface;
use K3ssen\MetaEntityBundle\MetaData\Property\MetaPropertyInterface;
use K3ssen\MetaEntityBundle\MetaData\Traits\MetaTraitInterface;
use Doctrine\Common\Inflector\Inflector;
use Doctrine\Common\Collections\ArrayCollection;

class MetaEntity implements MetaEntityInterface
{
    protected $name;

    protected $bundleNamespace;

    protected $subDir;

    protected $usages = [];

    protected $entityAnnotations = [];

    protected $traits = [];

    /** @var array */
    protected $properties = [];

    /** @var PrimitiveMetaPropertyInterface */
    protected $displayProperty;

    protected $customRepository = true;

    public function __construct(string $nameOrFullClassName)
    {
        if (strpos($nameOrFullClassName, ':') !== false) {
            throw new \InvalidArgumentException(sprintf('Cannot set metaEntity by shortcutNotation. Provide fullClassName instead; "%s" given', $nameOrFullClassName));
        } elseif (strpos($nameOrFullClassName, '\\') !== false) {
            $this->setFullClassName($nameOrFullClassName);
        } else {
            $this->setName($nameOrFullClassName);
        }

        $this->properties = new ArrayCollection();

        $this->addEntityAnnotation(new OrmTableAnnotation($this));
        $this->addEntityAnnotation(new OrmEntityAnnotation($this));
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = Inflector::classify($name);
        return $this;
    }

    public function getShortcutNotation()
    {
        return ($this->getBundleNamespace() ? $this->getBundleNamespace().':' : '') . ($this->getSubDir() ? $this->getSubDir() .'/' : ''). $this->getName();
    }

    public function getBundleNamespace(): ?string
    {
        return $this->bundleNamespace;
    }

    /**
     * Gets bundle name by retrieving the last part of the bundleNamespace
     * @return string
     */
    public function getBundleName(): ?string
    {
        if (strpos('\\', $this->getBundleNamespace()) !== false) {
            $parts = explode('\\', $this->getBundleNamespace());
            return array_pop($parts);
        }
        return $this->getBundleNamespace();
    }

    public function setFullClassName($fullClassName)
    {
        $parts = explode('\\Entity\\', $fullClassName);
        if (count($parts) === 0) {
            throw new \InvalidArgumentException(sprintf('Please make sure the entity has \\Entity\\ in its namespace. Got "%s"', $fullClassName));
        }
        $entityName = array_pop($parts);
        if ($bundleNamespace = implode('\\', $parts) !== static::NO_BUNDLE_NAMESPACE) {
            $this->setBundleNamespace(implode('\\', $parts));
        }
        if (strpos('\\', $entityName) !== false) {
            $subDirAndEntityParts = explode('\\', $entityName);
            $entityName = array_pop($subDirAndEntityParts);
            $this->setSubDir(implode(DIRECTORY_SEPARATOR, $subDirAndEntityParts));
        }
        $this->setName($entityName);
        return $this;
    }

    public function getFullClassName(): string
    {
        return $this->getNamespace().'\\'.$this->getName();
    }

    /**
     * Retrieve the namespace the entity resides in (= namespace without entityName)
     */
    public function getNamespace(): string
    {
        return ($this->getBundleNamespace() ? $this->getBundleNamespace() : static::NO_BUNDLE_NAMESPACE) . '\\Entity'.
            ($this->getSubDir() ? '\\'. $this->getSubDir() : '')
        ;
    }

    public function setBundleNamespace(?string $bundleNamespace)
    {
        $this->bundleNamespace = $bundleNamespace;
        return $this;
    }

    public function getSubDir(): ?string
    {
        return $this->subDir;
    }

    public function setSubDir(?string $subDir)
    {
        $this->subDir = $subDir ? Inflector::classify($subDir) : null;
        return $this;
    }

    public function getUsages(): ?array
    {
        return $this->usages;
    }

    public function addUsage($namespace, $alias = null)
    {
        $this->usages[$namespace] = $alias;
        return $this;
    }

    public function removeUsage($namespace)
    {
        unset($this->usages[$namespace]);
        return $this;
    }

    /**
     * @return MetaTraitInterface[]
     */
    public function getTraits(): ?array
    {
        return $this->traits;
    }

    public function addTrait(MetaTraitInterface $trait)
    {
        $this->traits[] = $trait;
    }

    public function getTableName(): ?string
    {
        return Inflector::pluralize(Inflector::tableize($this->getName()));
    }

    public function getRepositoryFullClassName(): ?string
    {
        return str_replace('Entity', 'Repository', $this->getNamespace()) . '\\' . $this->getName() . 'Repository';
    }

    public function getRepositoryNamespace(): ?string
    {
        return str_replace('Entity', 'Repository', $this->getNamespace());
    }

    /**
     * @return AnnotationInterface[]
     */
    public function getEntityAnnotations(): ?array
    {
        return $this->entityAnnotations;
    }

    public function addEntityAnnotation(AnnotationInterface $entityAnnotation)
    {
        $this->entityAnnotations[] = $entityAnnotation;
    }

    /**
     * @return MetaPropertyInterface[]|ArrayCollection
     */
    public function getProperties(): ArrayCollection
    {
        return $this->properties;
    }

    public function addProperty(MetaPropertyInterface $property)
    {
        if (!$this->getProperties()->contains($property)) {
            $this->getProperties()->add($property);
            $property->setMetaEntity($this);
        }
        return $this;
    }

    public function removeProperty(MetaPropertyInterface $property)
    {
        if ($this->getProperties()->contains($property)) {
            $this->getProperties()->removeElement($property);
        }
        return $this;
    }

    public function getCollectionProperties(): ArrayCollection
    {
        return $this->getProperties()->filter(function(MetaPropertyInterface $property){
            return $property->getReturnType() === 'Collection';
        });
    }

    /** @return ArrayCollection|RelationMetaPropertyInterface[] */
    public function getRelationshipProperties(): ArrayCollection
    {
        return $this->getProperties()->filter(function (MetaPropertyInterface $property) {
            return $property instanceof RelationMetaPropertyInterface;
        });
    }

    public function getDisplayProperty(): ?PrimitiveMetaPropertyInterface
    {
        return $this->displayProperty;
    }

    public function setDisplayProperty(?PrimitiveMetaPropertyInterface $displayProperty)
    {
        if ($displayProperty && !$this->getProperties()->contains($displayProperty)) {
            throw new \RuntimeException(sprintf('Cannot set property %s as display-property; This property hasn\'t been added to this entity yet', $displayProperty));
        }
        $this->displayProperty = $displayProperty;
    }

    public function getIdProperty(): ?PrimitiveMetaPropertyInterface
    {
        foreach ($this->getProperties() as $property) {
            if ($property instanceof  PrimitiveMetaPropertyInterface && $property->isId()) {
                return $property;
            }
        }
        return null;
    }

    public function hasCustomRepository(): bool
    {
        return $this->customRepository;
    }

    public function setUseCustomRepository(bool $customRepository)
    {
        $this->customRepository = $customRepository;
        return $this;
    }


    public function __toString()
    {
        return $this->getShortcutNotation();
    }
}