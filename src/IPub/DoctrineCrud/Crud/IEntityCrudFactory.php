<?php
/**
 * IEntityCrudFactory.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:DoctrineCrud!
 * @subpackage     Crud
 * @since          1.0.0
 *
 * @date           29.01.14
 */

declare(strict_types = 1);

namespace IPub\DoctrineCrud\Crud;

use Nette;

use IPub;
use IPub\DoctrineCrud\Crud;

/**
 * Doctrine CRUD factory
 *
 * @package        iPublikuj:DoctrineCrud!
 * @subpackage     Crud
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface IEntityCrudFactory
{
	/**
	 * @param string $entityName
	 *
	 * @return EntityCrud
	 */
	public function create(string $entityName) : EntityCrud;
}