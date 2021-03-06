<?php
/**
 * Test: IPub\DoctrineCrud\Models
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
 * @date           21.01.16
 */

namespace IPubTests\DoctrineCrud\Models;

use Doctrine\ORM\Mapping as ORM;

use IPub\DoctrineCrud\Entities;

use IPub\DoctrineCrud\Mapping\Annotation as IPubDoctrine;

/**
 * @ORM\Entity
 */
class ArticleEntity implements Entities\IEntity
{
	/**
	 * @var int
	 *
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $identifier;

	/**
	 * @var string|NULL
	 *
	 * @ORM\Column(type="string", nullable=true)
	 * @IPubDoctrine\Crud(is={"required", "writable"})
	 */
	private $title;

	/**
	 * @var UserEntity|NULL
	 *
	 * @ORM\ManyToOne(targetEntity="UserEntity")
	 * @IPubDoctrine\Crud(is={"required", "writable"})
	 */
	private $owner;

	/**
	 * @return int
	 */
	public function getId() : int
	{
		return $this->identifier;
	}

	/**
	 * @return string|NULL
	 */
	public function getTitle() : ?string
	{
		return $this->title;
	}

	/**
	 * @param string $title
	 *
	 * @return void
	 */
	public function setTitle(string $title) : void
	{
		$this->title = $title;
	}

	/**
	 * @return UserEntity|NULL
	 */
	public function getOwner() : ?UserEntity
	{
		return $this->owner;
	}

	/**
	 * @param UserEntity $user
	 *
	 * @return void
	 */
	public function setOwner(UserEntity $user) : void
	{
		$this->owner = $user;
	}
}
