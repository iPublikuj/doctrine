<?php
/**
 * OrmExtension.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Doctrine!
 * @subpackage     DI
 * @since          1.0.0
 *
 * @date           29.01.14
 */

declare(strict_types = 1);

namespace IPub\Doctrine\DI;

use Doctrine\Common;

use Nette;
use Nette\PhpGenerator;

use Kdyby;

use IPub\Doctrine;
use IPub\Doctrine\Crud;
use IPub\Doctrine\Mapping;

/**
 * Doctrine CRUD extension container
 *
 * @package        iPublikuj:Doctrine!
 * @subpackage     DI
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class OrmExtension extends Kdyby\Doctrine\DI\OrmExtension
{
	// Define tag string for validator
	const TAG_VALIDATOR = 'ipub.doctrine.validator';

	/**
	 * @return void
	 */
	public function loadConfiguration()
	{
		// Get container builder
		$builder = $this->getContainerBuilder();

		$annotationReader = new Common\Annotations\AnnotationReader;

		Common\Annotations\AnnotationRegistry::registerAutoloadNamespace(
			'IPub\\Doctrine\\Entities\\IEntity'
		);

		$annotationReader = new Common\Annotations\CachedReader($annotationReader, new Common\Cache\ArrayCache);

		/**
		 * Extensions helpers
		 */

		$builder->addDefinition($this->prefix('entity.validator'))
			->setClass(Doctrine\Validation\ValidatorProxy::class)
			->setArguments([$annotationReader])
			->setAutowired(FALSE);

		$builder->addDefinition($this->prefix('entity.mapper'))
			->setClass(Mapping\EntityMapper::class)
			->setArguments(['@' . $this->prefix('entity.validator'), $annotationReader])
			->setAutowired(FALSE);

		/**
		 * CRUD factories
		 */

		$builder->addDefinition($this->prefix('entity.creator'))
			->setClass(Crud\Create\EntityCreator::class)
			->setImplement(Crud\Create\IEntityCreator::class)
			->setAutowired(FALSE);

		$builder->addDefinition($this->prefix('entity.updater'))
			->setClass(Crud\Update\EntityUpdater::class)
			->setImplement(Crud\Update\IEntityUpdater::class)
			->setAutowired(FALSE);

		$builder->addDefinition($this->prefix('entity.deleter'))
			->setClass(Crud\Delete\EntityDeleter::class)
			->setImplement(Crud\Delete\IEntityDeleter::class)
			->setAutowired(FALSE);

		$builder->addDefinition($this->prefix('entity.crudFactory'))
			->setClass(Crud\EntityCrudFactory::class)
			->setArguments([
				'@' . $this->prefix('entity.mapper'),
				'@' . $this->prefix('entity.creator'),
				'@' . $this->prefix('entity.updater'),
				'@' . $this->prefix('entity.deleter'),
			]);

		// Syntax sugar for config
		$builder->addDefinition($this->prefix('crud'))
			->setClass(Crud\EntityCrud::class)
			->setFactory('@IPub\Doctrine\Crud\EntityCrudFactory::create', [new PhpGenerator\PhpLiteral('$entityName')])
			->setParameters(['entityName'])
			->setAutowired(FALSE);

		/**
		 *
		 */

		parent::loadConfiguration();

		$configuration = $builder->getDefinition('doctrine.default.ormConfiguration');
		$configuration->addSetup('addCustomStringFunction', ['DATE_FORMAT', Doctrine\StringFunctions\DateFormat::class]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function beforeCompile()
	{
		parent::beforeCompile();

		// Get container builder
		$builder = $this->getContainerBuilder();

		// Get validators service
		$validator = $builder->getDefinition($this->prefix('entity.validator'));

		foreach (array_keys($builder->findByTag(self::TAG_VALIDATOR)) as $serviceName) {
			// Register validator to proxy validator
			$validator->addSetup('registerValidator', ['@' . $serviceName, $serviceName]);
		}
	}
}
