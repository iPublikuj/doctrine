<?php
/**
 * Test: IPub\DoctrineCrud\Extension
 *
 * @testCase
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:DoctrineCrud!
 * @subpackage     Tests
 * @since          1.0.0
 *
 * @date           13.01.16
 */

declare(strict_types = 1);

namespace IPubTests\DoctrineCrud;

use Nette;

use Tester;
use Tester\Assert;

use IPub\DoctrineCrud;

require __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'bootstrap.php';

/**
 * Registering doctrine extension tests
 *
 * @package        iPublikuj:DoctrineCrud!
 * @subpackage     Tests
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class ExtensionTest extends Tester\TestCase
{
	public function testFunctional() : void
	{
		$dic = $this->createContainer();

		$crudFactory = $dic->getByType(DoctrineCrud\Crud\IEntityCrudFactory::class);
		Assert::true($crudFactory instanceof DoctrineCrud\Crud\IEntityCrudFactory);
	}

	/**
	 * @return Nette\DI\Container
	 */
	protected function createContainer() : Nette\DI\Container
	{
		$rootDir = __DIR__ . '/../../';

		$config = new Nette\Configurator();
		$config->setTempDirectory(TEMP_DIR);

		$config->addParameters(['container' => ['class' => 'SystemContainer_' . md5((string) time())]]);
		$config->addParameters(['appDir' => $rootDir, 'wwwDir' => $rootDir]);

		$config->addConfig(__DIR__ . DS . 'files' . DS . 'config.neon');

		DoctrineCrud\DI\DoctrineCrudExtension::register($config);

		return $config->createContainer();
	}
}

\run(new ExtensionTest());
