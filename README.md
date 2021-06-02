# Bilemo




This REST API provides a catalog of mobiles for our clients, and the possibility to manage their users.

[Visit the api doc](https://bilemo.tabaz.fr/doc)

## Build With

- Symfony 5
- LexikJWTAuthenticationBundle
- JMSSerializerBundle
- FOSRESTBUNDLE
- Hateoas-bundle
- Pagerfanta-bundle
- OpenApi


## Installation

1 - Clone or download the project

```https://github.com/AlixRomain/P7-Web-Service```

2 - Update your database identifiers in bilemo/.env

````DATABASE_URL=mysql://db_user:db_password@127.0.0.1:3306/db_name````

3 - Install composer -> [Composer installation doc](https://getcomposer.org/download/)

4 - Run composer.phar to install dependencies

```php bin/console composer.phar update```

5 - Two solutions for gives data in BDD.

- A - Import bilemo.sql to your database, it contains data set
- B - Use a doctrine:migrations:migrate for generate  a structure in base and generate a new datas with  DataFixtures
  
  ````symfony console doctrine:fixtures:load````


6 - Don't forget to add a JWT_PASSPHRASE in bilemo/.env

```JWT_PASSPHRASE=YourPassPhrase```

7 - Generate the JWTAuthentication SSH keys ([Official documentation](https://github.com/lexik/LexikJWTAuthenticationBundle/blob/master/Resources/doc/index.md#installation
))

 ```
 mkdir -p config/jwt
 openssl genrsa -out config/jwt/private.pem -aes256 4096
 openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
```

## Usage

Login link :

```/login```

An client account is already available, use it to test the API :

```
{
   "email" : "admin@admin.com",
   "password" : "OpenClass21!"
}
```

An admin account is already available, use it to test the API :

```
{
   "email" : "aperiam@orange.com",
   "password" : "OpenClass21!"
}
```

## Documentation

You can see the full documentation here => [Bilemo Api Documentation](https://bilemo.tabaz.fr/doc)
