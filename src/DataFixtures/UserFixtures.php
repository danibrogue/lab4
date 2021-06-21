<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    public function load(ObjectManager $manager)
    {
        $admin = new User();
		$admin->setName('admin');
		$admin->setEmail('admin@admin.com');
		$admin->setRoles(["ROLE_ADMIN"]);
		$password = $this->encoder->encodePassword($admin, '123456');
		$admin->setPassword($password);
        $manager->persist($admin);

        $user = new User();
		$user->setName('Daniil');
		$user->setEmail('daniil@gmail.com');
		$password = $this->encoder->encodePassword($user, '123456');
		$user->setPassword($password);
        $manager->persist($user);
        $manager->flush();
    }
}