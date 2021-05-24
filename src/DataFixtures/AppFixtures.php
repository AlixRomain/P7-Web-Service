<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Mobiles;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $encoder;
    private $faker;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
        $this->faker = Factory::create();
    }
    public function load(ObjectManager $manager)
    {

        //FOR LOAD METHOD WITH DROP TABLE "symfony console doctrine:fixtures:load"
        //FOR LOAD METHOD WITHOUT DROP TABLE "symfony console doctrine:fixtures:load --append"
        $contenu_fichier_json = file_get_contents(__DIR__.'/datas.json');
        $datas = json_decode($contenu_fichier_json, true);

        foreach($datas['mobiles'] as $mobil ){
            $mobile = new Mobiles();
            $mobile->setName($mobil["name"])
                    ->setDescription($mobil["description"])
                    ->setPrice($mobil["price"]);
            $manager->persist($mobile);
        }

        foreach($datas['clients'] as $client ){
            $newClient = new Client();
            $newClient->setName($client["name"]);
            $newClient->setAdress($client["adress"]);
            $manager->persist($newClient);
            /*Build 1 user Admin users foreach client corporation*/
            $userAdmin = new User();
            $userAdmin->setFullname('Mr . '.$client["name"])
                ->setUsername('Mr . '.$client["name"])
                ->setEmail(strtolower($this->faker->word.'@'.$client["name"].'.com'))
                ->setRoles(User::ROLE_CLIENT)
                ->setPassword($this->encoder->encodePassword($userAdmin, "OpenClass21!"))
                ->setAge($this->faker->numberBetween(18,90))
                ->setCreatedAt($this->faker->dateTimeThisYear( 'now'))
                ->setClient($newClient);
            $manager->persist($userAdmin);

            /*Build a 10 users simple for client corporation*/
            for($i = 0; $i < 10; $i++){
                $user = new User();
                $user->setFullname('Client chez '.$newClient->getName())
                    ->setUsername('Client chez '.$newClient->getName())
                    ->setEmail($this->faker->email)
                    ->setAge($this->faker->numberBetween(18,90))
                    ->setCreatedAt($this->faker->dateTimeThisYear( 'now'))
                    ->setRoles(User::ROLE_USER)
                    ->setPassword($this->encoder->encodePassword($user, "OpenClass21!"))
                    ->setClient($newClient);
                $manager->persist($user);
            }
        }
        $AdminBileMo = new User();
        $AdminBileMo->setFullname("Bile Mo")
            ->setUsername("BILEMo")
            ->setEmail("admin@admin.com")
            ->setRoles(User::ROLE_ADMIN)
            ->setPassword($this->encoder->encodePassword($AdminBileMo, "OpenClass21!"))
            ->setCreatedAt($this->faker->dateTimeThisYear( 'now'))
            ->setAge(25);
        $manager->persist($AdminBileMo);
        $manager->flush();
    }
}

