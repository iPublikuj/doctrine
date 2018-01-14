<?php
/**
 * EntityMapper.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:DoctrineCrud!
 * @subpackage     Mapping
 * @since          1.0.0
 *
 * @date           29.01.14
 */

declare(strict_types = 1);

namespace IPub\DoctrineCrud\Mapping;

use Doctrine\Common;
use Doctrine\ORM;

use Nette;
use Nette\Reflection;
use Nette\Utils;

use IPub;
use IPub\DoctrineCrud;
use IPub\DoctrineCrud\Entities;
use IPub\DoctrineCrud\Exceptions;
use IPub\DoctrineCrud\Validation;

/**
 * Doctrine CRUD entity mapper
 *
 * @package        iPublikuj:DoctrineCrud!
 * @subpackage     Mapping
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
final class EntityMapper implements IEntityMapper
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/**
	 * @var Validation\ValidatorProxy|Validation\IValidator
	 */
	private $validators;

	/**
	 * @var Common\Persistence\ManagerRegistry
	 */
	private $managerRegistry;

	/**
	 * @var Common\Annotations\AnnotationReader
	 */
	private $annotationReader;

	/**
	 * @param Validation\IValidator $validators
	 * @param Common\Annotations\Reader $annotationReader
	 * @param Common\Persistence\ManagerRegistry $managerRegistry
	 */
	public function __construct(
		Validation\IValidator $validators,
		Common\Annotations\Reader $annotationReader,
		Common\Persistence\ManagerRegistry $managerRegistry
	) {
		$this->validators = $validators;
		$this->annotationReader = $annotationReader;
		$this->managerRegistry = $managerRegistry;
	}

	/**
	 * {@inheritdoc}
	 */
	public function fillEntity(Utils\ArrayHash $values, Entities\IEntity $entity, $isNew = FALSE) : Entities\IEntity
	{
		$reflectionClass = new Reflection\ClassType(get_class($entity));

		// Hack for proxy classes...
		if ($reflectionClass->implementsInterface(Common\Proxy\Proxy::class)) {
			// ... we need to extract entity class name from proxy class
			$entityClass = $reflectionClass->getParentClass()->getName();

		} else {
			$entityClass = get_class($entity);
		}

		/** @var ORM\Mapping\ClassMetadata $classMetadata */
		$classMetadata = $this->managerRegistry->getManagerForClass($entityClass)->getClassMetadata($entityClass);

		foreach (array_merge($classMetadata->getFieldNames(), $classMetadata->getAssociationNames()) as $fieldName) {

			try {
				$propertyReflection = new Nette\Reflection\Property($entityClass, $fieldName);

			} catch (\ReflectionException $ex) {
				// Entity property is readonly
				continue;
			}

			/** @var DoctrineCrud\Mapping\Annotation\Crud $crud */
			if ($crud = $this->annotationReader->getPropertyAnnotation($propertyReflection, DoctrineCrud\Mapping\Annotation\Crud::class)) {
				if ($isNew && $crud->isRequired() && !$values->offsetExists($fieldName)) {
					throw new Exceptions\MissingRequiredFieldException($entity, $fieldName, sprintf('Missing required key "%s"', $fieldName));
				}

				if (!array_key_exists($fieldName, (array) $values) || (!$isNew && !$crud->isWritable())) {
					continue;
				}

				$value = $values->offsetGet($fieldName);

				if ($value instanceof Utils\ArrayHash || is_array($value)) {
					if (!$classMetadata->getFieldValue($entity, $fieldName) instanceof Entities\IEntity) {
						$propertyAnnotations = $this->annotationReader->getPropertyAnnotations($propertyReflection);

						$annotations = array_map((function ($annotation) : string {
							return get_class($annotation);
						}), $propertyAnnotations);

						if (isset($value['type']) && class_exists($value['type'])) {
							$className = $value['type'];

						} elseif (in_array('Doctrine\ORM\Mapping\OneToOne', $annotations, TRUE)) {
							$className = $this->annotationReader->getPropertyAnnotation($propertyReflection, 'Doctrine\ORM\Mapping\OneToOne')->targetEntity;

						} elseif (in_array('Doctrine\ORM\Mapping\ManyToOne', $annotations, TRUE)) {
							$className = $this->annotationReader->getPropertyAnnotation($propertyReflection, 'Doctrine\ORM\Mapping\ManyToOne')->targetEntity;

						} else {
							$className = $propertyReflection->getAnnotation('var');
						}

						// Check if class is callable
						if (class_exists($className) && ($value instanceof Utils\ArrayHash || is_array($value))) {
							$rc = new \ReflectionClass($className);

							if ($rc->isAbstract() && isset($value['entity']) && class_exists($value['entity'])) {
								$className = $value['entity'];
								$rc = new \ReflectionClass($value['entity']);
							}

							if ($constructor = $rc->getConstructor()) {
								$subEntity = $rc->newInstanceArgs(DoctrineCrud\Helpers::autowireArguments($constructor, array_merge((array) $value, [$entity])));

								$this->setFieldValue($classMetadata, $entity, $fieldName, $subEntity);

							} else {
								$this->setFieldValue($classMetadata, $entity, $fieldName, new $className);
							}

						} else {
							$this->setFieldValue($classMetadata, $entity, $fieldName, $value);
						}
					}

					// Check again if entity was created
					if (($fieldValue = $classMetadata->getFieldValue($entity, $fieldName)) && $fieldValue instanceof Entities\IEntity) {
						$this->setFieldValue($classMetadata, $entity, $fieldName, $this->fillEntity(Utils\ArrayHash::from((array) $value), $fieldValue, $isNew));
					}

				} else {
					$this->setFieldValue($classMetadata, $entity, $fieldName, $value);
				}
			}
		}

		return $entity;
	}

	/**
	 * @param ORM\Mapping\ClassMetadata $classMetadata
	 * @param Entities\IEntity $entity
	 * @param string $field
	 * @param mixed $value
	 *
	 * @return void
	 */
	private function setFieldValue(ORM\Mapping\ClassMetadata $classMetadata, Entities\IEntity $entity, string $field, $value) : void
	{
		$methodName = 'set' . ucfirst($field);

		if ($value instanceof Utils\ArrayHash) {
			$value = (array) $value;
		}

		try {
			$propertyReflection = new Nette\Reflection\Method(get_class($entity), $methodName);

			//if (!$this->validators->validate($value, $propertyReflection)) {
			//	// Validation fail
			//}

			if ($propertyReflection->isPublic()) {
				// Try to call entity setter
				call_user_func_array([$entity, $methodName], [$value]);

			} else {
				// Fallback for missing setter
				$classMetadata->setFieldValue($entity, $field, $value);
			}

			// Fallback for missing setter
		} catch (\ReflectionException $ex) {
			$classMetadata->setFieldValue($entity, $field, $value);
		}
	}
}