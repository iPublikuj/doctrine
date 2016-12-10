<?php
/**
 * TEntity.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:Doctrine!
 * @subpackage     Entities
 * @since          1.0.0
 *
 * @date           29.01.14
 */

declare(strict_types = 1);

namespace IPub\Doctrine\Entities;

use IPub;
use IPub\Doctrine;
use IPub\Doctrine\Mapping;

/**
 * Doctrine CRUD identified entity helper trait
 *
 * @package        iPublikuj:Doctrine!
 * @subpackage     Entities
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 *
 * @ORM\MappedSuperclass
 */
trait TEntity
{
	/**
	 * @var Mapping\EntityHydrator
	 */
	protected $hydrator;

	/**
	 * @return string
	 */
	public static function getClassName() : string
	{
		return get_called_class();
	}

	/**
	 * @param int $maxLevel
	 *
	 * @return array
	 */
	public function toArray(int $maxLevel = 1) : array
	{
		return $this->getHydrator()->extract($this, $maxLevel);
	}

	/**
	 * @return array
	 */
	public function toSimpleArray() : array
	{
		return $this->getHydrator()->simpleExtract($this);
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		if (isset($this->name) && $this->name !== NULL) {
			return (string) $this->name;

		} elseif (property_exists($this, 'id')) {
			return (string) $this->id;

		} else {
			return '';
		}
	}

	/**
	 * @return Mapping\EntityHydrator
	 */
	protected function getHydrator() : Mapping\EntityHydrator
	{
		if ($this->hydrator === NULL) {
			$this->hydrator = new Mapping\EntityHydrator;
		}

		return $this->hydrator;
	}
}
